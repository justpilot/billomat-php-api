<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Closure;
use Justpilot\Billomat\Api\AbstractApi;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Billomat beantwortet `POST /{dokumente}/{id}/email` und `/mail` mit 200 OK, leerem Body
 * und `text/html` (in der Sandbox gemessen, die Doku beschreibt keine Antwort). Der Versand
 * ist dann erfolgt; eine Exception würde Aufrufer zu einem doppelten Versand verleiten.
 */
#[CoversClass(AbstractApi::class)]
#[CoversClass(InvoicesApi::class)]
#[CoversClass(OffersApi::class)]
#[CoversClass(ConfirmationsApi::class)]
#[CoversClass(CreditNotesApi::class)]
#[CoversClass(DeliveryNotesApi::class)]
#[CoversClass(LettersApi::class)]
#[CoversClass(RemindersApi::class)]
#[UsesNamespace('Justpilot\\Billomat\\Config')]
#[UsesNamespace('Justpilot\\Billomat\\Exception')]
#[UsesNamespace('Justpilot\\Billomat\\Http')]
final class SendActionsEmptyResponseTest extends TestCase
{
    /**
     * @return iterable<string, array{string, Closure(BillomatHttpClient): bool}>
     */
    public static function senders(): iterable
    {
        yield 'invoices email' => ['invoices/7/email', static fn (BillomatHttpClient $h): bool => new InvoicesApi($h)->email(7)];
        yield 'invoices mail' => ['invoices/7/mail', static fn (BillomatHttpClient $h): bool => new InvoicesApi($h)->mail(7)];
        yield 'offers email' => ['offers/7/email', static fn (BillomatHttpClient $h): bool => new OffersApi($h)->email(7)];
        yield 'confirmations email' => ['confirmations/7/email', static fn (BillomatHttpClient $h): bool => new ConfirmationsApi($h)->email(7)];
        yield 'credit notes email' => ['credit-notes/7/email', static fn (BillomatHttpClient $h): bool => new CreditNotesApi($h)->email(7)];
        yield 'delivery notes email' => ['delivery-notes/7/email', static fn (BillomatHttpClient $h): bool => new DeliveryNotesApi($h)->email(7)];
        yield 'letters email' => ['letters/7/email', static fn (BillomatHttpClient $h): bool => new LettersApi($h)->email(7)];
        yield 'reminders email' => ['reminders/7/email', static fn (BillomatHttpClient $h): bool => new RemindersApi($h)->email(7)];
    }

    /**
     * @param Closure(BillomatHttpClient): bool $send
     */
    #[Test]
    #[DataProvider('senders')]
    public function sendingAcceptsBillomatsEmptyResponse(string $path, Closure $send): void
    {
        $method = null;
        $url = null;
        $mock = new MockHttpClient(static function (string $m, string $u) use (&$method, &$url): MockResponse {
            $method = $m;
            $url = $u;

            return new MockResponse('', ['http_code' => 200, 'response_headers' => ['content-type' => 'text/html; charset=UTF-8']]);
        });

        self::assertTrue($send(new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'k'))));
        self::assertSame('POST', $method);
        self::assertSame("https://mycompany.billomat.net/api/{$path}", $url);
    }

    /**
     * @param Closure(BillomatHttpClient): bool $send
     */
    #[Test]
    #[DataProvider('senders')]
    public function sendingStillThrowsOnHttpErrors(string $path, Closure $send): void
    {
        $mock = new MockHttpClient(new MockResponse('{"errors":{"error":"Invalid recipient"}}', ['http_code' => 422]));

        $this->expectException(ValidationException::class);

        $send(new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'k')));
    }
}
