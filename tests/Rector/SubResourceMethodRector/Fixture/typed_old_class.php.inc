<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\Api\InvoicePaymentsApi;
use Justpilot\Billomat\Api\InvoiceTagsApi;
use Justpilot\Billomat\Api\ReminderItemsApi;

final class TypedOldClass
{
    public function __construct(private InvoiceTagsApi $tags)
    {
    }

    public function run(InvoicePaymentsApi $payments, ReminderItemsApi $items): void
    {
        $this->tags->listByInvoice(invoiceId: 1);
        $payments->list(['invoice_id' => 2]);
        $items->listByReminder(3);
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\Api\InvoicePaymentsApi;
use Justpilot\Billomat\Api\InvoiceTagsApi;
use Justpilot\Billomat\Api\ReminderItemsApi;

final class TypedOldClass
{
    public function __construct(private InvoiceTagsApi $tags)
    {
    }

    public function run(InvoicePaymentsApi $payments, ReminderItemsApi $items): void
    {
        $this->tags->list(parentId: 1);
        $payments->list(filters: ['invoice_id' => 2]);
        $items->list(3);
    }
}
?>
