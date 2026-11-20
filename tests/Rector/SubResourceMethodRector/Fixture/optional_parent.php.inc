<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class OptionalParent
{
    public function run(BillomatClient $billomat, array $filters): void
    {
        $billomat->invoicePayments->list(['invoice_id' => 1]);
        $billomat->incomingPayments->list($filters);
        $billomat->articlePropertyValues->listPage(['article_id' => 2]);
        $billomat->supplierPropertyValues->iterateAll(['supplier_id' => 3], 50);
        $billomat->clientPropertyValues->iterateAll($filters, pageSize: 25);
        $billomat->creditNotePayments->list(filters: $filters);
        $billomat->invoicePayments->list(...$filters);
        $billomat->incomingPayments->list(...$filters, ...$filters);
        $billomat->invoicePayments->list();
        $billomat->clientPropertyValues->iterateAll(pageSize: 10);
        $billomat->invoicePayments->get(9);
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class OptionalParent
{
    public function run(BillomatClient $billomat, array $filters): void
    {
        $billomat->invoicePayments->list(filters: ['invoice_id' => 1]);
        $billomat->incomingPayments->list(filters: $filters);
        $billomat->articlePropertyValues->listPage(filters: ['article_id' => 2]);
        $billomat->supplierPropertyValues->iterateAll(filters: ['supplier_id' => 3], pageSize: 50);
        $billomat->clientPropertyValues->iterateAll(filters: $filters, pageSize: 25);
        $billomat->creditNotePayments->list(filters: $filters);
        $billomat->invoicePayments->list(...$filters);
        $billomat->incomingPayments->list(...$filters, ...$filters);
        $billomat->invoicePayments->list();
        $billomat->clientPropertyValues->iterateAll(pageSize: 10);
        $billomat->invoicePayments->get(9);
    }
}
?>
