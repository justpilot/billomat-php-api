<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Closure;
use Generator;
use InvalidArgumentException;
use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\ClientPropertyValueCreateOptions;
use Justpilot\Billomat\Api\ClientPropertyValueFilter;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\CommentsApi;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentFilter;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentFilter;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\Api\ItemsApi;
use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\PaymentsApi;
use Justpilot\Billomat\Api\PropertiesApi;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Api\PropertyValuesApi;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiversApi;
use Justpilot\Billomat\Api\RecurringsApi;
use Justpilot\Billomat\Api\ReminderItemsApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Api\SubResourceFilter;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueFilter;
use Justpilot\Billomat\Api\TagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\ClientPropertyValue;
use Justpilot\Billomat\Model\CreditNoteComment;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\ReminderItem;
use Justpilot\Billomat\Pagination\Page;
use Justpilot\Billomat\Pagination\PageInfo;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
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
#[CoversClass(RecurringEmailReceiversApi::class)]
#[CoversClass(PropertiesApi::class)]
#[UsesNamespace('Justpilot\\Billomat\\Api')]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
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
            $this->invoices()->comments->create(1, new OfferCommentCreateOptions(comment: 'x'));
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
        yield 'tags' => [static fn (InvoicesApi $i): object => $i->tags->create(1, new OfferTagCreateOptions(name: 'x'))];
        /** @phpstan-ignore argument.type (absichtlich falsche Options) */
        yield 'items' => [static fn (InvoicesApi $i): object => $i->items->create(1, new OfferItemCreateOptions(quantity: 1.0, unitPrice: Money::of('1')))];
        /** @phpstan-ignore argument.type (absichtlich falsche Options) */
        yield 'payments' => [static fn (InvoicesApi $i): object => $i->payments->create(1, new CreditNotePaymentCreateOptions(amount: Money::of('1')))];
        /** @phpstan-ignore argument.type (absichtlich falsche Options) */
        yield 'property values' => [static fn (InvoicesApi $i, ClientsApi $c): object => $c->propertyValues->create(1, new SupplierPropertyValueCreateOptions(supplierPropertyId: 2, value: 'x'))];
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

        new ClientsApi($this->http())->propertyValues->create(1, new ClientPropertyValueCreateOptions(clientPropertyId: 2, value: 'x'));
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
        iterator_to_array($api->iterateAll(5, ['value' => 'a']));

        self::assertSame([
            'https://mycompany.billomat.net/api/client-property-values?client_id=5&value=a',
            'https://mycompany.billomat.net/api/client-property-values?client_id=5&value=a&page=1&per_page=100',
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
     * @param Closure(BillomatHttpClient, SubResourceFilter): mixed $call
     */
    #[Test]
    #[DataProvider('sortedLists')]
    public function listsTakeParentIdAndSubResourceFilter(Closure $call, string $path, string $envelope, string $expectedQuery): void
    {
        $this->respond([$envelope => [(string) preg_replace(['/ies$/', '/s$/'], ['y', ''], $envelope) => [['id' => 1, 'unit_price' => '1'], ['id' => 2, 'unit_price' => '2']]]]);

        $result = $call($this->http(), new SubResourceFilter(orderBy: OrderBy::desc('id'), page: 2));
        $items = match (true) {
            $result instanceof Generator => iterator_to_array($result, false),
            $result instanceof Page => $result->items,
            \is_array($result) => $result,
            default => self::fail('Unerwartetes Ergebnis'),
        };

        self::assertCount(2, $items);
        self::assertSame(['https://mycompany.billomat.net/api/'.$path.'?'.$expectedQuery], $this->urls);
    }

    /**
     * @return iterable<string, array{Closure(BillomatHttpClient, SubResourceFilter): mixed, string, string, string}>
     */
    public static function sortedLists(): iterable
    {
        $sorted = 'order_by=id+DESC&page=2';
        $iterated = 'order_by=id+DESC&page=1&per_page=100';

        yield 'items list' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): array => new InvoicesApi($h)->items->list(7, $f), 'invoice-items', 'invoice-items', 'invoice_id=7&'.$sorted];
        yield 'items page' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Page => new InvoicesApi($h)->items->listPage(7, $f), 'invoice-items', 'invoice-items', 'invoice_id=7&'.$sorted];
        yield 'items iterate' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Generator => new InvoicesApi($h)->items->iterateAll(7, $f), 'invoice-items', 'invoice-items', 'invoice_id=7&'.$iterated];
        yield 'comments list' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): array => new InvoicesApi($h)->comments->list(7, filters: $f), 'invoice-comments', 'invoice-comments', 'invoice_id=7&'.$sorted];
        yield 'comments page' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Page => new InvoicesApi($h)->comments->listPage(7, [InvoiceCommentActionKey::COMMENT], $f), 'invoice-comments', 'invoice-comments', 'invoice_id=7&'.$sorted.'&actionkey=COMMENT'];
        yield 'comments iterate' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Generator => new InvoicesApi($h)->comments->iterateAll(7, filters: $f), 'invoice-comments', 'invoice-comments', 'invoice_id=7&'.$iterated];
        yield 'tags list' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): array => new InvoicesApi($h)->tags->list(7, $f), 'invoice-tags', 'invoice-tags', 'invoice_id=7&'.$sorted];
        yield 'tags page' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Page => new InvoicesApi($h)->tags->listPage(7, $f), 'invoice-tags', 'invoice-tags', 'invoice_id=7&'.$sorted];
        yield 'tags iterate' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Generator => new InvoicesApi($h)->tags->iterateAll(7, $f), 'invoice-tags', 'invoice-tags', 'invoice_id=7&'.$iterated];
        yield 'reminder items page' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Page => new RemindersApi($h)->items->listPage(7, $f), 'reminder-items', 'reminder-items', 'reminder_id=7&'.$sorted];
        yield 'reminder items iterate' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Generator => new RemindersApi($h)->items->iterateAll(7, $f), 'reminder-items', 'reminder-items', 'reminder_id=7&'.$iterated];
        yield 'email receivers list' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): array => new RecurringsApi($h)->emailReceivers->list(7, $f), 'recurring-email-receivers', 'recurring-email-receivers', 'recurring_id=7&'.$sorted];
        yield 'email receivers page' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Page => new RecurringsApi($h)->emailReceivers->listPage(7, $f), 'recurring-email-receivers', 'recurring-email-receivers', 'recurring_id=7&'.$sorted];
        yield 'email receivers iterate' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Generator => new RecurringsApi($h)->emailReceivers->iterateAll(7, $f), 'recurring-email-receivers', 'recurring-email-receivers', 'recurring_id=7&'.$iterated];
        yield 'properties list' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): array => new ClientsApi($h)->properties->list($f), 'client-properties', 'client-properties', $sorted];
        yield 'properties page' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Page => new ClientsApi($h)->properties->listPage($f), 'client-properties', 'client-properties', $sorted];
        yield 'properties iterate' => [static fn (BillomatHttpClient $h, SubResourceFilter $f): Generator => new ClientsApi($h)->properties->iterateAll($f), 'client-properties', 'client-properties', $iterated];
    }

    #[Test]
    public function paymentsTakeTheirTypedFilter(): void
    {
        $this->respond(['invoice-payments' => ['invoice-payment' => []]]);
        $this->respond(['invoice-payments' => ['invoice-payment' => []]]);
        $this->respond(['invoice-payments' => ['invoice-payment' => []]]);

        $api = $this->invoices()->payments;
        $filter = new InvoicePaymentFilter(type: [PaymentType::CASH], orderBy: OrderBy::desc('date'));
        (void) $api->list(7, $filter);
        (void) $api->listPage(filters: $filter->withPage(3));
        iterator_to_array($api->iterateAll(7, $filter));

        self::assertSame([
            'https://mycompany.billomat.net/api/invoice-payments?invoice_id=7&type=CASH&order_by=date+DESC',
            'https://mycompany.billomat.net/api/invoice-payments?type=CASH&order_by=date+DESC&page=3',
            'https://mycompany.billomat.net/api/invoice-payments?invoice_id=7&type=CASH&order_by=date+DESC&page=1&per_page=100',
        ], $this->urls);
    }

    /**
     * @param Closure(InvoicesApi, ClientsApi): mixed $call
     */
    #[Test]
    #[DataProvider('foreignFilters')]
    public function listsRejectFiltersOfAnotherResource(Closure $call, string $expected): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Expected '.$expected.', got ');

        try {
            $result = $call($this->invoices(), new ClientsApi($this->http()));
            if ($result instanceof Generator) {
                $result->current();
            }
        } finally {
            self::assertSame(0, $this->mock->getRequestsCount());
        }
    }

    /**
     * @return iterable<string, array{Closure(InvoicesApi, ClientsApi): mixed, class-string}>
     */
    public static function foreignFilters(): iterable
    {
        /** @phpstan-ignore argument.type (absichtlich falscher Filter) */
        yield 'payments list' => [static fn (InvoicesApi $i): array => $i->payments->list(1, new CreditNotePaymentFilter()), InvoicePaymentFilter::class];
        /** @phpstan-ignore argument.type (absichtlich falscher Filter) */
        yield 'payments page' => [static fn (InvoicesApi $i): Page => $i->payments->listPage(1, new SubResourceFilter()), InvoicePaymentFilter::class];
        /** @phpstan-ignore argument.type (absichtlich falscher Filter) */
        yield 'payments iterate' => [static fn (InvoicesApi $i): Generator => $i->payments->iterateAll(1, new CreditNotePaymentFilter()), InvoicePaymentFilter::class];
        /** @phpstan-ignore argument.type (absichtlich falscher Filter) */
        yield 'property values list' => [static fn (InvoicesApi $i, ClientsApi $c): array => $c->propertyValues->list(1, new SupplierPropertyValueFilter()), ClientPropertyValueFilter::class];
        /** @phpstan-ignore argument.type (absichtlich falscher Filter) */
        yield 'property values page' => [static fn (InvoicesApi $i, ClientsApi $c): Page => $c->propertyValues->listPage(1, new SupplierPropertyValueFilter()), ClientPropertyValueFilter::class];
        /** @phpstan-ignore argument.type (absichtlich falscher Filter) */
        yield 'property values iterate' => [static fn (InvoicesApi $i, ClientsApi $c): Generator => $c->propertyValues->iterateAll(1, new SupplierPropertyValueFilter()), ClientPropertyValueFilter::class];
    }

    #[Test]
    public function createsSendTheParentIdFromTheArgument(): void
    {
        $this->respond(['invoice-tag' => ['id' => 1, 'invoice_id' => 7, 'name' => 'x']]);
        $this->respond(['recurring-email-receiver' => ['id' => 2, 'recurring_id' => 8, 'type' => 'Cc', 'address' => 'a@example.com']]);

        $bodies = [];
        $this->mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$bodies): MockResponse {
            $bodies[] = json_decode(\is_string($options['body'] ?? null) ? $options['body'] : '', true, flags: JSON_THROW_ON_ERROR);

            return array_shift($this->responses) ?? new MockResponse('', ['http_code' => 500]);
        });

        $tag = $this->invoices()->tags->create(7, new InvoiceTagCreateOptions(name: 'x'));
        $receiver = new RecurringsApi($this->http())->emailReceivers->create(8, new RecurringEmailReceiverCreateOptions(type: RecurringEmailReceiverType::CC, address: 'a@example.com'));

        self::assertSame(7, $tag->invoiceId);
        self::assertSame(8, $receiver->recurringId);
        self::assertSame([
            ['invoice-tag' => ['name' => 'x', 'invoice_id' => 7]],
            ['recurring-email-receiver' => ['type' => 'Cc', 'address' => 'a@example.com', 'recurring_id' => 8]],
        ], $bodies);
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
