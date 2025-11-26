<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Invoices;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

final class InvoicesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureInvoiceId(): int
    {
        $billomat = $this->createBillomatClientOrSkip();

        // Versuche, eine existierende Rechnung zu holen
        $invoices = $billomat->invoices->list(['per_page' => 1]);

        if ($invoices !== []) {
            return $invoices[0]->id;
        }

        // Falls keine Rechnung existiert → Client & Rechnung anlegen und abschließen
        $faker = $this->faker();

        // 1) Client anlegen
        $clientOptions = new ClientCreateOptions(
            name: $faker->company(),
        );
        $clientOptions->email = $faker->unique()->safeEmail();
        $clientOptions->countryCode = 'DE';

        $client = $billomat->clients->create($clientOptions);
        $clientId = $client->id;

        // 2) Draft-Rechnung anlegen
        $invoiceOpts = new InvoiceCreateOptions(clientId: $clientId);
        $invoiceOpts->currencyCode = 'EUR';
        $invoiceOpts->title = 'PDF-Test ' . date('d.m.Y H:i:s');
        $invoiceOpts->label = 'Integrationstest Invoice PDF';
        $invoiceOpts->note = 'Erstellt durch automatisierten Integrationstest für PDF.';

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
        );
        $item->title = 'PDF Testposition';
        $item->description = 'Position für PDF-Test';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $invoiceOpts->addItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        if (!$draft instanceof Invoice || $draft->id === null) {
            throw new \RuntimeException('Failed to create draft invoice for PDF integration test.');
        }

        $draftId = $draft->id;

        // 3) Abschließen – hier wird in der Regel das PDF erzeugt
        $completed = $billomat->invoices->complete($draftId);
        if (!$completed) {
            throw new \RuntimeException('Failed to complete invoice for PDF integration test.');
        }

        return $draftId;
    }

    #[Group("integration")]
    public function test_can_list_invoices_from_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
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
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

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

        $invoiceOpts->date = $today;
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
            self::assertSame($invoiceOpts->date?->format('Y-m-d'), $invoice->date?->format('Y-m-d'));
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
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

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
        $invoiceOpts->date = $today;
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

    #[Group("integration")]
    public function test_can_delete_draft_invoice_in_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Client besorgen/anlegen (wie im Complete-Test)
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

        self::assertNotNull($clientId);

        // 2) Draft-Rechnung erstellen
        $invoiceOpts = new InvoiceCreateOptions(clientId: $clientId);
        $invoiceOpts->currencyCode = 'EUR';
        $invoiceOpts->title = 'Delete-Test ' . date('d.m.Y H:i:s');

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
        );
        $item->title = 'Testposition Delete';
        $item->description = 'Position für Delete-Flow';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $invoiceOpts->addItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertInstanceOf(Invoice::class, $draft);
        self::assertNotNull($draft->id);
        self::assertSame(InvoiceStatus::DRAFT, $draft->status);

        $draftId = $draft->id;

        // 3) Löschen
        $result = $billomat->invoices->delete($draftId);

        self::assertTrue($result);

        // 4) Nachprüfen, dass sie weg ist
        /** @var Invoice|null $deleted */
        $deleted = $billomat->invoices->get($draftId);

        self::assertNull($deleted);
    }

    #[Group("integration")]
    public function test_can_cancel_invoice_in_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Client besorgen oder anlegen
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

        self::assertNotNull($clientId);

        // 2) Draft-Rechnung erstellen
        $invoiceOpts = new InvoiceCreateOptions(clientId: $clientId);
        $invoiceOpts->currencyCode = 'EUR';
        $invoiceOpts->title = 'Cancel-Test ' . date('d.m.Y H:i:s');
        $invoiceOpts->label = 'Integrationstest Invoice Cancel';

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
        );
        $item->title = 'Testposition Cancel';
        $item->description = 'Position für Cancel-Flow';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $invoiceOpts->addItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertInstanceOf(Invoice::class, $draft);
        self::assertNotNull($draft->id);

        $draftId = $draft->id;

        // 3) Rechnung zuerst abschließen (cancel geht i.d.R. nur auf nicht-DRAFT)
        $completedResult = $billomat->invoices->complete($draftId);
        self::assertTrue($completedResult);

        $completed = $billomat->invoices->get($draftId);
        self::assertInstanceOf(Invoice::class, $completed);

        // 4) Stornieren
        $cancelResult = $billomat->invoices->cancel($draftId);
        self::assertTrue($cancelResult);

        $cancelled = $billomat->invoices->get($draftId);
        self::assertInstanceOf(Invoice::class, $cancelled);

        // je nach Billomat: "CANCELED"
        $this->assertContains(
            $cancelled->status,
            [InvoiceStatus::CANCELED],
            'Invoice status after cancel() should be CANCELED.'
        );
    }

    #[Group("integration")]
    public function test_can_uncancel_invoice_in_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Client besorgen oder anlegen
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

        self::assertNotNull($clientId);

        // 2) Draft-Rechnung erstellen
        $invoiceOpts = new InvoiceCreateOptions(clientId: $clientId);
        $invoiceOpts->currencyCode = 'EUR';
        $invoiceOpts->title = 'Uncancel-Test ' . date('d.m.Y H:i:s');
        $invoiceOpts->label = 'Integrationstest Invoice Uncancel';

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
        );
        $item->title = 'Testposition Uncancel';
        $item->description = 'Position für Uncancel-Flow';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $invoiceOpts->addItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertInstanceOf(Invoice::class, $draft);
        self::assertNotNull($draft->id);

        $draftId = $draft->id;

        // 3) Abschließen
        $completedResult = $billomat->invoices->complete($draftId);
        self::assertTrue($completedResult);

        // 4) Stornieren
        $cancelResult = $billomat->invoices->cancel($draftId);
        self::assertTrue($cancelResult);

        $cancelled = $billomat->invoices->get($draftId);
        self::assertInstanceOf(Invoice::class, $cancelled);
        $this->assertContains(
            $cancelled->status,
            [InvoiceStatus::CANCELED],
            'Invoice status after cancel() should be CANCELED.'
        );

        // 5) Stornierung zurücknehmen
        $uncancelResult = $billomat->invoices->uncancel($draftId);
        self::assertTrue($uncancelResult);

        $uncancelled = $billomat->invoices->get($draftId);
        self::assertInstanceOf(Invoice::class, $uncancelled);

        // Status sollte nicht mehr CANCELED sein
        $this->assertNotContains(
            $uncancelled->status,
            [InvoiceStatus::CANCELED],
            'Invoice status after uncancel() should no longer be CANCELED.'
        );
    }

    #[Group("integration")]
    public function test_can_fetch_invoice_pdf_from_sandbox_as_raw_binary(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $invoiceId = $this->ensureInvoiceId();

        $binary = $billomat->invoices->pdf(
            id: $invoiceId,
            type: null,
            rawPdf: true,
        );

        self::assertIsString($binary);
        self::assertNotSame('', $binary);
        self::assertStringContainsString('PDF', $binary);
    }
}