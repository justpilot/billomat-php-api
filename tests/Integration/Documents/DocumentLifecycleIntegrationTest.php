<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Documents;

use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\ConfirmationUpdateOptions;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\CreditNoteUpdateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\DeliveryNoteUpdateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\LetterCreateOptions;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\ReminderCreateOptions;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Aktionen auf abgeschlossenen Dokumenten aller sieben Typen gegen die Sandbox:
 * complete(), pdf()/pdfContent(), cancel()/uncancel(), uploadSignature() und email().
 *
 * Abgeschlossene Dokumente lassen sich nicht löschen; jeder Lauf hinterlässt sie in der Sandbox.
 * email() verschickt echte Mails und läuft nur mit BILLOMAT_TEST_EMAIL=1 und BILLOMAT_TEST_EMAIL_RECIPIENT.
 */
#[CoversNothing]
final class DocumentLifecycleIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    /** Gültiges Minimal-PDF; Billomat nimmt es als signierte Fassung an. */
    private const string MINIMAL_PDF = "%PDF-1.4\n"
        ."1 0 obj<</Type/Catalog/Pages 2 0 R>>endobj\n"
        ."2 0 obj<</Type/Pages/Count 0>>endobj\n"
        ."xref\n0 3\n"
        ."0000000000 65535 f\n"
        ."0000000009 00000 n\n"
        ."0000000052 00000 n\n"
        ."trailer<</Size 3/Root 1 0 R>>\n"
        ."startxref\n92\n%%EOF";

    /**
     * @return iterable<string, array{string}>
     */
    public static function documentTypes(): iterable
    {
        foreach (['invoices', 'offers', 'confirmations', 'creditNotes', 'deliveryNotes', 'letters', 'reminders'] as $type) {
            yield $type => [$type];
        }
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function cancellableTypes(): iterable
    {
        foreach (['invoices', 'offers', 'confirmations', 'creditNotes', 'reminders'] as $type) {
            yield $type => [$type];
        }
    }

    /**
     * Nur diese drei Typen kennen PUT /{type}/{id}/upload-signature.
     *
     * @return iterable<string, array{string}>
     */
    public static function signableTypes(): iterable
    {
        foreach (['invoices', 'creditNotes', 'reminders'] as $type) {
            yield $type => [$type];
        }
    }

    #[Group('integration')]
    #[Test]
    #[DataProvider('documentTypes')]
    public function completeCreatesThePdf(string $type): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        [$api, $id] = $this->createDraft($billomat, $type);

        $api->complete($id);
        self::assertNotSame('DRAFT', $api->getOrFail($id)->status?->value);

        $pdf = $api->pdf($id);
        self::assertSame($id, $pdf->documentId);
        self::assertSame([], $pdf->extra);
        self::assertSame('application/pdf', $pdf->mimeType);
        self::assertStringStartsWith('%PDF-', $pdf->getBinary());
        self::assertSame(\strlen($pdf->getBinary()), $pdf->fileSize);
        self::assertStringStartsWith('%PDF-', $api->pdfContent($id));
    }

    #[Group('integration')]
    #[Test]
    #[DataProvider('cancellableTypes')]
    public function cancelAndUncancel(string $type): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        [$api, $id] = $this->createDraft($billomat, $type);
        if (!$api instanceof InvoicesApi && !$api instanceof OffersApi && !$api instanceof ConfirmationsApi
            && !$api instanceof CreditNotesApi && !$api instanceof RemindersApi) {
            self::fail($type.' kennt kein cancel()');
        }
        $api->complete($id);
        $completed = $api->getOrFail($id)->status?->value;

        $api->cancel($id);
        self::assertSame('CANCELED', $api->getOrFail($id)->status?->value);

        $api->uncancel($id);
        self::assertSame($completed, $api->getOrFail($id)->status?->value);
    }

    #[Group('integration')]
    #[Test]
    #[DataProvider('signableTypes')]
    public function uploadSignatureStoresTheSignedPdf(string $type): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        [$api, $id] = $this->createDraft($billomat, $type);
        if (!$api instanceof InvoicesApi && !$api instanceof CreditNotesApi && !$api instanceof RemindersApi) {
            self::fail($type.' kennt kein uploadSignature()');
        }
        $api->complete($id);

        try {
            (void) $api->pdf($id, PdfType::SIGNED);
            self::fail('Vor dem Upload gibt es keine signierte Fassung.');
        } catch (NotFoundException) {
            // Billomat antwortet mit 404 "pdf not found".
        }

        $api->uploadSignature($id, base64_encode(self::MINIMAL_PDF));

        $signed = $api->pdf($id, PdfType::SIGNED);
        self::assertSame(self::MINIMAL_PDF, $signed->getBinary());
        self::assertSame(self::MINIMAL_PDF, $api->pdfContent($id, PdfType::SIGNED));
    }

    #[Group('integration')]
    #[Test]
    #[DataProvider('documentTypes')]
    public function emailSendsTheDocument(string $type): void
    {
        $recipient = getenv('BILLOMAT_TEST_EMAIL_RECIPIENT');
        if ('1' !== getenv('BILLOMAT_TEST_EMAIL') || !\is_string($recipient) || '' === $recipient) {
            self::markTestSkipped('Set BILLOMAT_TEST_EMAIL=1 and BILLOMAT_TEST_EMAIL_RECIPIENT: Billomat sends a real email.');
        }

        $billomat = $this->createBillomatClientOrSkip();
        [$api, $id] = $this->createDraft($billomat, $type);
        $api->complete($id);
        $before = $api->getOrFail($id);

        $api->email($id, new EmailOptions(
            to: [$recipient],
            subject: 'SDK-Integrationstest ('.$type.') – bitte ignorieren',
            body: 'Automatisierter Integrationstest des justpilot/billomat-php-api SDK aus der Sandbox.',
        ));

        // Billomat antwortet ohne Body; das Dokument existiert unverändert weiter.
        self::assertSame($before->id, $api->getOrFail($id)->id);
    }

    /**
     * @return iterable<string, array{string}>
     */
    public static function typesWithoutUpdateTest(): iterable
    {
        foreach (['confirmations', 'creditNotes', 'deliveryNotes'] as $type) {
            yield $type => [$type];
        }
    }

    #[Group('integration')]
    #[Test]
    #[DataProvider('typesWithoutUpdateTest')]
    public function updateChangesTheDraft(string $type): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        [$api, $id] = $this->createDraft($billomat, $type);

        try {
            $title = 'Geändert '.date('His');
            $updated = match (true) {
                $api instanceof ConfirmationsApi => $api->update($id, new ConfirmationUpdateOptions(title: $title)),
                $api instanceof CreditNotesApi => $api->update($id, new CreditNoteUpdateOptions(title: $title)),
                $api instanceof DeliveryNotesApi => $api->update($id, new DeliveryNoteUpdateOptions(title: $title)),
                default => self::fail($type.' ist hier nicht vorgesehen'),
            };

            self::assertSame($title, $updated->title);
            self::assertSame($title, $api->getOrFail($id)->title);
        } finally {
            $api->delete($id);
        }
    }

    /**
     * Legt einen Entwurf des Typs an; Mahnungen bekommen eine frisch abgeschlossene, überfällige Rechnung.
     *
     * @return array{InvoicesApi|OffersApi|ConfirmationsApi|CreditNotesApi|DeliveryNotesApi|LettersApi|RemindersApi, int}
     */
    private function createDraft(BillomatClient $billomat, string $type): array
    {
        $clientId = $this->clientId($billomat);
        $title = 'SDK-Lifecycle '.$type.' '.date('d.m.Y H:i:s');
        $price = Money::of('10.00');

        return match ($type) {
            'invoices' => [$billomat->invoices, $billomat->invoices->create(
                new InvoiceCreateOptions(clientId: $clientId, title: $title)->withItem(new InvoiceItemCreateOptions(1.0, $price, title: 'Testposition')),
            )->id],
            'offers' => [$billomat->offers, $billomat->offers->create(
                new OfferCreateOptions(clientId: $clientId, title: $title)->withItem(new OfferItemCreateOptions(1.0, $price, title: 'Testposition')),
            )->id],
            'confirmations' => [$billomat->confirmations, $billomat->confirmations->create(
                new ConfirmationCreateOptions(clientId: $clientId, title: $title)->withItem(new ConfirmationItemCreateOptions(1.0, $price, title: 'Testposition')),
            )->id],
            'creditNotes' => [$billomat->creditNotes, $billomat->creditNotes->create(
                new CreditNoteCreateOptions(clientId: $clientId, title: $title)->withItem(new CreditNoteItemCreateOptions(1.0, $price, title: 'Testposition')),
            )->id],
            'deliveryNotes' => [$billomat->deliveryNotes, $billomat->deliveryNotes->create(
                new DeliveryNoteCreateOptions(clientId: $clientId, title: $title)->withItem(new DeliveryNoteItemCreateOptions(1.0, $price, title: 'Testposition')),
            )->id],
            'letters' => [$billomat->letters, $billomat->letters->create(
                new LetterCreateOptions(clientId: $clientId, subject: $title, intro: 'Sehr geehrte Damen und Herren,'),
            )->id],
            'reminders' => [$billomat->reminders, $billomat->reminders->create(
                new ReminderCreateOptions(invoiceId: $this->overdueInvoiceId($billomat, $clientId), subject: $title),
            )->id],
            default => self::fail('Unbekannter Dokumenttyp '.$type),
        };
    }

    /**
     * Eine heute abgeschlossene Rechnung ist in der Sandbox sofort überfällig (Zahlungsziel 0 Tage).
     */
    private function overdueInvoiceId(BillomatClient $billomat, int $clientId): int
    {
        $invoice = $billomat->invoices->create(
            new InvoiceCreateOptions(clientId: $clientId, title: 'SDK-Lifecycle Mahnungsgrundlage')
                ->withItem(new InvoiceItemCreateOptions(1.0, Money::of('10.00'), title: 'Testposition')),
        );
        $billomat->invoices->complete($invoice->id);
        if ('OVERDUE' !== $billomat->invoices->getOrFail($invoice->id)->status?->value) {
            self::markTestSkipped('Die Sandbox-Rechnung ist nach complete() nicht überfällig; Mahnungen brauchen OVERDUE.');
        }

        return $invoice->id;
    }

    private function clientId(BillomatClient $billomat): int
    {
        $clients = $billomat->clients->list(['per_page' => 1]);
        if ([] === $clients) {
            self::markTestSkipped('Die Sandbox braucht mindestens einen Kunden.');
        }

        return $clients[0]->id;
    }
}
