<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ClientsCreateIntegrationTestCase extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canCreateClientInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        $options = new ClientCreateOptions();

        $options->name = $faker->company();
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

        if (null !== $created->email) {
            self::assertSame($options->email, $created->email);
        }
    }
}
