<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use DateTimeImmutable;
use Justpilot\Billomat\Api\Clear;
use Justpilot\Billomat\Api\ClientUpdateOptions;
use Justpilot\Billomat\Api\InvoiceUpdateOptions;
use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\Model\Enum\NetGross;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(ClientUpdateOptions::class)]
#[CoversClass(InvoiceUpdateOptions::class)]
#[CoversClass(SettingsUpdateOptions::class)]
final class ClearTest extends TestCase
{
    #[Test]
    public function updateOptionsSendClearedScalarAndBoolFieldsAsNull(): void
    {
        $options = new ClientUpdateOptions(name: 'ACME', note: Clear::Field, archived: Clear::Field);

        self::assertSame(['name' => 'ACME', 'note' => null, 'archived' => null], $options->toArray());
    }

    #[Test]
    public function updateOptionsStillSerializeBoolsAsOneOrZero(): void
    {
        self::assertSame(['archived' => 1], new ClientUpdateOptions(archived: true)->toArray());
        self::assertSame(['archived' => 0], new ClientUpdateOptions(archived: false)->toArray());
    }

    #[Test]
    public function updateOptionsSendClearedEnumAndDateFieldsAsNull(): void
    {
        $options = new InvoiceUpdateOptions(dueDate: Clear::Field, netGross: Clear::Field);

        self::assertSame(['due_date' => null, 'net_gross' => null], $options->toArray());
    }

    #[Test]
    public function updateOptionsStillFormatEnumAndDateValues(): void
    {
        $options = new InvoiceUpdateOptions(dueDate: new DateTimeImmutable('2026-11-30'), netGross: NetGross::GROSS);

        self::assertSame(['due_date' => '2026-11-30', 'net_gross' => 'GROSS'], $options->toArray());
    }

    #[Test]
    public function witherCanClearAField(): void
    {
        $options = new ClientUpdateOptions(note: 'alt');

        self::assertSame(['note' => null], $options->withNote(Clear::Field)->toArray());
        self::assertSame(['note' => 'alt'], $options->toArray());
    }

    #[Test]
    public function settingsUpdateOptionsSupportClear(): void
    {
        $options = new SettingsUpdateOptions(netGross: Clear::Field, invoiceNote: Clear::Field, printVersion: Clear::Field);

        self::assertSame(['net_gross' => null, 'invoice_note' => null, 'print_version' => null], $options->toArray());
    }
}
