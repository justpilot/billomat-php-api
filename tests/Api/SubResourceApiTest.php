<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Closure;
use InvalidArgumentException;
use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ClientPropertyValueCreateOptions;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\CommentsApi;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\ItemsApi;
use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\Api\PaymentsApi;
use Justpilot\Billomat\Api\PropertiesApi;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Api\PropertyValuesApi;
use Justpilot\Billomat\Api\ReminderItemsApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\TagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ClientPropertyValue;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\ReminderItem;
use Justpilot\Billomat\Pagination\Page;
use Justpilot\Billomat\Pagination\PageInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

/**
 * Typ-Prüfungen und Verschachtelung der generischen Sub-Ressourcen.
 */
#[CoversClass(AbstractSubResourceApi::class)]
#[CoversClass(CommentsApi::class)]
#[CoversClass(ItemsApi::class)]
#[CoversClass(TagsApi::class)]
#[CoversClass(PaymentsApi::class)]
#[CoversClass(PropertyValuesApi::class)]
#[CoversClass(ReminderItemsApi::class)]
#[CoversClass(InvoicesApi::class)]
#[UsesClass(ClientsApi::class)]
#[UsesClass(CreditNotesApi::class)]
#[UsesClass(RemindersApi::class)]
#[UsesClass(ClientPropertyValue::class)]
#[UsesClass(CreditNoteComment::class)]
#[UsesClass(ReminderItem::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Page::class)]
#[UsesClass(PageInfo::class)]
#[UsesClass(UnexpectedResponseException::class)]
#[UsesClass(OfferTagCreateOptions::class)]
#[UsesClass(PropertiesApi::class)]
#[UsesClass(PropertyCreateOptions::class)]
#[UsesClass(CreditNotePaymentCreateOptions::class)]
#[UsesClass(ClientPropertyValueCreateOptions::class)]
#[UsesClass(SupplierPropertyValueCreateOptions::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(OfferCommentCreateOptions::class)]
#[UsesClass(InvoiceItemCreateOptions::class)]
#[UsesClass(OfferItemCreateOptions::class)]
#[UsesClass(Money::class)]
#[UsesClass(ListCaster::class)]
final class SubResourceApiTest extends TestCase
{
    private MockHttpClient $mock;

    /** @var list<string> */
    private array $urls = [];

    /** @var list<MockResponse> */
    private array $responses = [];

    protected function setUp(): void
    {
        $this->mock = new MockHttpClient(function (string $method, string $url): MockResponse {
            $this->urls[] = $url;

            return array_shift($this->responses) ?? new MockResponse('', ['http_code' => 500]);
        });
    }

    #[Test]
    public function subResourcesAreCreatedOnceAndReused(): void
    {
        $invoices = $this->invoices();

        self::assertSame($invoices->comments, $invoices->comments);
        self::assertNotSame($invoices->comments, $this->invoices()->comments);
    }

    #[Test]
    public function createRejectsOptionsOfAnotherResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Expected '.InvoiceCommentCreateOptions::class.', got '.OfferCommentCreateOptions::class.'.');

