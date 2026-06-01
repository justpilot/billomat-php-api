<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration;

use Faker\Factory as FakerFactory;
use Faker\Generator;
use Justpilot\Billomat\BillomatClient;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\TestCase;

#[CoversNothing]
abstract class AbstractBillomatIntegrationTestCase extends TestCase
{
    private ?Generator $faker = null;

    /**
     * Erstellt einen BillomatClient oder skippt den Test,
     * wenn die benötigten Umgebungsvariablen fehlen.
     */
    protected function createBillomatClientOrSkip(): BillomatClient
    {
        $billomatId = getenv('BILLOMAT_ID') ?: null;
        $apiKey = getenv('BILLOMAT_API_KEY') ?: null;

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('Environment variables BILLOMAT_ID or BILLOMAT_API_KEY missing.');
        }

        return BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );
    }

    /**
     * Gemeinsamer Faker für alle Integrationstests.
     */
    protected function faker(): Generator
    {
        if (!$this->faker instanceof Generator) {
            $this->faker = FakerFactory::create('de_DE');
        }

        return $this->faker;
    }
}
