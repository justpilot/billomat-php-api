<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\CreditNoteFilter;
use Justpilot\Billomat\Api\IncomingItemCreateOptions;
use Justpilot\Billomat\Api\IncomingItemUpdateOptions;
use Justpilot\Billomat\Api\ReminderItemCreateOptions;
use Justpilot\Billomat\Api\ReminderItemUpdateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\CreditNoteGroup;
use Justpilot\Billomat\Model\Enum\CreditNoteGroupBy;
use Justpilot\Billomat\Model\Enum\IncomingGroupBy;
use Justpilot\Billomat\Model\Enum\IncomingStatus;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\IncomingItem;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Gruppierte Listen von Gutschriften und Eingangsrechnungen, InvoicesApi::preset(),
 * die Posten der Eingangsrechnungen und das Schreiben von Mahnungspositionen.
 */
#[CoversNamespace('Justpilot\\Billomat\\Api')]
#[UsesClass(BillomatClient::class)]
#[UsesNamespace('Justpilot\\Billomat\\Config')]
#[UsesNamespace('Justpilot\\Billomat\\Http')]
#[UsesNamespace('Justpilot\\Billomat\\Exception')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
final class GroupingPresetItemsApiTest extends TestCase
{
    #[Test]
    public function creditNotesAreListedGrouped(): void
    {
        $captured = new CapturedRequest();
        $billomat = $this->client($captured, [
            'credit-note-groups' => [
                'credit-note-group' => [
                    ['total_gross' => '120', 'total_net' => '100.8', 'client_id' => '9340866', 'year' => '2026', 'credit-note-params' => ['client_id' => '9340866', 'from' => '2026-01-01']],
                    ['total_gross' => '118.76', 'total_net' => '99.8', 'client_id' => '10651661', 'year' => '2026', 'credit-note-params' => ['client_id' => '10651661']],
                ],
                '@currency_code' => 'EUR',
            ],
        ]);

        $groups = $billomat->creditNotes->listGrouped([CreditNoteGroupBy::CLIENT, CreditNoteGroupBy::YEAR], new CreditNoteFilter(clientId: 9340866));

        self::assertCount(2, $groups);
        self::assertContainsOnlyInstancesOf(CreditNoteGroup::class, $groups);
        self::assertSame(9340866, $groups[0]->clientId);
        self::assertSame('100.8', $groups[0]->totalNet?->toString());
        self::assertSame(['client_id' => '9340866', 'from' => '2026-01-01'], $groups[0]->creditNoteParams);
        self::assertSame('/api/credit-notes', $captured->path());
        self::assertSame('client,year', $captured->query()['group_by'] ?? null);
        self::assertSame('9340866', $captured->query()['client_id'] ?? null);
    }

    #[Test]
    public function incomingsAreListedGroupedWithASingleCriterion(): void
    {
        $captured = new CapturedRequest();
        $billomat = $this->client($captured, [
            // Eine einzige Gruppe liefert Billomat als Objekt statt als Liste.
            'incoming-groups' => [
                'incoming-group' => ['total_gross' => '119', 'total_net' => '100', 'status' => 'OPEN', 'incoming-params' => ['status' => 'OPEN']],
                '@currency_code' => 'EUR',
            ],
        ]);

        $groups = $billomat->incomings->listGrouped(IncomingGroupBy::STATUS);

        self::assertCount(1, $groups);
        self::assertSame(IncomingStatus::OPEN, $groups[0]->status);
        self::assertSame('/api/incomings', $captured->path());
        self::assertSame('status', $captured->query()['group_by'] ?? null);
    }

    #[Test]
    public function presetReturnsCreateOptionsFromTheDraft(): void
    {
        $captured = new CapturedRequest();
        // Gekürzte Antwort von GET /invoices/preset?confirmation_id=… aus der Sandbox.
        $billomat = $this->client($captured, [
            'invoice' => [
                'id' => '',
                'client_id' => '9340866',
                'contact_id' => '',
                'number_pre' => 'RE',
                'number_length' => '5',
                'title' => 'Rechnung [Invoice.invoice_number]',
                'date' => '',
                'due_days' => '0',
                'status' => 'DRAFT',
                'total_gross' => '81.39',
                'net_gross' => 'GROSS',
                'discount_days' => '7',
                'payment_types' => '',
                'confirmation_id' => '550206',
                'template_id' => '971608',
            ],
        ]);

        $options = $billomat->invoices->preset(confirmationId: 550206);

        self::assertSame('/api/invoices/preset', $captured->path());
        self::assertSame(['confirmation_id' => '550206'], $captured->query());
        self::assertSame(9340866, $options->clientId);
        self::assertNull($options->contactId);
        self::assertSame('RE', $options->numberPre);
        self::assertSame(5, $options->numberLength);
        self::assertSame('Rechnung [Invoice.invoice_number]', $options->title);
        self::assertNull($options->date);
        self::assertSame(0, $options->dueDays);
        self::assertSame(NetGross::GROSS, $options->netGross);
        self::assertSame(7, $options->discountDays);
        self::assertSame([], $options->paymentTypes);
        self::assertSame(550206, $options->confirmationId);
        self::assertSame(971608, $options->templateId);
        // Summen und Status gehören nicht in den Payload.
        self::assertArrayNotHasKey('total_gross', $options->toArray());
        self::assertArrayNotHasKey('status', $options->toArray());
    }

