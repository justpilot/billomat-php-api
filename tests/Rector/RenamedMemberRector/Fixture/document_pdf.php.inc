<?php

namespace Justpilot\Billomat\Tests\Rector\RenamedMemberRector\Fixture;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\InvoicePdf;
use Justpilot\Billomat\Model\ReminderPdf;

final class DocumentPdfUsage
{
    public function run(BillomatClient $billomat, InvoicePdf $old): array
    {
        $pdf = $billomat->offers->pdf(1);
        $built = new ReminderPdf(id: 1, reminderId: 2, created: null, filename: 'x.pdf', mimeType: 'application/pdf', fileSize: 3, base64file: 'QUJD');

        return [$pdf->offerId, $pdf->base64file, $old->invoiceId, $old->filename, $built];
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\RenamedMemberRector\Fixture;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\InvoicePdf;
use Justpilot\Billomat\Model\ReminderPdf;

final class DocumentPdfUsage
{
    public function run(BillomatClient $billomat, InvoicePdf $old): array
    {
        $pdf = $billomat->offers->pdf(1);
        $built = new ReminderPdf(id: 1, documentId: 2, created: null, filename: 'x.pdf', mimeType: 'application/pdf', fileSize: 3, base64File: 'QUJD');

        return [$pdf->documentId, $pdf->base64File, $old->documentId, $old->filename, $built];
    }
}
?>
