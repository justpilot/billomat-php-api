<?php

namespace Justpilot\Billomat\Tests\Rector\UpgradeSet\Fixture;

use Justpilot\Billomat\Api\AbstractApi;

final class CustomApiSubclass extends AbstractApi
{
    public function archive(int $id): void
    {
        $this->putEmptyResponse("/invoices/{$id}/archive");
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\UpgradeSet\Fixture;

use Justpilot\Billomat\Api\AbstractApi;

final class CustomApiSubclass extends AbstractApi
{
    public function archive(int $id): void
    {
        $this->putVoid("/invoices/{$id}/archive");
    }
}
?>
