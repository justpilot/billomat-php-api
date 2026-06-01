<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use Justpilot\Billomat\Model\InvoiceComment;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceComment::class)]
final class InvoiceCommentTest extends TestCase
{
    #[Test]
    public function itHydratesFullPayload(): void
    {
        $comment = InvoiceComment::fromArray([
            'id' => 17,
            'created' => '2026-01-15T10:11:12+01:00',
            'invoice_id' => 123,
            'user_id' => '42',
            'comment' => 'Status auf bezahlt gesetzt.',
            'actionkey' => 'PAYMENT',
        ]);

        self::assertSame(17, $comment->id);
        self::assertSame(123, $comment->invoiceId);
        self::assertSame(42, $comment->userId);
        self::assertSame('Status auf bezahlt gesetzt.', $comment->comment);
        self::assertSame(InvoiceCommentActionKey::PAYMENT, $comment->actionkey);
        self::assertSame('PAYMENT', $comment->actionkeyRaw);
        self::assertNotNull($comment->created);
        self::assertSame('2026-01-15', $comment->created->format('Y-m-d'));
    }

    #[Test]
    public function unknownActionkeyKeepsRawStringButEnumIsNull(): void
    {
        $comment = InvoiceComment::fromArray([
            'id' => 1,
            'invoice_id' => 2,
            'actionkey' => 'SOMETHING_NEW',
        ]);

        self::assertNull($comment->actionkey);
        self::assertSame('SOMETHING_NEW', $comment->actionkeyRaw);
    }

    #[Test]
    public function missingActionkeyParsesAsNullBoth(): void
    {
        $comment = InvoiceComment::fromArray([
            'id' => 1,
            'invoice_id' => 2,
            'comment' => 'manuell',
        ]);

        self::assertNull($comment->actionkey);
        self::assertNull($comment->actionkeyRaw);
    }

    #[Test]
    public function toArrayRoundtripsTheRawActionkey(): void
    {
        $comment = InvoiceComment::fromArray([
            'id' => 1,
            'invoice_id' => 2,
            'actionkey' => 'SOMETHING_NEW',
        ]);

        $arr = $comment->toArray();

        self::assertSame('SOMETHING_NEW', $arr['actionkey']);
    }
}
