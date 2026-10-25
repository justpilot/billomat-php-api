<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\CreditNoteCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\CreditNoteCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(CreditNoteCommentCreateOptions::class)]
final class CreditNoteCommentCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadHasOnlyTheComment(): void
    {
        $options = new CreditNoteCommentCreateOptions(comment: 'Sammelgutschrift erstellt.');

        self::assertSame(
            ['comment' => 'Sammelgutschrift erstellt.'],
            $options->toArray(),
        );
    }

    #[Test]
    public function itSerializesActionkeyAsApiString(): void
    {
        $options = new CreditNoteCommentCreateOptions(
            comment: 'x',
            actionkey: CreditNoteCommentActionKey::COMMENT,
        );

        self::assertSame('COMMENT', $options->toArray()['actionkey']);
    }

    #[Test]
    public function itSerializesPublicFlag(): void
    {
        $options = new CreditNoteCommentCreateOptions(comment: 'x', public: false);

        self::assertFalse($options->toArray()['public']);
    }
}
