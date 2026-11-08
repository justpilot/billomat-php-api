<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\CustomFields;

use Justpilot\Billomat\Api\ClientTagCreateOptions;
use Justpilot\Billomat\Api\UnitCreateOptions;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * GET/PUT /{resource}/{id}/customfield auf einer Ressource und einer Sub-Ressource.
 */
#[CoversNothing]
#[Group('integration')]
final class CustomFieldIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Test]
    public function customFieldOfAResourceCanBeSetReadAndCleared(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $unit = $billomat->units->create(new UnitCreateOptions(name: 'IT-Customfield '.bin2hex(random_bytes(3))));

        try {
            self::assertNull($billomat->units->getCustomField($unit->id));

            $billomat->units->setCustomField($unit->id, 'erp-4711');
            self::assertSame('erp-4711', $billomat->units->getCustomField($unit->id));
            self::assertSame('erp-4711', $billomat->units->getOrFail($unit->id)->customField);

            $billomat->units->setCustomField($unit->id, '');
            self::assertNull($billomat->units->getCustomField($unit->id));
        } finally {
            $billomat->units->delete($unit->id);
        }
    }

    #[Test]
    public function customFieldOfASubResourceCanBeSet(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clients = $billomat->clients->list(['per_page' => 1]);
        if ([] === $clients) {
            self::markTestSkipped('No client in the sandbox to tag.');
        }

        $tag = $billomat->clients->tags->create($clients[0]->id, new ClientTagCreateOptions(name: 'it-customfield-'.bin2hex(random_bytes(3))));
        try {
            $billomat->clients->tags->setCustomField($tag->id, 'tag-1');
            self::assertSame('tag-1', $billomat->clients->tags->getCustomField($tag->id));
        } finally {
            $billomat->clients->tags->delete($tag->id);
        }
    }
}
