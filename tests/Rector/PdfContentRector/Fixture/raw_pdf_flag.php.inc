<?php

namespace Justpilot\Billomat\Tests\Rector\PdfContentRector\Fixture;

use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PdfType;

final class RawPdfFlag
{
    public function run(BillomatClient $billomat, InvoicesApi $invoices, RemindersApi $reminders, bool $raw, array $args): void
    {
        $invoices->pdf(1, PdfType::SIGNED, rawPdf: true);
        $invoices->pdf(1, PdfType::SIGNED, true);
        $invoices->pdf(1, null, TRUE);
        $invoices->pdf(id: 1, rawPdf: true, type: PdfType::PRINT);
        $invoices->pdf(1, rawPdf: false);
        $invoices->pdf(1, PdfType::PRINT, false);
        $reminders->pdf(2, rawPdf: true);
        $billomat->offers->pdf(3, rawPdf: true);
        $billomat->confirmations->pdf(3, rawPdf: true);
        $billomat->creditNotes->pdf(3, rawPdf: true);
        $billomat->deliveryNotes->pdf(3, rawPdf: true);
        $billomat->letters->pdf(3, rawPdf: true);
        $invoices->pdf(1, PdfType::SIGNED);
        $invoices->pdf(1, rawPdf: $raw);
        $invoices->pdf(1, null, PHP_VERSION);
        $invoices->pdf(...$args);
        $invoices->pdf(...);
        $invoices->get(1, rawPdf: true);
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\PdfContentRector\Fixture;

use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\PdfType;

final class RawPdfFlag
{
    public function run(BillomatClient $billomat, InvoicesApi $invoices, RemindersApi $reminders, bool $raw, array $args): void
    {
        $invoices->pdfContent(1, PdfType::SIGNED);
        $invoices->pdfContent(1, PdfType::SIGNED);
        $invoices->pdfContent(1, null);
        $invoices->pdfContent(id: 1, type: PdfType::PRINT);
        $invoices->pdf(1);
        $invoices->pdf(1, PdfType::PRINT);
        $reminders->pdfContent(2);
        $billomat->offers->pdfContent(3);
        $billomat->confirmations->pdfContent(3);
        $billomat->creditNotes->pdfContent(3);
        $billomat->deliveryNotes->pdfContent(3);
        $billomat->letters->pdfContent(3);
        $invoices->pdf(1, PdfType::SIGNED);
        $invoices->pdf(1, rawPdf: $raw);
        $invoices->pdf(1, null, PHP_VERSION);
        $invoices->pdf(...$args);
        $invoices->pdf(...);
        $invoices->get(1, rawPdf: true);
    }
}
?>
