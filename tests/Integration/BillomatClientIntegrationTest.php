<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\TestCase;
use Faker\Factory as FakerFactory;

final class BillomatClientIntegrationTest extends TestCase
{
    /**
     * @group integration
     */
    public function test_can_list_clients_from_sandbox(): void
    {
        $billomatId = getenv('BILLOMAT_ID') ?: null;
        $apiKey = getenv('BILLOMAT_API_KEY') ?: null;

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('BILLOMAT_ID or BILLOMAT_API_KEY not set in .env.test/.env.test.local');
        }

        $client = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        $clients = $client->clients->list(['per_page' => 1]);

        self::assertIsArray($clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        if ($clients !== []) {
            $first = $clients[0];
            self::assertIsInt($first->id);
            self::assertIsString($first->name);
        }
    }

    /**
     * @group integration
     */
    public function test_can_create_client_in_sandbox(): void
    {
        $billomatId = getenv('BILLOMAT_ID') ?: null;
        $apiKey = getenv('BILLOMAT_API_KEY') ?: null;

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('BILLOMAT_ID or BILLOMAT_API_KEY not set in .env.test/.env.test.local');
        }

        $client = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        $faker = FakerFactory::create();

        $name = $faker->company();

        $new = Client::new(
            name: $name,
            firstName: $faker->firstName(),
            lastName: $faker->lastName(),
            salutation: $faker->randomElement(['Herr', 'Frau']),
            clientNumber: null,
            email: $faker->email(),
            phone: $faker->phoneNumber(),
            street: $faker->streetName(),
            zip: $faker->postcode(),
            city: $faker->city(),
        );

        $created = $client->clients->create($new);

        self::assertSame($name, $created->name);
        self::assertNotNull($created->id);
        self::assertGreaterThan(0, $created->id);
    }
}