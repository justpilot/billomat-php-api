<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Letters;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\LetterCreateOptions;
use Justpilot\Billomat\Api\LetterUpdateOptions;
use Justpilot\Billomat\Model\Enum\LetterStatus;
use Justpilot\Billomat\Model\Letter;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class LettersIntegrationTest extends AbstractBillomatIntegrationTestCase
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
    public function canListLettersFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $letters = $billomat->letters->list(['per_page' => 5]);

        self::assertIsArray($letters);
        self::assertContainsOnlyInstancesOf(Letter::class, $letters);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateAndDeleteLetterDraftInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();

        $opts = new LetterCreateOptions(clientId: $clientId);
        $opts->date = new DateTimeImmutable('today');
        $opts->subject = 'Integrationstest-Brief '.date('d.m.Y H:i:s');
        $opts->label = 'Letter Integrationstest';
        $opts->intro = 'Sehr geehrte Damen und Herren,';
        $opts->note = 'Dies ist ein automatisierter Test.';

        $letter = $billomat->letters->create($opts);

        self::assertNotNull($letter->id);
        self::assertSame($clientId, $letter->clientId);
        self::assertSame(LetterStatus::DRAFT, $letter->status);

        // Update
        $update = new LetterUpdateOptions();
        $update->subject = 'Geänderter Betreff';
        $updated = $billomat->letters->update($letter->id, $update);

        self::assertSame($letter->id, $updated->id);

        // Cleanup
        self::assertTrue($billomat->letters->delete($letter->id));
        self::assertNull($billomat->letters->get($letter->id));
    }
}
