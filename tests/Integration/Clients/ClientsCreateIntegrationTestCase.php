<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

use Justpilot\Billomat\Api\ClientCreateOptions;
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

        $options = new ClientCreateOptions(
            name: $faker->company(),
            street: $faker->streetName(),
            zip: $faker->postcode(),
            city: $faker->city(),
            countryCode: 'DE',
            firstName: $faker->firstName(),
            lastName: $faker->lastName(),
            salutation: $faker->randomElement(['Herr', 'Frau']),
            email: $faker->unique()->safeEmail(),
            phone: $faker->phoneNumber(),
            debitorAccountNumber: $faker->numberBetween(10000, 99999),
        );

        $created = $billomat->clients->create($options);

        self::assertNotNull($created->id);
        self::assertGreaterThan(0, $created->id);
        self::assertSame($options->name, $created->name);

        if (null !== $created->email) {
            self::assertSame($options->email, $created->email);
        }
    }
}
