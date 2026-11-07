<?php

namespace Justpilot\Billomat\Tests\Rector\RenamedMemberRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class ListParameters
{
    public function run(BillomatClient $billomat): void
    {
        $billomat->contacts->listByClient(clientId: 1, query: ['per_page' => 10]);
        $billomat->contacts->iterateAll(clientId: 1);
        $billomat->search->query('ACME', extraFilters: ['per_page' => 5]);
        $billomat->taxes->list(query: ['per_page' => 50]);
        $billomat->templates->list(query: ['type' => 'INVOICE']);
        $billomat->clients->list(filters: ['name' => 'x']);
        $billomat->clients->list(query: ['name' => 'x']);
        $billomat->contacts->get(clientId: 1);
        $billomat->search?->query('ACME', extraFilters: []);
        $billomat->invoices->get(id: 1);
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\RenamedMemberRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class ListParameters
{
    public function run(BillomatClient $billomat): void
    {
        $billomat->contacts->listByClient(parentId: 1, filters: ['per_page' => 10]);
        $billomat->contacts->iterateAll(parentId: 1);
        $billomat->search->query('ACME', filters: ['per_page' => 5]);
        $billomat->taxes->list(filters: ['per_page' => 50]);
        $billomat->templates->list(filters: ['type' => 'INVOICE']);
        $billomat->clients->list(filters: ['name' => 'x']);
        $billomat->clients->list(query: ['name' => 'x']);
        $billomat->contacts->get(clientId: 1);
        $billomat->search?->query('ACME', filters: []);
        $billomat->invoices->get(id: 1);
    }
}
?>
