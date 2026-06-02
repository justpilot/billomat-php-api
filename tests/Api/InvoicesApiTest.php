<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceEmailOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceMailOptions;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\InvoiceUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\InvoiceGroupBy;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoiceGroup;
use Justpilot\Billomat\Model\InvoicePdf;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(InvoicesApi::class)]
#[CoversClass(InvoiceCreateOptions::class)]
#[CoversClass(InvoiceUpdateOptions::class)]
#[CoversClass(Invoice::class)]
#[CoversClass(InvoiceGroup::class)]
#[CoversClass(InvoiceGroupBy::class)]
final class InvoicesApiTest extends TestCase
{
    #[Test]
    public function itListsInvoicesAndPassesFilters(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoices' => [
                    'invoice' => [
                        [
                            'id' => 1,
                            'client_id' => 123,
                            'status' => InvoiceStatus::DRAFT->value,
                            'invoice_number' => null,
                            'date' => '2025-01-01',
                            'due_date' => '2025-01-15',
                            'currency_code' => 'EUR',
                            'total_gross' => 119.00,
                            'total_net' => 100.00,
                        ],
                        [
                            'id' => 2,
                            'client_id' => 456,
                            'status' => InvoiceStatus::PAID->value,
                            'invoice_number' => 'RE-2025-0002',
                            'date' => '2025-01-02',
                            'due_date' => '2025-01-16',
                            'currency_code' => 'EUR',
                            'total_gross' => 59.50,
                            'total_net' => 50.00,
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $filters = ['per_page' => 50];

        $invoices = $api->list($filters);

        self::assertIsArray($invoices);
        self::assertCount(2, $invoices);
        self::assertContainsOnlyInstancesOf(Invoice::class, $invoices);

        $first = $invoices[0];
        self::assertSame(1, $first->id);
        self::assertSame(123, $first->clientId);
        self::assertSame(InvoiceStatus::DRAFT, $first->status);
        self::assertNull($first->invoiceNumber);
        self::assertSame('2025-01-01', $first->date?->format('Y-m-d'));
        self::assertSame('2025-01-15', $first->dueDate?->format('Y-m-d'));
        self::assertSame('EUR', $first->currencyCode);
        self::assertSame(119.0, $first->totalGross);
        self::assertSame(100.0, $first->totalNet);

        // Request prüfen
        self::assertSame('GET', $captured['method']);

        $url = $captured['url'];
        $parts = parse_url($url);

        self::assertSame('/api/invoices', $parts['path'] ?? null);

        $query = [];
        if (isset($parts['query'])) {
            parse_str($parts['query'], $query);
        }

        self::assertSame(50, (int) ($query['per_page'] ?? 0));
    }

    #[Test]
    public function itGetsSingleInvoiceById(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoice' => [
                    'id' => 1234,
                    'client_id' => 999,
                    'status' => InvoiceStatus::OPEN->value,
                    'invoice_number' => 'RE-2025-0001',
                    'date' => '2025-02-01',
                    'due_date' => '2025-02-15',
                    'currency_code' => 'EUR',
                    'total_gross' => 238.00,
                    'total_net' => 200.00,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $invoice = $api->get(1234);

        self::assertInstanceOf(Invoice::class, $invoice);
        self::assertSame(1234, $invoice->id);
        self::assertSame(999, $invoice->clientId);
        self::assertSame(InvoiceStatus::OPEN, $invoice->status);
        self::assertSame('RE-2025-0001', $invoice->invoiceNumber);
        self::assertSame('2025-02-01', $invoice->date?->format('Y-m-d'));
        self::assertSame('2025-02-15', $invoice->dueDate?->format('Y-m-d'));
        self::assertSame('EUR', $invoice->currencyCode);
        self::assertSame(238.0, $invoice->totalGross);
        self::assertSame(200.0, $invoice->totalNet);

        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/1234',
            $captured['url']
        );
    }

    #[Test]
    public function itCreatesANewInvoiceDraftViaPost(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Response einer frisch angelegten Rechnung im Status DRAFT
            $body = json_encode([
                'invoice' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => InvoiceStatus::DRAFT->value,
                    'invoice_number' => null, // laut Doku: bei DRAFT leer
                    'date' => '2025-03-01',
                    'due_date' => '2025-03-15',
                    'currency_code' => 'EUR',
                    'total_gross' => 0.0,
                    'total_net' => 0.0,
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        // --- Build invoice create options ---
        $opts = new InvoiceCreateOptions(
            clientId: 123,
        );

        $opts->date = new DateTimeImmutable('2025-03-01');
        $opts->currencyCode = 'EUR';
        $opts->title = 'Rechnung März';
        $opts->label = 'Leistungen März 2025';
        $opts->note = 'Vielen Dank für Ihren Auftrag.';

        // --- Add a single invoice item ---
        $item = new InvoiceItemCreateOptions(
            quantity: 2.0,
            unitPrice: 100.0,
        );
        $item->title = 'Beratung';
        $item->description = 'Leistungspaket März';
        $item->unit = 'Stunde';
        $item->taxRate = 19.0;

        $opts->addItem($item);

        // --- Execute ---
        $created = $api->create($opts);

        // --- Assertions on response mapping ---
        self::assertSame(777, $created->id);
        self::assertSame(123, $created->clientId);
        self::assertSame(InvoiceStatus::DRAFT, $created->status);
        self::assertNull($created->invoiceNumber);
        self::assertSame('2025-03-01', $created->date?->format('Y-m-d'));
        self::assertSame('2025-03-15', $created->dueDate?->format('Y-m-d'));
        self::assertSame('EUR', $created->currencyCode);

        // --- Assertions on outgoing request ---
        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices',
            $captured['url']
        );

        $options = $captured['options'] ?? [];
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body'])) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertArrayHasKey('invoice', $payload);

        $invoicePayload = $payload['invoice'];

        // Basic fields
        self::assertSame(123, $invoicePayload['client_id'] ?? null);
        self::assertSame('2025-03-01', $invoicePayload['date'] ?? null);
        self::assertSame('EUR', $invoicePayload['currency_code'] ?? null);
        self::assertSame('Rechnung März', $invoicePayload['title'] ?? null);
        self::assertSame('Leistungen März 2025', $invoicePayload['label'] ?? null);
        self::assertSame('Vielen Dank für Ihren Auftrag.', $invoicePayload['note'] ?? null);

        // Items block
        self::assertArrayHasKey('invoice-items', $invoicePayload);
        self::assertArrayHasKey('invoice-item', $invoicePayload['invoice-items']);

        $items = $invoicePayload['invoice-items']['invoice-item'];
        self::assertIsArray($items);
        self::assertCount(1, $items);

        $firstItem = $items[0];

        self::assertSame(2.0, $firstItem['quantity'] ?? null);
        self::assertSame(100.0, $firstItem['unit_price'] ?? null);
        self::assertSame('Beratung', $firstItem['title'] ?? null);
        self::assertSame('Leistungspaket März', $firstItem['description'] ?? null);
        self::assertSame('Stunde', $firstItem['unit'] ?? null);
        self::assertSame(19.0, $firstItem['tax_rate'] ?? null);

        // id darf im Payload NICHT gesetzt sein
        self::assertArrayNotHasKey('id', $invoicePayload);
    }

    #[Test]
    public function itCompletesInvoiceViaPutAndOptionalTemplateId(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Billomat verhält sich in echt so: 200 OK mit leerem Body
            return new MockResponse('', ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $templateId = 5;

        $result = $api->complete(777, $templateId);

        // complete() gibt nur true zurück, wenn kein HTTP-Fehler kam
        self::assertTrue($result);

        // Request prüfen
        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/777/complete',
            $captured['url']
        );

        $options = $captured['options'] ?? [];

        // Symfony HttpClient benutzt normalerweise 'json' für JSON-Bodies
        $payload = $options['json'] ?? null;

        // Fallback: falls aus irgendeinem Grund ein roher Body gesetzt wurde
        if (null === $payload && isset($options['body']) && \is_string($options['body'])) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload);
        self::assertArrayHasKey('invoice', $payload);
        self::assertSame(
            $templateId,
            $payload['invoice']['template_id'] ?? null
        );
    }

    #[Test]
    public function itDeletesDraftInvoiceViaDelete(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            // Billomat kann 200 oder 204 ohne Body zurückgeben
            return new MockResponse('', ['http_code' => 204]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $result = $api->delete(777);

        self::assertTrue($result);

        self::assertSame('DELETE', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/777',
            $captured['url']
        );
    }

    #[Test]
    public function deletePropagatesValidationExceptionForNonDraftInvoice(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url, array $options): MockResponse {
            // Billomat meldet z.B. 400, wenn Rechnung nicht DRAFT ist
            $body = json_encode([
                'errors' => [
                    'error' => 'Invoice can only be deleted in status DRAFT.',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 400]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $this->expectException(ValidationException::class);

        $api->delete(777);
    }

    #[Test]
    public function itCancelsInvoice(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new InvoicesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->cancel(999);

        self::assertTrue($result);

        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/999/cancel',
            $captured['url']
        );
    }

    #[Test]
    public function itUncancelsInvoice(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new InvoicesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->uncancel(999);

        self::assertTrue($result);

        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/999/uncancel',
            $captured['url']
        );
    }

    #[Test]
    public function itFetchesInvoicePdfInJsonMode(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'pdf' => [
                    'id' => 4882,
                    'created' => '2009-09-02T12:04:15+02:00',
                    'invoice_id' => 240,
                    'filename' => 'invoice_123.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 70137,
                    'base64file' => base64_encode('%PDF-FAKE%'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $pdf = $api->pdf(240);

        self::assertInstanceOf(InvoicePdf::class, $pdf);
        self::assertSame(4882, $pdf->id);
        self::assertSame(240, $pdf->invoiceId);
        self::assertSame('invoice_123.pdf', $pdf->filename);
        self::assertSame('application/pdf', $pdf->mimeType);
        self::assertSame(70137, $pdf->fileSize);

        self::assertInstanceOf(DateTimeImmutable::class, $pdf->created);
        self::assertSame(
            '2009-09-02T12:04:15+02:00',
            $pdf->created?->format('c')
        );

        $binary = $pdf->getBinary();
        self::assertNotSame('', $binary);
        self::assertStringStartsWith('%PDF-FAKE%', $binary);

        // Request-Checks
        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/240/pdf',
            $captured['url']
        );
    }

    #[Test]
    public function itPassesTypeQueryParameterForPdf(): void
    {
        $capturedUrl = null;

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$capturedUrl): MockResponse {
            $capturedUrl = $url;

            $body = json_encode([
                'pdf' => [
                    'id' => 1,
                    'created' => '2025-01-01T10:00:00+01:00',
                    'invoice_id' => 999,
                    'filename' => 'dummy.pdf',
                    'mimetype' => 'application/pdf',
                    'filesize' => 1234,
                    'base64file' => base64_encode('PDF'),
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $api->pdf(999, InvoicePdfType::SIGNED);

        self::assertNotNull($capturedUrl);
        self::assertStringContainsString(
            '/invoices/999/pdf',
            $capturedUrl
        );
        self::assertStringContainsString(
            'type=signed',
            $capturedUrl,
            'Expected type=signed query parameter in PDF URL'
        );
    }

    #[Test]
    public function itCanFetchRawPdfBinary(): void
    {
        $capturedUrl = null;

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$capturedUrl): MockResponse {
            $capturedUrl = $url;

            $binaryPdf = '%PDF-1.4 FAKE BINARY%';

            return new MockResponse(
                $binaryPdf,
                [
                    'http_code' => 200,
                    'response_headers' => [
                        'content-type' => 'application/pdf',
                    ],
                ]
            );
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $result = $api->pdf(
            id: 777,
            type: null,
            rawPdf: true
        );

        self::assertIsString($result);
        self::assertStringStartsWith('%PDF-1.4', $result);

        self::assertNotNull($capturedUrl);
        self::assertStringContainsString(
            '/invoices/777/pdf',
            $capturedUrl
        );
        self::assertStringContainsString(
            'format=pdf',
            $capturedUrl,
            'Expected format=pdf query parameter in raw PDF URL'
        );
    }

    #[Test]
    public function itUpdatesInvoiceDraftViaPut(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoice' => [
                    'id' => 777,
                    'client_id' => 123,
                    'status' => 'DRAFT',
                    'date' => '2025-03-10',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $opts = new InvoiceUpdateOptions();
        $opts->date = new DateTimeImmutable('2025-03-10');

        $updated = $api->update(777, $opts);

        self::assertSame(777, $updated->id);
        self::assertSame('2025-03-10', $updated->date?->format('Y-m-d'));

        // Request prüfen
        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/777',
            $captured['url']
        );

        $options = $captured['options'] ?? [];

        // Payload robust extrahieren: Symfony kann json -> body normalisieren
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body'])) {
            $body = $options['body'];

            if (\is_string($body) && '' !== $body) {
                $payload = json_decode($body, true, flags: JSON_THROW_ON_ERROR);
            } elseif (\is_array($body)) {
                // selten, aber möglich
                $payload = $body;
            }
        }

        self::assertIsArray($payload);
        self::assertSame(['invoice' => ['date' => '2025-03-10']], $payload);

        // Optional: Content-Type prüfen (kann als "headers" array oder "normalized_headers" auftauchen)
        $headers = $options['headers'] ?? [];
        $flat = [];

        foreach ($headers as $k => $v) {
            if (\is_int($k) && \is_string($v) && str_contains($v, ':')) {
                [$hn, $hv] = explode(':', $v, 2);
                $flat[strtolower(trim($hn))] = trim($hv);
            } elseif (\is_string($k)) {
                $flat[strtolower($k)] = \is_array($v) ? implode(', ', $v) : (string) $v;
            }
        }

        // je nach Symfony-Version kann das auch intern gesetzt werden – daher "optional soft check"
        if (isset($flat['content-type'])) {
            self::assertStringContainsString('application/json', strtolower($flat['content-type']));
        }
    }

    #[Test]
    public function itEmailsInvoiceWithRecipientsAndSubject(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            // Billomat antwortet i. d. R. mit leerem Body oder Bestätigungsobjekt
            return new MockResponse('{"@status":"OK"}', ['http_code' => 200]);
        });

        $api = new InvoicesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new InvoiceEmailOptions();
        $opts->from = 'me@example.com';
        $opts->to = ['kunde@example.com'];
        $opts->cc = ['buchhaltung@example.com'];
        $opts->bcc = ['archiv@example.com'];
        $opts->subject = 'Ihre Rechnung';
        $opts->body = 'Anbei …';
        $opts->filename = 'rechnung-2026-001';
        $opts->attachments = [
            ['filename' => 'agb.pdf', 'mimetype' => 'application/pdf', 'base64file' => 'QUJD'],
        ];

        $result = $api->email(777, $opts);

        self::assertTrue($result);
        self::assertSame('POST', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/777/email',
            $captured['url']
        );

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertArrayHasKey('email', $payload);

        $email = $payload['email'];
        self::assertSame('me@example.com', $email['from'] ?? null);
        self::assertSame(['kunde@example.com'], $email['recipients']['to'] ?? null);
        self::assertSame(['buchhaltung@example.com'], $email['recipients']['cc'] ?? null);
        self::assertSame(['archiv@example.com'], $email['recipients']['bcc'] ?? null);
        self::assertSame('Ihre Rechnung', $email['subject'] ?? null);
        self::assertSame('Anbei …', $email['body'] ?? null);
        self::assertSame('rechnung-2026-001', $email['filename'] ?? null);

        // Attachments-Envelope
        self::assertArrayHasKey('attachments', $email);
        self::assertSame([
            ['filename' => 'agb.pdf', 'mimetype' => 'application/pdf', 'base64file' => 'QUJD'],
        ], $email['attachments']['attachment']);
    }

    #[Test]
    public function emailWithoutOptionsSendsEmptyEnvelopeToUseDefaults(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new InvoicesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->email(123);

        self::assertTrue($result);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/invoices/123/email', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertSame(['email' => []], $payload);
    }

    #[Test]
    public function itSendsInvoiceViaPixelletterMail(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('{}', ['http_code' => 200]);
        });

        $api = new InvoicesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new InvoiceMailOptions();
        $opts->color = true;
        $opts->duplex = false;
        $opts->paperWeight = '90';
        $opts->attachments = [
            ['filename' => 'agb.pdf', 'mimetype' => 'application/pdf', 'base64file' => 'QUJD'],
        ];

        $result = $api->mail(888, $opts);

        self::assertTrue($result);
        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/invoices/888/mail', $captured['url']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertArrayHasKey('mail', $payload);
        self::assertSame(1, $payload['mail']['color']);
        self::assertSame(0, $payload['mail']['duplex']);
        self::assertSame('90', $payload['mail']['paper_weight']);
        self::assertArrayNotHasKey('recipient_address', $payload['mail']);
        self::assertCount(1, $payload['mail']['attachments']['attachment']);
        self::assertSame('agb.pdf', $payload['mail']['attachments']['attachment'][0]['filename']);
    }

