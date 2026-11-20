<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\Api\CommentsApi;
use Justpilot\Billomat\BillomatClient;

final class SkipUnrelatedCalls
{
    public function run(BillomatClient $billomat, \stdClass $other, CommentsApi $comments, array $filters): void
    {
        $billomat->invoices->list(['status' => 'OPEN']);
        $billomat->clientProperties->list(['per_page' => 10]);
        $billomat->dunningLevels->list(['per_page' => 10]);
        $billomat->invoiceTags->cloud();
        $billomat->incomingComments->listByIncoming(...);
        $other->invoicePayments->list($filters);
        $other->listByInvoice(1);
        $comments->list(1);
        $billomat->{'invoicePayments'}->list($filters);
        $billomat->invoicePayments->{'list'}($filters);
    }
}
?>
