<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\InvoiceMailOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceMailOptions::class)]
final class InvoiceMailOptionsTest extends TestCase
{
    #[Test]
    public function emptyOptionsProduceEmptyPayload(): void
    {
        self::assertSame([], new InvoiceMailOptions()->toArray());
    }

    #[Test]
    public function itConvertsBoolColorAndDuplexToOneZero(): void
    {
        $options = new InvoiceMailOptions();
        $options->color = true;
        $options->duplex = false;

        $payload = $options->toArray();

        self::assertSame(1, $payload['color']);
        self::assertSame(0, $payload['duplex']);
    }

    #[Test]
    public function itPassesStringFieldsThroughUntouched(): void
    {
        $options = new InvoiceMailOptions();
        $options->paperWeight = '100';
        $options->recipientAddress = "Max Mustermann\nMusterstr. 1\n12345 Berlin";

        $payload = $options->toArray();

        self::assertSame('100', $payload['paper_weight']);
        self::assertSame("Max Mustermann\nMusterstr. 1\n12345 Berlin", $payload['recipient_address']);
    }

    #[Test]
    public function itStripsNullFields(): void
    {
        $options = new InvoiceMailOptions();
        $options->color = true;

        $payload = $options->toArray();

        self::assertSame(['color' => 1], $payload);
    }
}
