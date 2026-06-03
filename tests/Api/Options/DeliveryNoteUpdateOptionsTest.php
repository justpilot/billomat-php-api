<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\DeliveryNoteUpdateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(DeliveryNoteUpdateOptions::class)]
final class DeliveryNoteUpdateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesOfferId(): void
    {
        $options = new DeliveryNoteUpdateOptions();
        $options->offerId = 1001;

        self::assertSame(1001, $options->toArray()['offer_id']);
    }
}
