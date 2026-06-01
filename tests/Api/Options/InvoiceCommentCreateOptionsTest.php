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
    public function minimalPayloadHasInvoiceIdAndComment(): void
    {
        $options = new InvoiceCommentCreateOptions(invoiceId: 17, comment: 'Anruf vom Kunden.');

        self::assertSame(
            ['invoice_id' => 17, 'comment' => 'Anruf vom Kunden.'],
            $options->toArray(),
        );
    }

    #[Test]
    public function itSerializesActionkeyAsApiString(): void
    {
        $options = new InvoiceCommentCreateOptions(invoiceId: 1, comment: 'x');
        $options->actionkey = InvoiceCommentActionKey::COMMENT;

        $payload = $options->toArray();

        self::assertSame('COMMENT', $payload['actionkey']);
    }

    #[Test]
    public function itOmitsActionkeyWhenNull(): void
    {
        $options = new InvoiceCommentCreateOptions(invoiceId: 1, comment: 'x');

        self::assertArrayNotHasKey('actionkey', $options->toArray());
    }
}
