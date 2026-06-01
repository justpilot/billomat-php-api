<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Model\InvoiceTag;
use Justpilot\Billomat\Model\InvoiceTagCloudEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceTag::class)]
#[CoversClass(InvoiceTagCloudEntry::class)]
final class InvoiceTagTest extends TestCase
{
    #[Test]
    public function tagHydratesFromArray(): void
    {
        $tag = InvoiceTag::fromArray(['id' => '7', 'invoice_id' => '42', 'name' => 'wichtig']);

        self::assertSame(7, $tag->id);
        self::assertSame(42, $tag->invoiceId);
        self::assertSame('wichtig', $tag->name);
        self::assertSame(['id' => 7, 'invoice_id' => 42, 'name' => 'wichtig'], $tag->toArray());
    }

    #[Test]
    public function cloudEntryHydratesCount(): void
    {
        $entry = InvoiceTagCloudEntry::fromArray(['id' => '1', 'name' => 'foo', 'count' => '3']);

        self::assertSame(1, $entry->id);
        self::assertSame('foo', $entry->name);
        self::assertSame(3, $entry->count);
    }

    #[Test]
    public function cloudEntryDefaultsCountToZero(): void
    {
        $entry = InvoiceTagCloudEntry::fromArray(['name' => 'foo']);

        self::assertSame(0, $entry->count);
        self::assertNull($entry->id);
    }
}
