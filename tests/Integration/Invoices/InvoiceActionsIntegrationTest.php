<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Invoices;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceEmailOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceMailOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integrationstests für die in den Konformitäts-Fixes ergänzten Invoice-Aktionen:
 *  - POST /invoices/{id}/email
 *  - POST /invoices/{id}/mail              (Pixelletter, KOSTENPFLICHTIG)
 *  - PUT  /invoices/{id}/upload-signature
 *  - PUT  /invoices/{id}/encash            (Inkasso, reale Nebenwirkungen)
 *
 * Alle vier Tests sind standardmäßig deaktiviert, weil sie entweder
 *   (a) real Geld kosten (mail, evtl. email),
 *   (b) reale Nebenwirkungen haben (encash sendet Forderung an Inkasso-Dienst),
 * oder
 *   (c) Datei-Uploads in den Account einspielen (upload-signature).
 *
 * Jeweils per einzelner Umgebungsvariable freischaltbar:
 *   BILLOMAT_TEST_EMAIL=1                  (zusätzlich: BILLOMAT_TEST_EMAIL_RECIPIENT)
 *   BILLOMAT_TEST_UPLOAD_SIGNATURE=1
 *   BILLOMAT_TEST_MAIL=1                   ⚠ Pixelletter-Versand wird ausgelöst
 *   BILLOMAT_TEST_ENCASH=1                 ⚠ Forderung wird ans Inkasso übergeben
 */
