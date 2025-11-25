<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Api\ClientCreateOptions;
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
        $billomatId = getenv('BILLOMAT_ID');
        $apiKey = getenv('BILLOMAT_API_KEY');

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('Environment variables BILLOMAT_ID or BILLOMAT_API_KEY missing.');
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
            self::assertNotNull($first->id);
            self::assertIsString($first->name);
        }
    }

    /**
     * @group integration
     */
    public function test_can_create_client_in_sandbox(): void
    {
        $billomatId = getenv('BILLOMAT_ID');
        $apiKey = getenv('BILLOMAT_API_KEY');

        if (!$billomatId || !$apiKey) {
            $this->markTestSkipped('Environment variables BILLOMAT_ID or BILLOMAT_API_KEY missing.');
        }

        $client = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        $faker = FakerFactory::create('de_DE'); // realistische deutsche Daten

        // Realistische minimale Client-Daten
        $options = new ClientCreateOptions(
            name: $faker->company()
        );

        // Sinnvolle Faker-Werte setzen
        $options->firstName = $faker->firstName();
        $options->lastName = $faker->lastName();
        $options->salutation = $faker->randomElement(['Herr', 'Frau']);
        $options->email = $faker->unique()->safeEmail();
        $options->phone = $faker->phoneNumber();
        $options->street = $faker->streetName();
        $options->zip = $faker->postcode();
        $options->city = $faker->city();
        $options->countryCode = 'DE'; // Sandbox sicher
        $options->debitorAccountNumber = $faker->numberBetween(10000, 99999);

        // Client erstellen
        $created = $client->clients->create($options);

        // Assertions
        self::assertInstanceOf(Client::class, $created);
        self::assertNotNull($created->id);
        self::assertGreaterThan(0, $created->id);

        // Name 1:1 wiedererkennbar?
        self::assertSame($options->name, $created->name);

        // Optional weitere Prüfungen
        self::assertSame('DE', $created->countryCode);
        self::assertSame($options->email, $created->email);
    }
}