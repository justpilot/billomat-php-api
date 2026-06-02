<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Contacts;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ContactCreateOptions;
use Justpilot\Billomat\Api\ContactUpdateOptions;
use Justpilot\Billomat\Model\Contact;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ContactsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureClientId(): int
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ([] !== $clients) {
            return $clients[0]->id;
        }

        $faker = $this->faker();
        $opts = new ClientCreateOptions();
        $opts->name = $faker->company();
        $opts->email = $faker->unique()->safeEmail();
        $opts->countryCode = 'DE';

        $created = $billomat->clients->create($opts);
        self::assertNotNull($created->id);

        return $created->id;
    }

    #[Group('integration')]
    #[Test]
    public function canListContactsByClientFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();

        $contacts = $billomat->contacts->listByClient($clientId, ['per_page' => 5]);

        self::assertIsArray($contacts);
        self::assertContainsOnlyInstancesOf(Contact::class, $contacts);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateDeleteContactInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();
        $faker = $this->faker();

        $opts = new ContactCreateOptions(clientId: $clientId);
        $opts->firstName = $faker->firstName();
        $opts->lastName = $faker->lastName();
        $opts->email = $faker->unique()->safeEmail();
        $opts->phone = $faker->phoneNumber();
        $opts->countryCode = 'DE';

        $contact = $billomat->contacts->create($opts);

        self::assertNotNull($contact->id);

        // Update
        $update = new ContactUpdateOptions();
        $update->city = 'Berlin';
        $updated = $billomat->contacts->update($contact->id, $update);

        self::assertSame($contact->id, $updated->id);

        // Get
        $fetched = $billomat->contacts->get($contact->id);
        self::assertInstanceOf(Contact::class, $fetched);
        self::assertSame('Berlin', $fetched->city);

        // Cleanup
        self::assertTrue($billomat->contacts->delete($contact->id));
        self::assertNull($billomat->contacts->get($contact->id));
    }
}
