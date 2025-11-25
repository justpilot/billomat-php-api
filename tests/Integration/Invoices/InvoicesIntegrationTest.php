<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Invoices;

use Faker\Factory as FakerFactory;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\TestCase;

final class InvoicesIntegrationTest extends TestCase
{
    #[Group("integration")]
    public function test_can_list_invoices_from_sandbox(): void
    {
        $billomatId = getenv('BILLOMAT_ID');
        $apiKey = getenv('BILLOMAT_API_KEY');

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('Environment variables BILLOMAT_ID or BILLOMAT_API_KEY missing.');
        }

        $billomat = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        $invoices = $billomat->invoices->list(['per_page' => 5]);

        self::assertIsArray($invoices);
        self::assertContainsOnlyInstancesOf(Invoice::class, $invoices);

        if ($invoices !== []) {
            $first = $invoices[0];
            self::assertNotNull($first->id);
            self::assertIsInt($first->clientId);
            self::assertInstanceOf(InvoiceStatus::class, $first->status);
        }
    }

    #[Group("integration")]
    public function test_can_create_invoice_draft_in_sandbox(): void
    {
        $billomatId = getenv('BILLOMAT_ID');
        $apiKey = getenv('BILLOMAT_API_KEY');

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('Environment variables BILLOMAT_ID or BILLOMAT_API_KEY missing.');
        }

        $billomat = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        $faker = FakerFactory::create('de_DE');

        // 1) Einen Client für die Rechnung bestimmen
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ($clients === []) {
            // Fallback: Minimalen Client erstellen, falls noch keiner existiert
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
            );
            $clientOptions->email = $faker->unique()->safeEmail();
            $clientOptions->countryCode = 'DE';

            $createdClient = $billomat->clients->create($clientOptions);

            self::assertInstanceOf(Client::class, $createdClient);
            self::assertNotNull($createdClient->id);

            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId, 'Client ID must not be null for invoice creation');

        // 2) InvoiceCreateOptions vorbereiten
        $today = new \DateTimeImmutable('today');

        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
        );

        $invoiceOpts->date = $today->format('Y-m-d');
        $invoiceOpts->currencyCode = 'EUR';
        $invoiceOpts->title = 'Integrationstest-Rechnung';
        $invoiceOpts->label = 'Leistungen Integrationstest';
        $invoiceOpts->note = 'Erstellt durch automatisierten Integrationstest.';

        // 3) Eine simple Position hinzufügen
        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: 40.0,
        );
        $item->title = 'Testposition';
        $item->description = 'Automatisch generierte Testposition';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $invoiceOpts->addItem($item);

        // 4) Rechnung in der Sandbox erstellen (Draft)
        $invoice = $billomat->invoices->create($invoiceOpts);

        self::assertInstanceOf(Invoice::class, $invoice);
        self::assertNotNull($invoice->id);
        self::assertGreaterThan(0, $invoice->id);
        self::assertSame($clientId, $invoice->clientId);

        // Status sollte gesetzt sein (bei Erstellung typischerweise "DRAFT")
        self::assertNotNull($invoice->status);
        self::assertInstanceOf(InvoiceStatus::class, $invoice->status);

        // Rechnungsdatum prüfen, wenn vom Server zurückgegeben
        if ($invoice->date !== null) {
            self::assertSame($invoiceOpts->date, $invoice->date);
        }

        // Währung prüfen, wenn zurückgegeben
        if ($invoice->currencyCode !== null) {
            self::assertSame('EUR', $invoice->currencyCode);
        }

        // In DRAFT ist invoiceNumber meist null/leer – keine harte Assertion
        if ($invoice->invoiceNumber !== null) {
            self::assertIsString($invoice->invoiceNumber);
        }
    }

    #[Group("integration")]
    public function test_can_complete_invoice_in_sandbox(): void
    {
        $billomatId = getenv('BILLOMAT_ID');
        $apiKey = getenv('BILLOMAT_API_KEY');

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('Environment variables BILLOMAT_ID or BILLOMAT_API_KEY missing.');
        }

        $billomat = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        $faker = FakerFactory::create('de_DE');

        // 1) Einen Client besorgen (oder anlegen)
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ($clients === []) {
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
            );
            $clientOptions->email = $faker->unique()->safeEmail();
            $clientOptions->countryCode = 'DE';

            $createdClient = $billomat->clients->create($clientOptions);
            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId, 'Client ID must not be null for invoice completion');

        // 2) Draft-Rechnung erstellen
        $today = new \DateTimeImmutable('today');

        $invoiceOpts = new InvoiceCreateOptions(clientId: $clientId);
        $invoiceOpts->date = $today->format('Y-m-d');
        $invoiceOpts->currencyCode = 'EUR';
        $invoiceOpts->title = 'Completion-Test ' . date('d.m.Y H:i:s');
        $invoiceOpts->label = 'Integrationstest Invoice Complete';
        $invoiceOpts->note = 'Erstellt durch automatisierten Integrationstest für complete().';

        $unitPrice = $faker->randomFloat(2, 20, 100);

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $unitPrice,
        );
        $item->title = 'Testposition Completion';
        $item->description = 'Position für Completion-Flow';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $invoiceOpts->addItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertInstanceOf(Invoice::class, $draft);
        self::assertNotNull($draft->id);
        self::assertGreaterThan(0, $draft->id);

        $draftId = $draft->id;

        // 3) Rechnung abschließen (ohne template_id, damit Defaults greifen)
        $result = $billomat->invoices->complete($draftId);

        self::assertTrue($result);

        // 4) Invoice neu laden und prüfen
        $completed = $billomat->invoices->get($draftId);

        self::assertInstanceOf(Invoice::class, $completed);
        self::assertSame($draftId, $completed->id);
        self::assertSame($clientId, $completed->clientId);

        self::assertNotNull($completed->status);
        self::assertInstanceOf(InvoiceStatus::class, $completed->status);
        self::assertNotSame(InvoiceStatus::DRAFT, $completed->status, 'Invoice status should not remain DRAFT after complete().');

        if ($completed->invoiceNumber !== null) {
            self::assertNotSame('', trim($completed->invoiceNumber));
        }
    }
}