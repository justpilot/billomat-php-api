<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Closure;
use Generator;
use Justpilot\Billomat\Api\ContactsApi;
use Justpilot\Billomat\Api\CountriesApi;
use Justpilot\Billomat\Api\CurrenciesApi;
use Justpilot\Billomat\Api\ReminderTextsApi;
use Justpilot\Billomat\Api\TaxesApi;
use Justpilot\Billomat\Api\TemplatesApi;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Pagination\Page;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
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
use const PHP_URL_PATH;

/**
 * `listPage()`/`iterateAll()` der Ressourcen, die bis 4.0.0-alpha.1 nur `list()` hatten.
 */
#[CoversClass(ContactsApi::class)]
#[CoversClass(TaxesApi::class)]
#[CoversClass(TemplatesApi::class)]
#[CoversClass(ReminderTextsApi::class)]
#[CoversClass(CountriesApi::class)]
#[CoversClass(CurrenciesApi::class)]
#[UsesClass(BillomatClient::class)]
#[UsesNamespace('Justpilot\\Billomat\\Api')]
#[UsesNamespace('Justpilot\\Billomat\\Config')]
#[UsesNamespace('Justpilot\\Billomat\\Http')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
#[UsesNamespace('Justpilot\\Billomat\\Pagination')]
final class ListPagingTest extends TestCase
{
    /** @var list<CapturedRequest> */
    private array $requests = [];

    /**
     * Pfad, Envelope-Schlüssel, `listPage(['page' => 2, 'per_page' => 2])` und `iterateAll(['name' => 'x'], 2)`.
     *
     * @return iterable<string, array{non-empty-string, string, string, Closure(BillomatClient): Page<object>, Closure(BillomatClient): Generator<int, object>}>
     */
    public static function resources(): iterable
    {
        $page = ['page' => 2, 'per_page' => 2];
        $filter = ['name' => 'x'];

        yield 'contacts' => ['/contacts', 'contacts', 'contact', static fn (BillomatClient $b): Page => $b->contacts->listPage(9, $page), static fn (BillomatClient $b): Generator => $b->contacts->iterateAll(9, $filter, 2)];
        yield 'taxes' => ['/taxes', 'taxes', 'tax', static fn (BillomatClient $b): Page => $b->taxes->listPage($page), static fn (BillomatClient $b): Generator => $b->taxes->iterateAll($filter, 2)];
        yield 'templates' => ['/templates', 'templates', 'template', static fn (BillomatClient $b): Page => $b->templates->listPage($page), static fn (BillomatClient $b): Generator => $b->templates->iterateAll($filter, 2)];
        yield 'reminder texts' => ['/reminder-texts', 'reminder-texts', 'reminder-text', static fn (BillomatClient $b): Page => $b->reminderTexts->listPage($page), static fn (BillomatClient $b): Generator => $b->reminderTexts->iterateAll($filter, 2)];
    }

    /**
     * @param non-empty-string                                $path
     * @param Closure(BillomatClient): Page<object>           $listPage
     * @param Closure(BillomatClient): Generator<int, object> $iterateAll
     */
    #[Test]
    #[DataProvider('resources')]
    public function listPageReturnsItemsAndPageInfo(string $path, string $outer, string $inner, Closure $listPage, Closure $iterateAll): void
    {
        $billomat = $this->client($this->page($outer, $inner, [1, 2], page: 2, perPage: 2, total: 5));

        $page = $listPage($billomat);

        self::assertCount(2, $page->items);
        self::assertSame(2, $page->info->page);
        self::assertSame(2, $page->info->perPage);
        self::assertSame(5, $page->info->total);
        self::assertStringEndsWith($path, (string) parse_url($this->requests[0]->url, PHP_URL_PATH));
        self::assertSame('2', $this->requests[0]->query()['page'] ?? null);
    }

