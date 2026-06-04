<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Model\User;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(User::class)]
final class UserTest extends TestCase
{
    #[Test]
    public function itHydratesFullUserFromArray(): void
    {
        $user = User::fromArray([
            'id' => '3',
            'email' => 'admin@example.com',
            'first_name' => 'Alice',
            'last_name' => 'Admin',
            'salutation' => 'Frau',
            'phone' => '+49 30 12345',
            'mobile' => '+49 170 1234567',
            'fax' => '+49 30 12346',
            'role_id' => '1',
        ]);

        self::assertSame(3, $user->id);
        self::assertSame('admin@example.com', $user->email);
        self::assertSame('Alice', $user->firstName);
        self::assertSame('Admin', $user->lastName);
        self::assertSame(1, $user->roleId);
    }

    #[Test]
    public function itHandlesMissingOptionalFields(): void
    {
        $user = User::fromArray(['id' => '1', 'email' => 'min@example.com']);

        self::assertSame(1, $user->id);
        self::assertSame('min@example.com', $user->email);
        self::assertNull($user->firstName);
        self::assertNull($user->roleId);
    }

    #[Test]
    public function itFiltersEmptyStringAsNullForRoleId(): void
    {
        $user = User::fromArray([
            'id' => '1',
            'email' => 'a@b.de',
            'role_id' => '',
        ]);

        self::assertNull($user->roleId);
    }

    #[Test]
    public function toArrayMapsCamelCaseBackToSnakeCase(): void
    {
        $array = User::fromArray([
            'id' => '3',
            'email' => 'admin@example.com',
            'first_name' => 'Alice',
            'role_id' => '2',
        ])->toArray();

        self::assertSame('Alice', $array['first_name']);
        self::assertSame(2, $array['role_id']);
        self::assertSame('admin@example.com', $array['email']);
    }
}
