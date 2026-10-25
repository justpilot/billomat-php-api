<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Webhook;

use JsonException;
use Justpilot\Billomat\Exception\InvalidWebhookException;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoicePayment;
use Justpilot\Billomat\Webhook\WebhookEvent;
use Justpilot\Billomat\Webhook\WebhookPayload;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

/**
 * Fixtures: anonymisierte echte Webhook-Bodies (tests/Fixtures/Webhook/<event>.json).
 */
#[CoversClass(WebhookPayload::class)]
#[UsesClass(WebhookEvent::class)]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
final class WebhookPayloadTest extends TestCase
{
    #[Test]
    public function itHydratesAnInvoiceStatusEvent(): void
    {
        $payload = WebhookPayload::fromJson(WebhookEvent::INVOICE_STATUS, self::fixture('invoice.status'));
        $invoice = $payload->model();

        self::assertSame(WebhookEvent::INVOICE_STATUS, $payload->event);
        self::assertSame(1001, $payload->id());
        self::assertInstanceOf(Invoice::class, $invoice);
        self::assertSame(1001, $invoice->id);
        self::assertSame(2002, $invoice->clientId);
        self::assertSame(InvoiceStatus::PAID, $invoice->status);
        self::assertSame('RE-2026-0042', $invoice->invoiceNumber);
        self::assertSame('', $payload->data['contact_id']);
        self::assertNull($invoice->contactId);
        self::assertCount(1, $invoice->taxes);
    }

    #[Test]
    public function itHydratesAnInvoicePaymentEvent(): void
    {
        $payload = WebhookPayload::fromJson(WebhookEvent::INVOICE_PAYMENT_CREATE, self::fixture('invoice_payment.create'));
        $payment = $payload->model();

        self::assertInstanceOf(InvoicePayment::class, $payment);
        self::assertSame(5005, $payload->id());
        self::assertSame(1001, $payload->int('invoice_id'));
        self::assertSame(1001, $payment->invoiceId);
        self::assertSame(InvoicePaymentType::BANK_CARD, $payment->type);
        self::assertSame('2026-10-20', $payment->date?->format('Y-m-d'));
    }

    /**
     * Jede Fixture passt zu ihrem Event und zum Model.
     */
    #[Test]
    #[DataProvider('fixtures')]
    public function everyFixtureHydrates(WebhookEvent $event, string $json): void
    {
        $payload = WebhookPayload::fromJson($event, $json);

        self::assertInstanceOf($event->modelClass(), $payload->model());
        self::assertNotNull($payload->id());
    }

    /**
     * @return iterable<string, array{WebhookEvent, string}>
     */
    public static function fixtures(): iterable
    {
        foreach ((array) glob(__DIR__.'/../Fixtures/Webhook/*.json') as $file) {
            $name = basename((string) $file, '.json');
            yield $name => [WebhookEvent::from($name), self::fixture($name)];
        }
    }

    #[Test]
    public function intReadsDigitsOnly(): void
    {
        $payload = WebhookPayload::fromArray(WebhookEvent::INVOICE_UPDATE, ['invoice' => [
            'id' => 12,
            'client_id' => '',
            'contact_id' => 'abc',
            'number' => '-3',
        ]]);

        self::assertSame(12, $payload->id());
        self::assertNull($payload->int('client_id'));
        self::assertNull($payload->int('contact_id'));
        self::assertNull($payload->int('number'));
        self::assertNull($payload->int('missing'));
    }

    #[Test]
    public function anEmptyRecordIsAccepted(): void
    {
        $payload = WebhookPayload::fromArray(WebhookEvent::INVOICE_DELETE, ['invoice' => []]);

        self::assertSame([], $payload->data);
        self::assertNull($payload->id());
    }

    /**
     * @param array<array-key, mixed> $body
     */
    #[Test]
    #[DataProvider('invalidBodies')]
    public function itRejectsBodiesWithoutTheEnvelope(array $body): void
    {
        $this->expectException(InvalidWebhookException::class);
        $this->expectExceptionMessageIsOrContains('Billomat webhook body for "invoice_payment.create" lacks the "invoice-payment" envelope.');

        WebhookPayload::fromArray(WebhookEvent::INVOICE_PAYMENT_CREATE, $body);
    }

    /**
     * @return iterable<string, array{array<array-key, mixed>}>
     */
    public static function invalidBodies(): iterable
    {
        yield 'leer' => [[]];
        yield 'falscher Schlüssel' => [['invoice_payment' => ['id' => '1']]];
        yield 'anderer Envelope' => [['invoice' => ['id' => '1']]];
        yield 'ohne Envelope' => [['id' => '1', 'invoice_id' => '2']];
        yield 'String statt Objekt' => [['invoice-payment' => '1']];
        yield 'Liste statt Objekt' => [['invoice-payment' => [['id' => '1']]]];
    }

    #[Test]
    public function itRejectsInvalidJson(): void
    {
        try {
            WebhookPayload::fromJson(WebhookEvent::INVOICE_UPDATE, '{"invoice": ');
            self::fail('Expected an InvalidWebhookException.');
        } catch (InvalidWebhookException $e) {
            self::assertSame('Billomat webhook body for "invoice.update" is not valid JSON.', $e->getMessage());
            self::assertInstanceOf(JsonException::class, $e->getPrevious());
        }
    }

    #[Test]
    #[DataProvider('scalarJson')]
    public function itRejectsJsonThatIsNoObject(string $json): void
    {
        $this->expectException(InvalidWebhookException::class);
        $this->expectExceptionMessageIsOrContains('Billomat webhook body for "invoice.update" is not a JSON object.');

        WebhookPayload::fromJson(WebhookEvent::INVOICE_UPDATE, $json);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function scalarJson(): iterable
    {
        yield 'String' => ['"invoice"'];
        yield 'Zahl' => ['42'];
        yield 'null' => ['null'];
    }

    private static function fixture(string $event): string
    {
        return (string) file_get_contents(__DIR__.'/../Fixtures/Webhook/'.$event.'.json');
    }
}
