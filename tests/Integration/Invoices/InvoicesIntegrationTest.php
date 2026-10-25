<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Invoices;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceUpdateOptions;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;
use RuntimeException;

#[CoversNothing]
final class InvoicesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureInvoiceId(): int
    {
        $billomat = $this->createBillomatClientOrSkip();

        // Versuche, eine existierende Rechnung zu holen
        $invoices = $billomat->invoices->list(['per_page' => 1]);

        if ([] !== $invoices) {
            return $invoices[0]->id;
        }

        // Falls keine Rechnung existiert → Client & Rechnung anlegen und abschließen
        $faker = $this->faker();

        // 1) Client anlegen
        $clientOptions = new ClientCreateOptions(
            name: $faker->company(),
            countryCode: 'DE',
            email: $faker->unique()->safeEmail(),
        );

        $client = $billomat->clients->create($clientOptions);
        $clientId = $client->id;

        // 2) Draft-Rechnung anlegen
        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
            title: 'PDF-Test '.date('d.m.Y H:i:s'),
            label: 'Integrationstest Invoice PDF',
            note: 'Erstellt durch automatisierten Integrationstest für PDF.',
            currencyCode: 'EUR',
        );

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
            unit: 'Stück',
            taxRate: 19.0,
            title: 'PDF Testposition',
            description: 'Position für PDF-Test',
        );

        $invoiceOpts = $invoiceOpts->withItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        if (null === $draft->id) {
            throw new RuntimeException('Failed to create draft invoice for PDF integration test.');
        }

        $draftId = $draft->id;

        // 3) Abschließen – hier wird in der Regel das PDF erzeugt
        $billomat->invoices->complete($draftId);

        return $draftId;
    }

    #[Group('integration')]
    #[Test]
    public function canListInvoicesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $invoices = $billomat->invoices->list(['per_page' => 5]);

        self::assertIsArray($invoices);
        self::assertContainsOnlyInstancesOf(Invoice::class, $invoices);

        if ([] !== $invoices) {
            $first = $invoices[0];
            self::assertNotNull($first->id);
            self::assertIsInt($first->clientId);
            self::assertInstanceOf(InvoiceStatus::class, $first->status);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canListInvoicesFromSandboxOrderBy(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $invoices = $billomat->invoices->list(['per_page' => 5, 'order_by' => 'date+DESC']);

        self::assertIsArray($invoices);
        self::assertContainsOnlyInstancesOf(Invoice::class, $invoices);

        if ([] !== $invoices) {
            $first = $invoices[0];
            self::assertNotNull($first->id);
            self::assertIsInt($first->clientId);
            self::assertInstanceOf(InvoiceStatus::class, $first->status);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canListInvoicesWithTypedFilter(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $statuses = [InvoiceStatus::DRAFT, InvoiceStatus::OPEN, InvoiceStatus::PAID];
        $from = new DateTimeImmutable('2000-01-01');

        // Mehrfach-Status und geschachtelte Sortierung gehen kommasepariert an Billomat.
        $invoices = $billomat->invoices->list(new InvoiceFilter(
            status: $statuses,
            from: $from,
            orderBy: OrderBy::desc('date')->thenAsc('id'),
            perPage: 20,
        ));

        self::assertContainsOnlyInstancesOf(Invoice::class, $invoices);
        $previous = null;
        foreach ($invoices as $invoice) {
            self::assertContains($invoice->status, $statuses);
            self::assertNotNull($invoice->date);
            self::assertGreaterThanOrEqual($from, $invoice->date);
            if ($previous instanceof DateTimeImmutable) {
                self::assertLessThanOrEqual($previous, $invoice->date, 'Rechnungen sind nicht absteigend nach Datum sortiert.');
            }
            $previous = $invoice->date;
        }

        // Ein Status, den keine der gelisteten Rechnungen hat, liefert eine disjunkte Menge.
        $canceled = $billomat->invoices->list(new InvoiceFilter(status: [InvoiceStatus::CANCELED], perPage: 20));
        foreach ($canceled as $invoice) {
            self::assertSame(InvoiceStatus::CANCELED, $invoice->status);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canCreateInvoiceDraftInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Einen Client für die Rechnung bestimmen
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ([] === $clients) {
            // Fallback: Minimalen Client erstellen, falls noch keiner existiert
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
                countryCode: 'DE',
                email: $faker->unique()->safeEmail(),
            );

            $createdClient = $billomat->clients->create($clientOptions);

            self::assertNotNull($createdClient->id);

            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId, 'Client ID must not be null for invoice creation');

        // 2) InvoiceCreateOptions vorbereiten
        $today = new DateTimeImmutable('today');

        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
            date: $today,
            title: 'Integrationstest-Rechnung',
            label: 'Leistungen Integrationstest',
            note: 'Erstellt durch automatisierten Integrationstest.',
            currencyCode: 'EUR',
        );

        // 3) Eine simple Position hinzufügen
        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: 40.0,
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Testposition',
            description: 'Automatisch generierte Testposition',
        );

        $invoiceOpts = $invoiceOpts->withItem($item);

        // 4) Rechnung in der Sandbox erstellen (Draft)
        $invoice = $billomat->invoices->create($invoiceOpts);

        self::assertNotNull($invoice->id);
        self::assertGreaterThan(0, $invoice->id);
        self::assertSame($clientId, $invoice->clientId);

        // Status sollte gesetzt sein (bei Erstellung typischerweise "DRAFT")
        self::assertNotNull($invoice->status);

        // Rechnungsdatum prüfen, wenn vom Server zurückgegeben
        if ($invoice->date instanceof DateTimeImmutable) {
            self::assertSame($today->format('Y-m-d'), $invoice->date->format('Y-m-d'));
        }

        // Währung prüfen, wenn zurückgegeben
        if (null !== $invoice->currencyCode) {
            self::assertSame('EUR', $invoice->currencyCode);
        }

        // In DRAFT ist invoiceNumber meist null/leer – keine harte Assertion
        if (null !== $invoice->invoiceNumber) {
            self::assertIsString($invoice->invoiceNumber);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canCompleteInvoiceInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Einen Client besorgen (oder anlegen)
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ([] === $clients) {
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
                countryCode: 'DE',
                email: $faker->unique()->safeEmail(),
            );

            $createdClient = $billomat->clients->create($clientOptions);
            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId, 'Client ID must not be null for invoice completion');

        // 2) Draft-Rechnung erstellen
        $today = new DateTimeImmutable('today');

        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
            date: $today,
            title: 'Completion-Test '.date('d.m.Y H:i:s'),
            label: 'Integrationstest Invoice Complete',
            note: 'Erstellt durch automatisierten Integrationstest für complete().',
            currencyCode: 'EUR',
        );

        $unitPrice = $faker->randomFloat(2, 20, 100);

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $unitPrice,
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Testposition Completion',
            description: 'Position für Completion-Flow',
        );

        $invoiceOpts = $invoiceOpts->withItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertNotNull($draft->id);
        self::assertGreaterThan(0, $draft->id);

        $draftId = $draft->id;

        // 3) Rechnung abschließen (ohne template_id, damit Defaults greifen)
        $billomat->invoices->complete($draftId);

        // 4) Invoice neu laden und prüfen
        $completed = $billomat->invoices->get($draftId);

        self::assertInstanceOf(Invoice::class, $completed);
        self::assertSame($draftId, $completed->id);
        self::assertSame($clientId, $completed->clientId);

        self::assertNotNull($completed->status);
        self::assertNotSame(InvoiceStatus::DRAFT, $completed->status, 'Invoice status should not remain DRAFT after complete().');

        if (null !== $completed->invoiceNumber) {
            self::assertNotSame('', trim($completed->invoiceNumber));
        }
    }

    #[Group('integration')]
    #[Test]
    public function canDeleteDraftInvoiceInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Client besorgen/anlegen (wie im Complete-Test)
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ([] === $clients) {
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
                countryCode: 'DE',
                email: $faker->unique()->safeEmail(),
            );

            $createdClient = $billomat->clients->create($clientOptions);
            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId);

        // 2) Draft-Rechnung erstellen
        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
            title: 'Delete-Test '.date('d.m.Y H:i:s'),
            currencyCode: 'EUR',
        );

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Testposition Delete',
            description: 'Position für Delete-Flow',
        );

        $invoiceOpts = $invoiceOpts->withItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertNotNull($draft->id);
        self::assertSame(InvoiceStatus::DRAFT, $draft->status);

        $draftId = $draft->id;

        // 3) Löschen
        $billomat->invoices->delete($draftId);

        // 4) Nachprüfen, dass sie weg ist
        $deleted = $billomat->invoices->get($draftId);

        self::assertNull($deleted);
    }

    #[Group('integration')]
    #[Test]
    public function canCancelInvoiceInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Client besorgen oder anlegen
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ([] === $clients) {
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
                countryCode: 'DE',
                email: $faker->unique()->safeEmail(),
            );

            $createdClient = $billomat->clients->create($clientOptions);
            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId);

        // 2) Draft-Rechnung erstellen
        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
            title: 'Cancel-Test '.date('d.m.Y H:i:s'),
            label: 'Integrationstest Invoice Cancel',
            currencyCode: 'EUR',
        );

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Testposition Cancel',
            description: 'Position für Cancel-Flow',
        );

        $invoiceOpts = $invoiceOpts->withItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertNotNull($draft->id);

        $draftId = $draft->id;

        // 3) Rechnung zuerst abschließen (cancel geht i.d.R. nur auf nicht-DRAFT)
        $billomat->invoices->complete($draftId);

        $completed = $billomat->invoices->get($draftId);
        self::assertInstanceOf(Invoice::class, $completed);

        // 4) Stornieren
        $billomat->invoices->cancel($draftId);

        $cancelled = $billomat->invoices->get($draftId);
        self::assertInstanceOf(Invoice::class, $cancelled);

        // je nach Billomat: "CANCELED"
        $this->assertContains(
            $cancelled->status,
            [InvoiceStatus::CANCELED],
            'Invoice status after cancel() should be CANCELED.'
        );
    }

    #[Group('integration')]
    #[Test]
    public function canUncancelInvoiceInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Client besorgen oder anlegen
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ([] === $clients) {
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
                countryCode: 'DE',
                email: $faker->unique()->safeEmail(),
            );

            $createdClient = $billomat->clients->create($clientOptions);
            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        self::assertNotNull($clientId);

        // 2) Draft-Rechnung erstellen
        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
            title: 'Uncancel-Test '.date('d.m.Y H:i:s'),
            label: 'Integrationstest Invoice Uncancel',
            currencyCode: 'EUR',
        );

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
            unit: 'Stück',
            taxRate: 19.0,
            title: 'Testposition Uncancel',
            description: 'Position für Uncancel-Flow',
        );

        $invoiceOpts = $invoiceOpts->withItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);

        self::assertNotNull($draft->id);

        $draftId = $draft->id;

        // 3) Abschließen
        $billomat->invoices->complete($draftId);

        // 4) Stornieren
        $billomat->invoices->cancel($draftId);

        $cancelled = $billomat->invoices->get($draftId);
        self::assertInstanceOf(Invoice::class, $cancelled);
        $this->assertContains(
            $cancelled->status,
            [InvoiceStatus::CANCELED],
            'Invoice status after cancel() should be CANCELED.'
        );

        // 5) Stornierung zurücknehmen
        $billomat->invoices->uncancel($draftId);

        $uncancelled = $billomat->invoices->get($draftId);
        self::assertInstanceOf(Invoice::class, $uncancelled);

        // Status sollte nicht mehr CANCELED sein
        $this->assertNotContains(
            $uncancelled->status,
            [InvoiceStatus::CANCELED],
            'Invoice status after uncancel() should no longer be CANCELED.'
        );
    }

    #[Group('integration')]
    #[Test]
    public function canFetchInvoicePdfFromSandboxAsRawBinary(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $invoiceId = $this->ensureInvoiceId();

        $binary = $billomat->invoices->pdf(
            id: $invoiceId,
            rawPdf: true,
        );

        self::assertIsString($binary);
        self::assertNotSame('', $binary);
        self::assertStringContainsString('PDF', $binary);
    }

    #[Group('integration')]
    #[Test]
    public function canUpdateDraftInvoiceInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // Minimal: wir erzeugen Draft wie in create-test
        $clients = $billomat->clients->list(['per_page' => 1]);
        if ([] === $clients) {
            $clientOptions = new ClientCreateOptions(
                name: $faker->company(),
                countryCode: 'DE',
                email: $faker->unique()->safeEmail(),
            );
            $createdClient = $billomat->clients->create($clientOptions);
            $clientId = $createdClient->id;
        } else {
            $clientId = $clients[0]->id;
        }

        $invoiceOpts = new InvoiceCreateOptions(
            clientId: $clientId,
            title: 'Update-Draft-Test '.date('d.m.Y H:i:s'),
            currencyCode: 'EUR',
        );

        $item = new InvoiceItemCreateOptions(
            1.0,
            $faker->randomFloat(2, 10, 50),
            title: 'Update Draft Position',
        );
        $invoiceOpts = $invoiceOpts->withItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);
        self::assertNotNull($draft->id);

        // Draft bearbeiten: date ändern
        $newDate = new DateTimeImmutable('today');

        $update = new InvoiceUpdateOptions(date: $newDate);

        $updated = $billomat->invoices->update($draft->id, $update);

        self::assertSame($draft->id, $updated->id);
        self::assertSame($newDate->format('Y-m-d'), $updated->date?->format('Y-m-d'));

        // optional: nochmal neu holen
        $fetched = $billomat->invoices->get($draft->id);
        self::assertSame($newDate->format('Y-m-d'), $fetched->date?->format('Y-m-d'));
    }
}
