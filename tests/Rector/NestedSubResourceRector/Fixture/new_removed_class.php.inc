<?php

namespace Justpilot\Billomat\Tests\Rector\NestedSubResourceRector\Fixture;

use Justpilot\Billomat\Api\InvoiceCommentsApi;
use Justpilot\Billomat\Api\ReminderItemsApi;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;

final class NewRemovedClass
{
    public function run(BillomatHttpClientInterface $http): void
    {
        $comments = new InvoiceCommentsApi($http);
        $properties = new \Justpilot\Billomat\Api\SupplierPropertiesApi($http);
        $items = new ReminderItemsApi($http);
        $levels = new \Justpilot\Billomat\Api\DunningLevelsApi($http);
        $class = InvoiceCommentsApi::class;
        $dynamic = new $class($http);
        $anonymous = new class {};
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\NestedSubResourceRector\Fixture;

use Justpilot\Billomat\Api\InvoiceCommentsApi;
use Justpilot\Billomat\Api\ReminderItemsApi;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;

final class NewRemovedClass
{
    public function run(BillomatHttpClientInterface $http): void
    {
        $comments = (new \Justpilot\Billomat\Api\InvoicesApi($http))->comments;
        $properties = (new \Justpilot\Billomat\Api\SuppliersApi($http))->properties;
        $items = new ReminderItemsApi($http);
        $levels = new \Justpilot\Billomat\Api\DunningLevelsApi($http);
        $class = InvoiceCommentsApi::class;
        $dynamic = new $class($http);
        $anonymous = new class {};
    }
}
?>
