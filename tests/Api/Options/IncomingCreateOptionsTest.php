<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\IncomingCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncomingCreateOptions::class)]
final class IncomingCreateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesNewSpecFields(): void
    {
        $options = new IncomingCreateOptions(supplierId: 5);
        $options->number = 'RE-2026-001';
        $options->base64file = 'JVBERi0xLg==';
        $options->category = 'Software';
        $options->clientNumber = 'AC-42';
        $options->expenseAccountNumber = 4980;

        $payload = $options->toArray();

        self::assertSame('RE-2026-001', $payload['number']);
        self::assertSame('JVBERi0xLg==', $payload['base64file']);
        self::assertSame('Software', $payload['category']);
        self::assertSame('AC-42', $payload['client_number']);
        self::assertSame(4980, $payload['expense_account_number']);
    }

    #[Test]
    public function minimalPayloadHasOnlySupplierId(): void
    {
        $options = new IncomingCreateOptions(supplierId: 5);

        self::assertSame(['supplier_id' => 5], $options->toArray());
    }
}