#[CoversNothing]
final class InvoiceActionsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    /**
     * Legt einen frischen Sandbox-Kunden + Draft-Rechnung an, schließt die Rechnung ab
     * und gibt die `id` zurück. Bricht den Test ab, wenn etwas schiefläuft.
     */
    private function createCompletedInvoice(BillomatClient $billomat): int
    {
        $faker = $this->faker();

        $clientOpts = new ClientCreateOptions();
        $clientOpts->name = 'IntegrationActions '.$faker->unique()->bothify('##??##');
        $clientOpts->email = $faker->unique()->safeEmail();
        $clientOpts->countryCode = 'DE';

        $client = $billomat->clients->create($clientOpts);
        self::assertNotNull($client->id);

        $invoiceOpts = new InvoiceCreateOptions(clientId: $client->id);
        $invoiceOpts->currencyCode = 'EUR';
        $invoiceOpts->title = 'Action-Integration '.date('d.m.Y H:i:s');
        $invoiceOpts->label = 'Integrationstest Invoice Action';

        $item = new InvoiceItemCreateOptions(
            quantity: 1.0,
            unitPrice: $faker->randomFloat(2, 20, 100),
        );
        $item->title = 'Testposition Action';
        $item->unit = 'Stück';
        $item->taxRate = 19.0;

        $invoiceOpts->addItem($item);

        $draft = $billomat->invoices->create($invoiceOpts);
        self::assertNotNull($draft->id);

        // Aktionen wie email/mail/encash benötigen i. d. R. eine abgeschlossene Rechnung.
        self::assertTrue(
            $billomat->invoices->complete($draft->id),
            'Failed to complete invoice for action integration test.'
        );

        return $draft->id;
    }

    #[Group('integration')]
    #[Test]
    public function canEmailInvoiceInSandboxWhenExplicitlyEnabled(): void
    {
        // Doppelte Sicherheitsbremse: nur wenn EXPLIZIT aktiviert UND ein
        // Empfänger gesetzt ist (der i. d. R. der/die User:in selbst ist).
        $enabled = getenv('BILLOMAT_TEST_EMAIL');
        if ('1' !== $enabled) {
            $this->markTestSkipped(
                'Set BILLOMAT_TEST_EMAIL=1 to enable POST /invoices/{id}/email integration test. '
                .'WARNUNG: Billomat versendet daraufhin eine echte E-Mail aus dem Sandbox-Account.'
            );
        }

        $recipient = getenv('BILLOMAT_TEST_EMAIL_RECIPIENT') ?: null;
        if (!$recipient) {
            $this->markTestSkipped(
                'Set BILLOMAT_TEST_EMAIL_RECIPIENT to a real address (idealerweise deine eigene), '
                .'an die der Sandbox-Test die Test-Rechnung schicken darf.'
            );
        }

        $billomat = $this->createBillomatClientOrSkip();
        $invoiceId = $this->createCompletedInvoice($billomat);

        $opts = new InvoiceEmailOptions();
        $opts->to = [$recipient];
        $opts->subject = 'SDK-Integrationstest – Bitte ignorieren';
        $opts->body = "Automatisierter Integrationstest des justpilot/billomat-php-api SDK.\n\n"
            .'Diese Mail wurde aus der Sandbox geschickt. Du kannst sie löschen.';

        $result = $billomat->invoices->email($invoiceId, $opts);
        self::assertTrue($result);
    }

    #[Group('integration')]
    #[Test]
    public function canUploadSignatureInSandboxWhenExplicitlyEnabled(): void
    {
        if ('1' !== getenv('BILLOMAT_TEST_UPLOAD_SIGNATURE')) {
            $this->markTestSkipped(
                'Set BILLOMAT_TEST_UPLOAD_SIGNATURE=1 to enable PUT /invoices/{id}/upload-signature '
                .'integration test. (Der Test lädt ein Dummy-PDF in die Rechnung und mutiert deren Zustand.)'
            );
        }

        $billomat = $this->createBillomatClientOrSkip();
        $invoiceId = $this->createCompletedInvoice($billomat);

        // Minimal-PDF (1.4 header + EOF) – akzeptiert von Billomat als gültige PDF-Datei.
        $minimalPdf = "%PDF-1.4\n"
            ."1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
            ."2 0 obj<</Type/Pages/Count 0>>endobj\n"
            ."xref\n0 3\n"
            ."0000000000 65535 f\n"
            ."0000000009 00000 n\n"
            ."0000000052 00000 n\n"
            ."trailer<</Size 3/Root 1 0 R>>\n"
            ."startxref\n92\n%%EOF";

        $base64 = base64_encode($minimalPdf);

        $result = $billomat->invoices->uploadSignature($invoiceId, $base64);
        self::assertTrue($result);

        // Sanity-Check: Rechnung existiert weiterhin und ist nicht zerstört.
        $afterUpload = $billomat->invoices->get($invoiceId);
        self::assertInstanceOf(Invoice::class, $afterUpload);
        self::assertSame($invoiceId, $afterUpload->id);
    }

    #[Group('integration')]
    #[Test]
    public function canSendInvoiceViaPixelletterWhenExplicitlyEnabled(): void
    {
        if ('1' !== getenv('BILLOMAT_TEST_MAIL')) {
            $this->markTestSkipped(
                'Set BILLOMAT_TEST_MAIL=1 to enable POST /invoices/{id}/mail integration test. '
                .'⚠ Pixelletter-Versand ist KOSTENPFLICHTIG (echte Briefzustellung). '
                .'Nur aktivieren, wenn du das tatsächlich willst.'
            );
        }

        $billomat = $this->createBillomatClientOrSkip();
        $invoiceId = $this->createCompletedInvoice($billomat);

        $opts = new InvoiceMailOptions();
        $opts->color = false;
        $opts->duplex = true;
        $opts->paperWeight = '80';
        $opts->recipientAddress = "SDK-Integrationstest\nPostfach 4711\n12345 Testdorf";

        $result = $billomat->invoices->mail($invoiceId, $opts);
        self::assertTrue($result);
    }

    #[Group('integration')]
    #[Test]
    public function canSendInvoiceToEncashWhenExplicitlyEnabled(): void
    {
        if ('1' !== getenv('BILLOMAT_TEST_ENCASH')) {
            $this->markTestSkipped(
                'Set BILLOMAT_TEST_ENCASH=1 to enable PUT /invoices/{id}/encash integration test. '
                .'⚠ Diese Aktion übergibt die Rechnung an Billomats Inkasso-Partner — '
                .'auch in der Sandbox können dabei reale Vorgänge angestoßen werden. '
                .'Nur aktivieren, wenn du dir sicher bist.'
            );
        }

        $billomat = $this->createBillomatClientOrSkip();
        $invoiceId = $this->createCompletedInvoice($billomat);

        // encash erwartet, dass die Rechnung OPEN/OVERDUE ist — complete() setzt sie
        // in den Standardfall auf OPEN. Status defensiv prüfen.
        $invoice = $billomat->invoices->get($invoiceId);
        self::assertInstanceOf(Invoice::class, $invoice);
        self::assertNotSame(InvoiceStatus::DRAFT, $invoice->status);

        $result = $billomat->invoices->encash($invoiceId);
        self::assertTrue($result);
    }
}
