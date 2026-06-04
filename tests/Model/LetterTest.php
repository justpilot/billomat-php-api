<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\LetterStatus;
use Justpilot\Billomat\Model\Letter;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Letter::class)]
final class LetterTest extends TestCase
{
    #[Test]
    public function itHydratesFullLetterFromArray(): void
    {
        $letter = Letter::fromArray([
            'id' => '11',
            'client_id' => '42',
            'contact_id' => '100',
            'letter_number' => 'BR-2025-0011',
            'number' => '11',
            'number_pre' => 'BR-',
            'number_length' => '4',
            'status' => 'OPEN',
            'date' => '2025-07-01',
            'subject' => 'Vertragsänderung',
            'label' => 'Wichtig',
            'intro' => 'Sehr geehrte Damen und Herren,',
            'note' => 'Mit freundlichen Grüßen',
            'template_id' => '3',
        ]);

        self::assertSame(11, $letter->id);
        self::assertSame(42, $letter->clientId);
        self::assertSame(100, $letter->contactId);
        self::assertSame('BR-2025-0011', $letter->letterNumber);
        self::assertSame(LetterStatus::OPEN, $letter->status);
        self::assertInstanceOf(DateTimeImmutable::class, $letter->date);
        self::assertSame('Vertragsänderung', $letter->subject);
        self::assertSame(3, $letter->templateId);
    }

    #[Test]
    public function itHandlesMissingOptionalFields(): void
    {
        $letter = Letter::fromArray(['id' => '1', 'client_id' => '1']);

        self::assertSame(1, $letter->id);
        self::assertSame(1, $letter->clientId);
        self::assertNull($letter->status);
        self::assertNull($letter->subject);
        self::assertNull($letter->letterNumber);
        self::assertNull($letter->date);
    }

    #[Test]
    public function itHandlesUnknownStatus(): void
    {
        $letter = Letter::fromArray(['id' => '1', 'client_id' => '1', 'status' => 'BOGUS']);

        self::assertNull($letter->status);
    }

    #[Test]
    public function toArrayRoundTrips(): void
    {
        $array = Letter::fromArray([
            'id' => '1',
            'client_id' => '42',
            'status' => 'CANCELED',
            'subject' => 'Test',
            'date' => '2025-07-01',
        ])->toArray();

        self::assertSame('CANCELED', $array['status']);
        self::assertSame('Test', $array['subject']);
        self::assertSame('2025-07-01', $array['date']);
        self::assertSame(42, $array['client_id']);
    }
}
