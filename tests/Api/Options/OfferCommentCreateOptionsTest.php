<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\OfferCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(OfferCommentCreateOptions::class)]
final class OfferCommentCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadHasParentIdAndComment(): void
    {
        $options = new OfferCommentCreateOptions(offerId: 17, comment: 'Vom Kunden angenommen.');

        self::assertSame(
            ['offer_id' => 17, 'comment' => 'Vom Kunden angenommen.'],
            $options->toArray(),
        );
    }

    #[Test]
    public function itSerializesActionkeyAsApiString(): void
    {
        $options = new OfferCommentCreateOptions(offerId: 1, comment: 'x');
        $options->actionkey = OfferCommentActionKey::COMMENT;

        self::assertSame('COMMENT', $options->toArray()['actionkey']);
    }

    #[Test]
    public function itSerializesPublicFlag(): void
    {
        $options = new OfferCommentCreateOptions(offerId: 1, comment: 'x');
        $options->public = true;

        self::assertTrue($options->toArray()['public']);
    }
}
