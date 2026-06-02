<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\ClientTags;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ClientTagCreateOptions;
use Justpilot\Billomat\Model\ClientTag;
use Justpilot\Billomat\Model\ClientTagCloudEntry;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ClientTagsIntegrationTest extends AbstractBillomatIntegrationTestCase
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
    public function canShowClientTagCloudFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $cloud = $billomat->clientTags->cloud();

        self::assertIsArray($cloud);
        self::assertContainsOnlyInstancesOf(ClientTagCloudEntry::class, $cloud);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateListAndDeleteClientTagInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->ensureClientId();

        $opts = new ClientTagCreateOptions(clientId: $clientId, name: 'IT-CT-'.date('His'));
        $tag = $billomat->clientTags->create($opts);

        self::assertInstanceOf(ClientTag::class, $tag);
        self::assertNotNull($tag->id);

        try {
            $tags = $billomat->clientTags->listByClient($clientId);
            self::assertContainsOnlyInstancesOf(ClientTag::class, $tags);
            self::assertGreaterThanOrEqual(1, \count($tags));
        } finally {
            self::assertTrue($billomat->clientTags->delete($tag->id));
        }
    }
}
