<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Incomings;

use DateTimeImmutable;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingUpdateOptions;
use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Model\Incoming;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class IncomingsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureSupplierId(): int
    {
        $billomat = $this->createBillomatClientOrSkip();
        $suppliers = $billomat->suppliers->list(['per_page' => 1]);

        if ([] !== $suppliers) {
            return $suppliers[0]->id;
        }

        $opts = new SupplierCreateOptions(name: 'IT-Lieferant Incoming '.date('His'));
        $opts->countryCode = 'DE';
        $created = $billomat->suppliers->create($opts);
        self::assertNotNull($created->id);

        return $created->id;
    }

    #[Group('integration')]
    #[Test]
    public function canListIncomingsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $incomings = $billomat->incomings->list(['per_page' => 5]);

        self::assertIsArray($incomings);
        self::assertContainsOnlyInstancesOf(Incoming::class, $incomings);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateDeleteIncomingInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $supplierId = $this->ensureSupplierId();
        $faker = $this->faker();

        $opts = new IncomingCreateOptions(supplierId: $supplierId);
        $opts->date = new DateTimeImmutable('today');
        $opts->incomingNumber = 'IT-IN-'.date('His');
        $opts->label = 'Integrationstest Incoming';
        $opts->note = 'Auto-Test';
        $opts->totalGross = $faker->randomFloat(2, 50, 500);
        $opts->currencyCode = 'EUR';

        $incoming = $billomat->incomings->create($opts);

        self::assertInstanceOf(Incoming::class, $incoming);
        self::assertNotNull($incoming->id);
        self::assertSame($supplierId, $incoming->supplierId);

        // Update
        $update = new IncomingUpdateOptions();
        $update->label = 'Geänderter Label';
        $updated = $billomat->incomings->update($incoming->id, $update);

        self::assertSame($incoming->id, $updated->id);

        // Cleanup
        self::assertTrue($billomat->incomings->delete($incoming->id));
        self::assertNull($billomat->incomings->get($incoming->id));
    }
}
