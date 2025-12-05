<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Taxes;

use Justpilot\Billomat\Api\TaxRateCreateOptions;
use Justpilot\Billomat\Model\TaxRate;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

final class TaxesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    public function test_can_list_tax_rates_from_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $taxes = $billomat->taxes->list([
            'per_page' => 10,
        ]);

        self::assertIsArray($taxes);
        self::assertContainsOnlyInstancesOf(TaxRate::class, $taxes);

        if ($taxes !== []) {
            $first = $taxes[0];

            self::assertNotNull($first->id);
            self::assertGreaterThan(0, $first->id);

            self::assertNotSame('', trim($first->name));
            self::assertIsFloat($first->rate);
        }
    }

    #[Group('integration')]
    public function test_can_create_and_delete_tax_rate_in_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) neuen Steuersatz anlegen
        $name = 'SDK Integration Test Tax ' . $faker->numberBetween(1000, 9999);
        $rate = $faker->randomElement([7.0, 9.5, 21.0]);
        $isDefault = false;

        $options = new TaxRateCreateOptions(
            name: $name,
            rate: $rate,
            isDefault: $isDefault,
        );

        $created = $billomat->taxes->create($options);

        self::assertInstanceOf(TaxRate::class, $created);
        self::assertNotNull($created->id);
        self::assertGreaterThan(0, $created->id);
        self::assertSame($name, $created->name);
        self::assertSame($rate, $created->rate);
        self::assertFalse($created->isDefault);

        $taxId = $created->id;

        // 2) erneut abrufen
        $fetched = $billomat->taxes->get($taxId);

        self::assertInstanceOf(TaxRate::class, $fetched);
        self::assertSame($taxId, $fetched->id);
        self::assertSame($name, $fetched->name);
        self::assertSame($rate, $fetched->rate);

        // 3) löschen
        $deleteResult = $billomat->taxes->delete($taxId);
        self::assertTrue($deleteResult);

        // 4) sicherstellen, dass er weg ist (get() gibt null zurück)
        $afterDelete = $billomat->taxes->get($taxId);
        self::assertNull($afterDelete);
    }
}