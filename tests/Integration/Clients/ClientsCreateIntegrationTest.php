<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

use Faker\Factory as FakerFactory;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\TestCase;

final class ClientsCreateIntegrationTest extends TestCase
{
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

        $billomat = BillomatClient::create(
            billomatId: $billomatId,
            apiKey: $apiKey,
        );

        $faker = FakerFactory::create('de_DE');

        $options = new ClientCreateOptions(
            name: $faker->company(),
        );

        $options->firstName = $faker->firstName();
        $options->lastName = $faker->lastName();
        $options->salutation = $faker->randomElement(['Herr', 'Frau']);
        $options->email = $faker->unique()->safeEmail();
        $options->phone = $faker->phoneNumber();
        $options->street = $faker->streetName();
        $options->zip = $faker->postcode();
        $options->city = $faker->city();
        $options->countryCode = 'DE';
        $options->debitorAccountNumber = $faker->numberBetween(10000, 99999);

        $created = $billomat->clients->create($options);

        self::assertInstanceOf(Client::class, $created);
        self::assertNotNull($created->id);
        self::assertGreaterThan(0, $created->id);
        self::assertSame($options->name, $created->name);

        if ($created->email !== null) {
            self::assertSame($options->email, $created->email);
        }
    }
}