    /**
     * @param non-empty-string                                $path
     * @param Closure(BillomatClient): Page<object>           $listPage
     * @param Closure(BillomatClient): Generator<int, object> $iterateAll
     */
    #[Test]
    #[DataProvider('resources')]
    public function iterateAllFetchesEveryPage(string $path, string $outer, string $inner, Closure $listPage, Closure $iterateAll): void
    {
        $billomat = $this->client(
            $this->page($outer, $inner, [1, 2], page: 1, perPage: 2, total: 3),
            $this->page($outer, $inner, [3], page: 2, perPage: 2, total: 3),
        );

        $items = iterator_to_array($iterateAll($billomat), false);

        self::assertCount(3, $items);
        self::assertCount(2, $this->requests);
        self::assertSame(['1', '2'], array_map(static fn (CapturedRequest $r): mixed => $r->query()['page'] ?? null, $this->requests));
        self::assertSame('x', $this->requests[1]->query()['name'] ?? null);
    }

    /**
     * @return iterable<string, array{string, string, Closure(BillomatClient): Generator<int, object>}>
     */
    public static function defaultIterators(): iterable
    {
        yield 'contacts' => ['contacts', 'contact', static fn (BillomatClient $b): Generator => $b->contacts->iterateAll(9)];
        yield 'taxes' => ['taxes', 'tax', static fn (BillomatClient $b): Generator => $b->taxes->iterateAll()];
        yield 'templates' => ['templates', 'template', static fn (BillomatClient $b): Generator => $b->templates->iterateAll()];
        yield 'reminder texts' => ['reminder-texts', 'reminder-text', static fn (BillomatClient $b): Generator => $b->reminderTexts->iterateAll()];
    }

    /**
     * @param Closure(BillomatClient): Generator<int, object> $iterateAll
     */
    #[Test]
    #[DataProvider('defaultIterators')]
    public function iterateAllUsesPagesOfHundredByDefault(string $outer, string $inner, Closure $iterateAll): void
    {
        $billomat = $this->client($this->page($outer, $inner, [1], page: 1, perPage: 100, total: 1));

        self::assertCount(1, iterator_to_array($iterateAll($billomat), false));
        self::assertSame('100', $this->requests[0]->query()['per_page'] ?? null);
    }

    #[Test]
    public function contactsAlwaysSendTheClientId(): void
    {
        $billomat = $this->client(
            $this->page('contacts', 'contact', [1], page: 1, perPage: 100, total: 1),
            $this->page('contacts', 'contact', [1], page: 1, perPage: 100, total: 1),
            $this->page('contacts', 'contact', [1], page: 1, perPage: 100, total: 1),
        );

        (void) $billomat->contacts->list(9, ['client_id' => 1]);
        (void) $billomat->contacts->listPage(9, ['client_id' => 1]);
        foreach ($billomat->contacts->iterateAll(9, ['client_id' => 1]) as $contact) {
            self::assertSame(1, $contact->id);
        }

        foreach ($this->requests as $request) {
            self::assertSame('9', $request->query()['client_id'] ?? null);
        }
        self::assertCount(3, $this->requests);
    }

    #[Test]
    public function countriesAndCurrenciesPassFiltersToList(): void
    {
        $billomat = $this->client(
            $this->page('countries', 'country', [1], page: 1, perPage: 100, total: 1),
            $this->page('currencies', 'currency', [1], page: 1, perPage: 100, total: 1),
        );

        (void) $billomat->countries->list(['code' => 'DE']);
        (void) $billomat->currencies->list(['code' => 'EUR']);

        self::assertSame('DE', $this->requests[0]->query()['code'] ?? null);
        self::assertSame('EUR', $this->requests[1]->query()['code'] ?? null);
    }

    private function client(MockResponse ...$responses): BillomatClient
    {
        $responses = array_values($responses);
        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$responses): MockResponse {
            $this->requests[] = CapturedRequest::of($method, $url, $options);

            return array_shift($responses) ?? self::fail('Unerwarteter weiterer Request: '.$url);
        });

        return new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));
    }

    /**
     * @param list<int> $ids
     */
    private function page(string $outer, string $inner, array $ids, int $page, int $perPage, int $total): MockResponse
    {
        $rows = array_map(static fn (int $id): array => ['id' => (string) $id, 'client_id' => '9', 'name' => 'n'.$id], $ids);

        return new MockResponse(json_encode([
            $outer => [$inner => $rows, '@page' => (string) $page, '@per_page' => (string) $perPage, '@total' => (string) $total],
        ], JSON_THROW_ON_ERROR));
    }
}
