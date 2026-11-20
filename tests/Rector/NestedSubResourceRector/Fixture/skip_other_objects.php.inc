<?php

namespace Justpilot\Billomat\Tests\Rector\NestedSubResourceRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class SkipOtherObjects
{
    public $invoiceComments;

    public function run(BillomatClient $billomat, \stdClass $other, $untyped): void
    {
        $this->invoiceComments->listByInvoice(1);
        $other->invoiceComments->listByInvoice(1);
        $untyped->clientTags->listByClient(1);
        $billomat->invoices->comments->list(1);
        $billomat->{'invoiceComments'};
    }
}
?>
