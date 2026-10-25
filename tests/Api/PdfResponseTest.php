<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractApi;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\PdfType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Alle `pdfContent()`-Methoden prüfen, dass Billomat wirklich ein PDF geliefert hat.
 */
#[CoversClass(AbstractApi::class)]
#[CoversClass(ConfirmationsApi::class)]
#[CoversClass(CreditNotesApi::class)]
#[CoversClass(DeliveryNotesApi::class)]
#[CoversClass(InvoicesApi::class)]
#[CoversClass(LettersApi::class)]
#[CoversClass(OffersApi::class)]
#[CoversClass(RemindersApi::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
final class PdfResponseTest extends TestCase
{
    /**
     * @param class-string<InvoicesApi|OffersApi|ConfirmationsApi|CreditNotesApi|DeliveryNotesApi|LettersApi|RemindersApi> $class
     */
    #[Test]
    #[DataProvider('apis')]
    public function itReturnsThePdfBody(string $class, string $path): void
    {
        $url = null;
        $api = new $class($this->http(static function (string $method, string $requested) use (&$url): MockResponse {
            $url = $requested;

            return new MockResponse("%PDF-1.7\n…", ['response_headers' => ['Content-Type' => 'application/pdf']]);
        }));

        self::assertSame("%PDF-1.7\n…", $api->pdfContent(5));
        self::assertSame('https://mycompany.billomat.net/api/'.$path.'/5/pdf?format=pdf', $url);

        (void) $api->pdfContent(5, PdfType::SIGNED);
        self::assertSame('https://mycompany.billomat.net/api/'.$path.'/5/pdf?type=signed&format=pdf', $url);
    }

    /**
     * @param class-string<InvoicesApi|OffersApi|ConfirmationsApi|CreditNotesApi|DeliveryNotesApi|LettersApi|RemindersApi> $class
     */
    #[Test]
    #[DataProvider('apis')]
    public function pdfReturnsTheJsonModel(string $class, string $path): void
    {
        $url = null;
        $api = new $class($this->http(static function (string $method, string $requested) use (&$url): MockResponse {
            $url = $requested;

            return new MockResponse('{"pdf":{"id":"9","filename":"x.pdf","mimetype":"application/pdf","base64file":"JVBERi0="}}');
        }));

        $pdf = $api->pdf(5, PdfType::PRINT);

        self::assertSame(9, $pdf->id);
        self::assertSame('JVBERi0=', $pdf->base64file);
        self::assertSame('https://mycompany.billomat.net/api/'.$path.'/5/pdf?type=print', $url);
    }

    /**
     * @param class-string<InvoicesApi|OffersApi|ConfirmationsApi|CreditNotesApi|DeliveryNotesApi|LettersApi|RemindersApi> $class
     */
    #[Test]
    #[DataProvider('invalidBodies')]
    public function itRejectsBodiesThatAreNoPdf(string $class, string $path, string $body, string $reason): void
    {
        $api = new $class($this->http(static fn (): MockResponse => new MockResponse($body)));

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('Unexpected response from Billomat for GET /'.$path.'/5/pdf: '.$reason.'.');

        (void) $api->pdfContent(5);
    }

    /**
     * @return iterable<string, array{class-string<InvoicesApi|OffersApi|ConfirmationsApi|CreditNotesApi|DeliveryNotesApi|LettersApi|RemindersApi>, string}>
     */
    public static function apis(): iterable
    {
        yield 'invoices' => [InvoicesApi::class, 'invoices'];
        yield 'offers' => [OffersApi::class, 'offers'];
        yield 'confirmations' => [ConfirmationsApi::class, 'confirmations'];
        yield 'credit notes' => [CreditNotesApi::class, 'credit-notes'];
        yield 'delivery notes' => [DeliveryNotesApi::class, 'delivery-notes'];
        yield 'letters' => [LettersApi::class, 'letters'];
        yield 'reminders' => [RemindersApi::class, 'reminders'];
    }

    /**
     * @return iterable<string, array{class-string<InvoicesApi|OffersApi|ConfirmationsApi|CreditNotesApi|DeliveryNotesApi|LettersApi|RemindersApi>, string, string, string}>
     */
    public static function invalidBodies(): iterable
    {
        foreach (self::apis() as $name => [$class, $path]) {
            yield $name.': leer' => [$class, $path, '', 'empty body instead of a PDF'];
            yield $name.': HTML' => [$class, $path, '<html>Wartung</html>', 'the body is not a PDF'];
            yield $name.': JSON' => [$class, $path, '{"pdf":{}}', 'the body is not a PDF'];
            yield $name.': Präfix nicht am Anfang' => [$class, $path, ' %PDF-1.7', 'the body is not a PDF'];
        }
    }

    private function http(callable $respond): BillomatHttpClient
    {
        return new BillomatHttpClient(
            new Psr18Client(new MockHttpClient($respond)),
            new BillomatConfig('mycompany', 'secret-key', retry: RetryPolicy::none()),
        );
    }
}