        try {
            /** @phpstan-ignore argument.type (absichtlich falsche Options) */
            $this->invoices()->comments->create(new OfferCommentCreateOptions(offerId: 1, comment: 'x'));
        } finally {
            self::assertSame(0, $this->mock->getRequestsCount());
        }
    }

    #[Test]
    public function updateRejectsOptionsOfAnotherResource(): void
    {
        $this->expectException(InvalidArgumentException::class);

        /** @phpstan-ignore argument.type (absichtlich falsche Options) */
        $this->invoices()->items->update(1, new OfferItemCreateOptions(quantity: 1.0, unitPrice: Money::of('1')));
    }

    #[Test]
    public function listRejectsActionKeysOfAnotherResource(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains(OfferCommentActionKey::class);

        /** @phpstan-ignore argument.type (absichtlich falsches Enum) */
        (void) $this->invoices()->comments->list(1, [OfferCommentActionKey::CREATE]);
    }

    /**
     * @param Closure(InvoicesApi, ClientsApi): object $call
     */
    #[Test]
    #[DataProvider('foreignOptions')]
    public function writesRejectOptionsOfAnotherResource(Closure $call): void
    {
        $this->expectException(InvalidArgumentException::class);

        try {
            $call($this->invoices(), new ClientsApi($this->http()));
        } finally {
            self::assertSame(0, $this->mock->getRequestsCount());
        }
    }

    /**
     * @return iterable<string, array{Closure(InvoicesApi, ClientsApi): object}>
     */
    public static function foreignOptions(): iterable
    {
        /** @phpstan-ignore argument.type (absichtlich falsche Options) */
        yield 'tags' => [static fn (InvoicesApi $i): object => $i->tags->create(new OfferTagCreateOptions(offerId: 1, name: 'x'))];
        /** @phpstan-ignore argument.type (absichtlich falsche Options) */
        yield 'items' => [static fn (InvoicesApi $i): object => $i->items->create(1, new OfferItemCreateOptions(quantity: 1.0, unitPrice: Money::of('1')))];
        /** @phpstan-ignore argument.type (absichtlich falsche Options) */
        yield 'payments' => [static fn (InvoicesApi $i): object => $i->payments->create(new CreditNotePaymentCreateOptions(creditNoteId: 1, amount: Money::of('1')))];
        /** @phpstan-ignore argument.type (absichtlich falsche Options) */
        yield 'property values' => [static fn (InvoicesApi $i, ClientsApi $c): object => $c->propertyValues->create(new SupplierPropertyValueCreateOptions(supplierId: 1, supplierPropertyId: 2, value: 'x'))];
    }

    #[Test]
    public function pathsAndFilterFieldDeriveFromTheParentSlug(): void
    {
        $this->respond(['credit-note-comments' => ['credit-note-comment' => ['id' => 1, 'credit_note_id' => 7, 'comment' => 'x']]]);

        $comments = new CreditNotesApi($this->http())->comments->list(7);

        self::assertCount(1, $comments);
        self::assertSame(['https://mycompany.billomat.net/api/credit-note-comments?credit_note_id=7'], $this->urls);
    }

    #[Test]
    public function getNamesTheResourceWhenTheEnvelopeIsMissing(): void
    {
        $this->respond(['unexpected' => []]);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('Unexpected response from Billomat when fetching credit note comment.');

        (void) new CreditNotesApi($this->http())->comments->get(1);
    }

    #[Test]
    public function createNamesTheResourceWhenTheEnvelopeIsMissing(): void
    {
        $this->respond(['unexpected' => []]);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('Unexpected response from Billomat when creating client property value.');

        new ClientsApi($this->http())->propertyValues->create(new ClientPropertyValueCreateOptions(clientId: 1, clientPropertyId: 2, value: 'x'));
    }

    #[Test]
    public function updateNamesTheResourceWhenTheEnvelopeIsMissing(): void
    {
        $this->respond(['unexpected' => []]);

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('Unexpected response from Billomat when updating client property.');

        new ClientsApi($this->http())->properties->update(1, new PropertyCreateOptions(name: 'x'));
    }

    #[Test]
    public function propertyValuePagesPutTheParentFilterFirst(): void
    {
        $this->respond(['client-property-values' => ['client-property-value' => []]]);
        $this->respond(['client-property-values' => ['client-property-value' => []]]);

        $api = new ClientsApi($this->http())->propertyValues;
        (void) $api->listPage(5, ['client_id' => 9, 'value' => 'a']);
        iterator_to_array($api->iterateAll(5, ['value' => 'a'], 10));

        self::assertSame([
            'https://mycompany.billomat.net/api/client-property-values?client_id=5&value=a',
            'https://mycompany.billomat.net/api/client-property-values?client_id=5&value=a&page=1&per_page=10',
        ], $this->urls);
    }

    #[Test]
    public function reminderItemsPassAdditionalFilters(): void
    {
        $this->respond(['reminder-items' => ['reminder-item' => []]]);

        (void) new RemindersApi($this->http())->items->list(3, ['per_page' => 5]);

        self::assertSame(['https://mycompany.billomat.net/api/reminder-items?reminder_id=3&per_page=5'], $this->urls);
    }

    /**
     * @param array<string, mixed> $body
     */
    private function respond(array $body): void
    {
        $this->responses[] = new MockResponse(json_encode($body, JSON_THROW_ON_ERROR));
    }

    private function invoices(): InvoicesApi
    {
        return new InvoicesApi($this->http());
    }

    private function http(): BillomatHttpClient
    {
        $config = new BillomatConfig(billomatId: 'mycompany', apiKey: 'k', retry: RetryPolicy::none());

        return new BillomatHttpClient(new Psr18Client($this->mock), $config);
    }
}