    #[Test]
    public function itUploadsSignaturePdf(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new InvoicesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $base64 = base64_encode('PDF-FAKE-CONTENT');
        $result = $api->uploadSignature(555, $base64);

        self::assertTrue($result);
        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/555/upload-signature',
            $captured['url']
        );

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertSame(['upload' => ['base64file' => $base64]], $payload);
    }

    #[Test]
    public function itSendsInvoiceToEncashment(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new InvoicesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $result = $api->encash(101);

        self::assertTrue($result);
        self::assertSame('PUT', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/invoices/101/encash',
            $captured['url']
        );
    }

    #[Test]
    public function updateOptionsSerializesAllNewFields(): void
    {
        $opts = new InvoiceUpdateOptions();

        $opts->clientId = 42;
        $opts->contactId = 17;
        $opts->address = "Beispiel GmbH\nMusterstr. 1\n12345 Berlin";
        $opts->numberPre = 'RE-';
        $opts->number = 9001;
        $opts->numberLength = 5;
        $opts->discountRate = 2.0;
        $opts->discountDays = 7;
        $opts->discountDate = new DateTimeImmutable('2026-01-22');
        $opts->offerId = 1001;
        $opts->confirmationId = 2002;
        $opts->recurringId = 3003;
        $opts->invoiceId = 4004;
        $opts->freeTextId = 5005;
        $opts->templateId = 6006;

        $payload = $opts->toArray();

        self::assertSame(42, $payload['client_id']);
        self::assertSame(17, $payload['contact_id']);
        self::assertSame("Beispiel GmbH\nMusterstr. 1\n12345 Berlin", $payload['address']);
        self::assertSame('RE-', $payload['number_pre']);
        self::assertSame(9001, $payload['number']);
        self::assertSame(5, $payload['number_length']);
        self::assertSame(2.0, $payload['discount_rate']);
        self::assertSame(7, $payload['discount_days']);
        self::assertSame('2026-01-22', $payload['discount_date']);
        self::assertSame(1001, $payload['offer_id']);
        self::assertSame(2002, $payload['confirmation_id']);
        self::assertSame(3003, $payload['recurring_id']);
        self::assertSame(4004, $payload['invoice_id']);
        self::assertSame(5005, $payload['free_text_id']);
        self::assertSame(6006, $payload['template_id']);
    }

    /**
     * Liest den JSON-Payload aus den `options` von MockHttpClient.
     *
     * MockHttpClient verschiebt `options[json]` häufig in `options[body]` als
     * vorab serialisierten String. Symmetrisch zur Pattern an anderen Stellen
     * in dieser Test-Datei (z. B. test_it_completes_invoice_...).
     *
     * @param array<string,mixed> $options
     *
     * @return array<string,mixed>|null
     */
    private function extractJsonPayload(array $options): ?array
    {
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body']) && '' !== $options['body']) {
            $decoded = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);

            if (\is_array($decoded)) {
                return $decoded;
            }
        }

        return \is_array($payload) ? $payload : null;
    }

    #[Test]
    public function itListsInvoicesGroupedByMultipleCriteria(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $body = json_encode([
                'invoice-groups' => [
                    'invoice-group' => [
                        [
                            'total_gross' => 347.28,
                            'total_net' => 291.83,
                            'client_id' => 476,
                            'invoice-params' => ['client_id' => 476],
                        ],
                        [
                            'total_gross' => 1127.53,
                            'total_net' => 947.50,
                            'client_id' => 477,
                            'invoice-params' => ['client_id' => 477],
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $http = new BillomatHttpClient($mock, $config);
        $api = new InvoicesApi($http);

        $groups = $api->listGrouped([InvoiceGroupBy::CLIENT, InvoiceGroupBy::YEAR]);

        self::assertCount(2, $groups);
        self::assertContainsOnlyInstancesOf(InvoiceGroup::class, $groups);
        self::assertSame(347.28, $groups[0]->totalGross);
        self::assertSame(476, $groups[0]->clientId);
        self::assertSame(['client_id' => 476], $groups[0]->invoiceParams);

        $parts = parse_url($captured['url']);
        self::assertSame('/api/invoices', $parts['path'] ?? null);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame('client,year', $query['group_by'] ?? null);
    }

    #[Test]
    public function itAcceptsSingleGroupByEnumValue(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'invoice-groups' => [
                    'invoice-group' => [
                        'total_gross' => 100.0,
                        'total_net' => 84.03,
                        'status' => 'OPEN',
                        'invoice-params' => ['status' => 'OPEN'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new InvoicesApi($http);

        $groups = $api->listGrouped(InvoiceGroupBy::STATUS);

        self::assertCount(1, $groups);
        self::assertSame('OPEN', $groups[0]->status);

        $parts = parse_url($captured['url']);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame('status', $query['group_by'] ?? null);
    }

    #[Test]
    public function completeMapsHttpErrorToValidationException(): void
    {
        // Regression: vor Bug-Fix verschluckte `complete()` 4xx/5xx und gab still `false` zurück,
        // weil $response->getStatusCode() in Symfony's HttpClient bei 4xx/5xx NICHT wirft.
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['errors' => ['error' => 'Invoice cannot be completed.']], JSON_THROW_ON_ERROR),
            ['http_code' => 422],
        ));

        $api = new InvoicesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(ValidationException::class);

        $api->complete(777);
    }
}
