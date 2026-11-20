<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\InboxDocumentMetadataKey;
use Justpilot\Billomat\Model\Enum\InboxDocumentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InboxDocumentType::class)]
#[CoversClass(InboxDocumentMetadataKey::class)]
final class InboxDocumentEnumsTest extends TestCase
{
    #[Test]
    public function valuesMatchTheBillomatDocs(): void
    {
        self::assertCount(10, InboxDocumentType::cases());
        self::assertCount(31, InboxDocumentMetadataKey::cases());
        self::assertSame(InboxDocumentType::FUEL_RECEIPT, InboxDocumentType::fromApi('FUEL_RECEIPT'));
        self::assertSame(InboxDocumentMetadataKey::SEPA_CREDITOR_IDENTIFIER, InboxDocumentMetadataKey::fromApi('SEPA_CREDITOR_IDENTIFIER'));
        self::assertNull(InboxDocumentType::fromApi(''));
        self::assertNull(InboxDocumentMetadataKey::fromApi('vendor'));
    }

    #[Test]
    public function everyCaseHasAGermanLabel(): void
    {
        $labels = [
            ...array_map(static fn (InboxDocumentType $c): string => $c->label(), InboxDocumentType::cases()),
            ...array_map(static fn (InboxDocumentMetadataKey $c): string => $c->label(), InboxDocumentMetadataKey::cases()),
        ];

        self::assertNotContains('', $labels);
        self::assertSame('Tankbeleg', InboxDocumentType::FUEL_RECEIPT->label());
        self::assertSame('Name des Absenders', InboxDocumentMetadataKey::SENDER_NAME->label());
        self::assertCount(\count($labels), array_unique($labels));
    }
}
