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
     *
     * Sind BILLOMAT_APP_ID und BILLOMAT_APP_SECRET gesetzt, werden zusätzlich
     * die App-Header gesendet — Billomat gewährt registrierten Apps ein
     * eigenes, höheres Rate-Limit (1000 Requests pro 15 Minuten).
     */
    protected function createBillomatClientOrSkip(): BillomatClient
    {
        $billomatId = getenv('BILLOMAT_ID') ?: null;
        $apiKey = getenv('BILLOMAT_API_KEY') ?: null;

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('Environment variables BILLOMAT_ID or BILLOMAT_API_KEY missing.');
        }

        $appId = getenv('BILLOMAT_APP_ID');
        $appSecret = getenv('BILLOMAT_APP_SECRET');

        // App-Header nur paarweise senden.
        if (!\is_string($appId) || '' === $appId || !\is_string($appSecret) || '' === $appSecret) {
            $appId = $appSecret = null;
        }

        return BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
            appId: $appId,
            appSecret: $appSecret,
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
