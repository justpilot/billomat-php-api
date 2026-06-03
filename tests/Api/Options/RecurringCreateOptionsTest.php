<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use DateTimeImmutable;
use Justpilot\Billomat\Api\RecurringCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(RecurringCreateOptions::class)]
final class RecurringCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesAllNewlyExposedFields(): void
    {
        $options = new RecurringCreateOptions(clientId: 1);
        $options->nextCreationDate = new DateTimeImmutable('2026-07-01');
        $options->emailFilename = 'rechnung.pdf';
        $options->emailBcc = true;
        $options->letterColor = false;
        $options->letterDuplex = true;
        $options->letterPaperWeight = 100;
        $options->offerId = 42;
        $options->confirmationId = 7;

        $payload = $options->toArray();

        self::assertSame('2026-07-01', $payload['next_creation_date']);
        self::assertSame('rechnung.pdf', $payload['email_filename']);
        self::assertTrue($payload['email_bcc']);
        self::assertFalse($payload['letter_color']);
        self::assertTrue($payload['letter_duplex']);
        self::assertSame(100, $payload['letter_paper_weight']);
        self::assertSame(42, $payload['offer_id']);
        self::assertSame(7, $payload['confirmation_id']);
    }
}
