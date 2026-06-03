<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeliveryNoteCreateOptions::class)]
final class DeliveryNoteCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesOfferIdAlongsideInvoiceAndConfirmation(): void
    {
        $options = new DeliveryNoteCreateOptions(clientId: 7);
        $options->offerId = 1001;
        $options->invoiceId = 2002;
        $options->confirmationId = 3003;

        $payload = $options->toArray();

        self::assertSame(1001, $payload['offer_id']);
        self::assertSame(2002, $payload['invoice_id']);
        self::assertSame(3003, $payload['confirmation_id']);
    }

    #[Test]
    public function itOmitsOfferIdWhenNull(): void
    {
        $options = new DeliveryNoteCreateOptions(clientId: 7);

        self::assertArrayNotHasKey('offer_id', $options->toArray());
    }
}
