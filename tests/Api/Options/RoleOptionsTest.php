<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use InvalidArgumentException;
use Justpilot\Billomat\Api\RoleCreateOptions;
use Justpilot\Billomat\Api\RoleUpdateOptions;
use Justpilot\Billomat\Model\Enum\AccessLevel;
use Justpilot\Billomat\Model\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(RoleCreateOptions::class)]
#[CoversClass(RoleUpdateOptions::class)]
#[CoversClass(Role::class)]
#[UsesClass(AccessLevel::class)]
final class RoleOptionsTest extends TestCase
{
    #[Test]
    public function createSendsNameAndPermissionsAsSingleFields(): void
    {
        $options = new RoleCreateOptions(name: 'Buchhaltung', permissions: ['invoices' => AccessLevel::DELETE]);

        self::assertSame(['name' => 'Buchhaltung', 'invoices' => 'DELETE'], $options->toArray());
    }

    #[Test]
    public function createWithersReturnChangedCopies(): void
    {
        $original = new RoleCreateOptions(name: 'Vertrieb', permissions: ['offers' => AccessLevel::UPDATE]);

        $renamed = $original->withName('Innendienst');
        $added = $original->withPermission('settings_documents', AccessLevel::UPDATE);
        $overwritten = $original->withPermission('offers', AccessLevel::READ);
        $replaced = $original->withPermissions(['clients' => AccessLevel::READ]);

        self::assertSame('Innendienst', $renamed->name);
        self::assertSame(['offers' => AccessLevel::UPDATE, 'settings_documents' => AccessLevel::UPDATE], $added->permissions);
        self::assertSame(['offers' => AccessLevel::READ], $overwritten->permissions);
        self::assertSame(['clients' => AccessLevel::READ], $replaced->permissions);
        self::assertSame('Vertrieb', $original->name);
        self::assertSame(['offers' => AccessLevel::UPDATE], $original->permissions);
    }

    #[Test]
    public function updateSendsOnlyGivenFieldsAndEmptiesRevokedAreas(): void
    {
        self::assertSame([], new RoleUpdateOptions()->toArray());
        self::assertSame(['name' => 'Neu'], new RoleUpdateOptions(name: 'Neu')->toArray());
        self::assertSame(
            ['articles' => '', 'clients' => 'READ'],
            new RoleUpdateOptions(permissions: ['articles' => null, 'clients' => AccessLevel::READ])->toArray(),
        );
    }

    #[Test]
    public function updateWithersReturnChangedCopies(): void
    {
        $original = new RoleUpdateOptions(permissions: ['articles' => AccessLevel::READ]);

        $renamed = $original->withName('Neu');
        $revoked = $original->withPermission('articles', null);
        $replaced = $original->withPermissions(['banking' => AccessLevel::READ]);

        self::assertSame('Neu', $renamed->name);
        self::assertSame(['articles' => null], $revoked->permissions);
        self::assertSame(['banking' => AccessLevel::READ], $replaced->permissions);
        self::assertNull($original->name);
        self::assertSame(['articles' => AccessLevel::READ], $original->permissions);
    }

    #[Test]
    public function unknownAreasAreRejectedBecauseBillomatWouldIgnoreThem(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessageIsOrContains('Unknown role permission area "invoice"');

        new RoleCreateOptions(name: 'Tippfehler', permissions: ['invoice' => AccessLevel::READ]);
    }

    #[Test]
    public function everyWayToSetPermissionsChecksTheArea(): void
    {
        $create = new RoleCreateOptions(name: 'x');
        $update = new RoleUpdateOptions();
        $attempts = [
            static fn (): object => new RoleUpdateOptions(permissions: ['unknown' => null]),
            static fn (): object => $create->withPermission('unknown', AccessLevel::READ),
            static fn (): object => $create->withPermissions(['unknown' => AccessLevel::READ]),
            static fn (): object => $update->withPermission('unknown', null),
            static fn (): object => $update->withPermissions(['unknown' => null]),
        ];

        foreach ($attempts as $i => $attempt) {
            try {
                $attempt();
                self::fail("Versuch {$i} hat den unbekannten Bereich angenommen.");
            } catch (InvalidArgumentException $e) {
                self::assertStringContainsString('"unknown"', $e->getMessage());
            }
        }
    }

    #[Test]
    public function settingsAreasAreAcceptedEvenIfNotListed(): void
    {
        self::assertTrue(Role::isArea('settings_future_addon'));
        self::assertTrue(Role::isArea('banking'));
        self::assertFalse(Role::isArea('bank'));
        self::assertSame(['settings_future_addon' => 'UPDATE'], new RoleUpdateOptions(permissions: ['settings_future_addon' => AccessLevel::UPDATE])->toArray());
    }
}
