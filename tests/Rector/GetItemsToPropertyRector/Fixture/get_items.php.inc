<?php

namespace Justpilot\Billomat\Tests\Rector\GetItemsToPropertyRector\Fixture;

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\InvoiceUpdateOptions;

final class GetItems
{
    public function run(InvoiceCreateOptions $invoice, RecurringCreateOptions $recurring, InvoiceUpdateOptions $update, \ArrayObject $other): void
    {
        $items = $invoice->getItems();
        $count = \count($recurring->getItems());
        $update->getItems();
        $other->getItems();
        $invoice->getItems(1);
        $invoice->{'getItems'}();
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\GetItemsToPropertyRector\Fixture;

use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\InvoiceUpdateOptions;

final class GetItems
{
    public function run(InvoiceCreateOptions $invoice, RecurringCreateOptions $recurring, InvoiceUpdateOptions $update, \ArrayObject $other): void
    {
        $items = $invoice->items;
        $count = \count($recurring->items);
        $update->getItems();
        $other->getItems();
        $invoice->getItems(1);
        $invoice->{'getItems'}();
    }
}
?>
