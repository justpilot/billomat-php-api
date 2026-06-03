<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\SupplierUpdateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(SupplierUpdateOptions::class)]
final class SupplierUpdateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesBankSwiftClientNumberAndCreditorIdentifier(): void
    {
        $options = new SupplierUpdateOptions();
        $options->bankSwift = 'COBADEFFXXX';
        $options->clientNumber = 'LIEF-001';
        $options->creditorIdentifier = 'DE98ZZZ09999999999';

        $payload = $options->toArray();

        self::assertSame('COBADEFFXXX', $payload['bank_swift']);
        self::assertSame('LIEF-001', $payload['client_number']);
        self::assertSame('DE98ZZZ09999999999', $payload['creditor_identifier']);
    }
}
