<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\IncomingUpdateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(IncomingUpdateOptions::class)]
final class IncomingUpdateOptionsTest extends TestCase
{
    #[Test]
    public function itSerializesNewSpecFields(): void
    {
        $options = new IncomingUpdateOptions();
        $options->number = 'RE-2026-001';
        $options->category = 'Hosting';
        $options->clientNumber = 'AC-42';
        $options->expenseAccountNumber = 4980;

        $payload = $options->toArray();

        self::assertSame('RE-2026-001', $payload['number']);
        self::assertSame('Hosting', $payload['category']);
        self::assertSame('AC-42', $payload['client_number']);
        self::assertSame(4980, $payload['expense_account_number']);
    }
}
