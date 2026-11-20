<?php

namespace Justpilot\Billomat\Tests\Rector\ParentIdToCreateArgumentRector\Fixture;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\BillomatClient;

final class SkipUnrelatedCreates
{
    public function run(BillomatClient $billomat, int $id, InvoiceTagCreateOptions $options, array $args, string $class): void
    {
        $billomat->invoices->tags->create($id, new InvoiceTagCreateOptions(name: 'x'));
        $billomat->invoices->tags->create($options);
        $billomat->invoices->tags->create(new InvoiceTagCreateOptions(name: 'x'));
        $billomat->invoices->tags->create(new InvoiceTagCreateOptions(...$args));
        $billomat->invoices->tags->create(...$args);
        $billomat->invoices->tags->create(other: new InvoiceTagCreateOptions($id, 'x'));
        $billomat->invoices->tags->create(new $class($id, 'x'));
        $billomat->invoices->tags->update(new InvoiceTagCreateOptions($id, 'x'));
        $billomat->invoices->tags->create(...);
        $billomat->clients->create(new ClientCreateOptions('ACME'));
        $billomat->clients->properties->create(new PropertyCreateOptions('Branche'));
        $billomat->invoices->items->create($id, new InvoiceItemCreateOptions(1.0, 2.0));
    }
}
?>
