<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceCommentCreateOptions::class)]
final class InvoiceCommentCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadHasOnlyTheComment(): void
    {
        $options = new InvoiceCommentCreateOptions(comment: 'Anruf vom Kunden.');

        self::assertSame(
            ['comment' => 'Anruf vom Kunden.'],
            $options->toArray(),
        );
    }

    #[Test]
    public function itSerializesActionkeyAsApiString(): void
    {
        $options = new InvoiceCommentCreateOptions(
            comment: 'x',
            actionkey: InvoiceCommentActionKey::COMMENT,
        );

        $payload = $options->toArray();

        self::assertSame('COMMENT', $payload['actionkey']);
    }

    #[Test]
    public function itOmitsActionkeyWhenNull(): void
    {
        $options = new InvoiceCommentCreateOptions(comment: 'x');

        self::assertArrayNotHasKey('actionkey', $options->toArray());
    }

    #[Test]
    public function itSerializesPublicFlag(): void
    {
        $options = new InvoiceCommentCreateOptions(comment: 'x', public: true);

        self::assertTrue($options->toArray()['public']);
    }

    #[Test]
    public function itOmitsPublicWhenNull(): void
    {
        $options = new InvoiceCommentCreateOptions(comment: 'x');

        self::assertArrayNotHasKey('public', $options->toArray());
    }
}