    #[Test]
    public function presetSendsEverySource(): void
    {
        $captured = new CapturedRequest();
        $billomat = $this->client($captured, ['invoice' => ['client_id' => '1']]);

        (void) $billomat->invoices->preset(clientId: 1, offerId: 2, confirmationId: 3, deliveryNoteId: 4, invoiceId: 5, recurringId: 6);

        self::assertSame([
            'client_id' => '1',
            'offer_id' => '2',
            'confirmation_id' => '3',
            'delivery_note_id' => '4',
            'invoice_id' => '5',
            'recurring_id' => '6',
        ], $captured->query());
    }

    #[Test]
    public function presetWithoutSourceLeavesTheErrorToBillomat(): void
    {
        $captured = new CapturedRequest();
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('{"errors":{"error":"Neither client nor supplier was set"}}', ['http_code' => 400]);
        });
        $billomat = new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));

        try {
            (void) $billomat->invoices->preset();
            self::fail('preset() ohne Quelle hat keine ValidationException geworfen.');
        } catch (ValidationException $e) {
            self::assertStringContainsString('Neither client nor supplier was set', $e->getMessage());
        }
        self::assertSame([], $captured->query());
    }

    #[Test]
    public function presetWithoutClientIsUnexpected(): void
    {
        $billomat = $this->client(new CapturedRequest(), ['invoice' => ['id' => '', 'client_id' => '']]);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('InvoiceCreateOptions without a valid "client_id" field');

        (void) $billomat->invoices->preset(offerId: 1);
    }

    #[Test]
    public function incomingItemsAreManagedThroughTheIncoming(): void
    {
        $captured = new CapturedRequest();
        $billomat = $this->client($captured, [
            'incoming-item' => ['id' => '2177198', 'incoming_id' => '2063418', 'total' => '119.50', 'tax_rate' => '19', 'tax_included' => '1', 'type' => 'SERVICE'],
        ]);

        $item = $billomat->incomings->items->create(2063418, new IncomingItemCreateOptions(
            total: Money::of('119.50'),
            description: 'Hosting',
            taxRate: 19.0,
            taxIncluded: true,
            type: ItemType::SERVICE,
        ));

        self::assertInstanceOf(IncomingItem::class, $item);
        self::assertSame(2063418, $item->incomingId);
        self::assertSame('119.5', $item->total?->toString());
        self::assertTrue($item->taxIncluded);
        self::assertSame('POST', $captured->method);
        self::assertSame('/api/incoming-items', $captured->path());
        self::assertSame(['incoming-item' => [
            'total' => '119.5',
            'description' => 'Hosting',
            'tax_rate' => 19.0,
            'tax_included' => 1,
            'type' => 'SERVICE',
            'incoming_id' => 2063418,
        ]], $captured->json());

        $billomat->incomings->items->update(2177198, new IncomingItemUpdateOptions(description: 'Server'));
        self::assertSame('PUT', $captured->method);
        self::assertSame('/api/incoming-items/2177198', $captured->path());
        self::assertSame(['incoming-item' => ['description' => 'Server']], $captured->json());

        (void) $billomat->incomings->items->list(2063418);
        self::assertSame('2063418', $captured->query()['incoming_id'] ?? null);

        $billomat->incomings->items->delete(2177198);
        self::assertSame('DELETE', $captured->method);
        self::assertSame('/api/incoming-items/2177198', $captured->path());
    }

    #[Test]
    public function reminderItemsCanBeWritten(): void
    {
        $captured = new CapturedRequest();
        $billomat = $this->client($captured, [
            'reminder-item' => ['id' => '4298852', 'reminder_id' => '3375689', 'quantity' => '1', 'unit_price' => '5', 'title' => 'Porto'],
        ]);

        $item = $billomat->reminders->items->create(3375689, new ReminderItemCreateOptions(quantity: 1.0, unitPrice: Money::of('5'), title: 'Porto'));

        self::assertSame(4298852, $item->id);
        self::assertSame('POST', $captured->method);
        self::assertSame('/api/reminder-items', $captured->path());
        self::assertSame(['reminder-item' => ['title' => 'Porto', 'quantity' => 1.0, 'unit_price' => '5', 'reminder_id' => 3375689]], $captured->json());

        $updated = $billomat->reminders->items->update(4298852, new ReminderItemUpdateOptions(title: 'Porto und Versand'));
        self::assertSame(4298852, $updated->id);
        self::assertSame('PUT', $captured->method);
        self::assertSame('/api/reminder-items/4298852', $captured->path());
        self::assertSame(['reminder-item' => ['title' => 'Porto und Versand']], $captured->json());

        $billomat->reminders->items->delete(4298852);
        self::assertSame('DELETE', $captured->method);
        self::assertSame('/api/reminder-items/4298852', $captured->path());
    }

    /**
     * @param array<string, mixed> $answer
     */
    private function client(CapturedRequest $captured, array $answer): BillomatClient
    {
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured, $answer): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('DELETE' === $method ? '' : (string) json_encode($answer));
        });

        return new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));
    }
}
