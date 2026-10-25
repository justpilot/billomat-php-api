<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Suppliers;

use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SupplierTagCreateOptions;
use Justpilot\Billomat\Api\SupplierUpdateOptions;
use Justpilot\Billomat\Model\Supplier;
use Justpilot\Billomat\Model\SupplierTag;
use Justpilot\Billomat\Model\SupplierTagCloudEntry;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class SuppliersIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canListSuppliersFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $suppliers = $billomat->suppliers->list(['per_page' => 5]);

        self::assertIsArray($suppliers);
        self::assertContainsOnlyInstancesOf(Supplier::class, $suppliers);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateDeleteSupplierInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        $opts = new SupplierCreateOptions(
            name: 'IT-Lieferant '.date('His'),
            city: $faker->city(),
            countryCode: 'DE',
            email: $faker->unique()->safeEmail(),
            phone: $faker->phoneNumber(),
        );

        $supplier = $billomat->suppliers->create($opts);

        self::assertNotNull($supplier->id);

        // Update
        $update = new SupplierUpdateOptions(city: 'München');
        $updated = $billomat->suppliers->update($supplier->id, $update);

        self::assertSame($supplier->id, $updated->id);

        // Get
        $fetched = $billomat->suppliers->get($supplier->id);
        self::assertInstanceOf(Supplier::class, $fetched);
        self::assertSame('München', $fetched->city);

        // Cleanup
        $billomat->suppliers->delete($supplier->id);
        self::assertNull($billomat->suppliers->get($supplier->id));
    }

    #[Group('integration')]
    #[Test]
    public function canManageSupplierTagsInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $supplierOpts = new SupplierCreateOptions(name: 'IT-Tag-Lieferant '.date('His'));
        $supplier = $billomat->suppliers->create($supplierOpts);

        try {
            $tagOpts = new SupplierTagCreateOptions(supplierId: $supplier->id, name: 'IT-SupTag-'.date('His'));
            $tag = $billomat->suppliers->tags->create($tagOpts);

            self::assertNotNull($tag->id);

            $tags = $billomat->suppliers->tags->list($supplier->id);
            self::assertContainsOnlyInstancesOf(SupplierTag::class, $tags);
            self::assertGreaterThanOrEqual(1, \count($tags));

            $cloud = $billomat->suppliers->tags->cloud();
            self::assertContainsOnlyInstancesOf(SupplierTagCloudEntry::class, $cloud);

            $billomat->suppliers->tags->delete($tag->id);
        } finally {
            $billomat->suppliers->delete($supplier->id);
        }
    }
}
