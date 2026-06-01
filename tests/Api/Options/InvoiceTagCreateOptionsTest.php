<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(InvoiceTagCreateOptions::class)]
final class InvoiceTagCreateOptionsTest extends TestCase
{
    #[Test]
    public function toArrayContainsInvoiceIdAndName(): void
    {
        $options = new InvoiceTagCreateOptions(invoiceId: 17, name: 'wichtig');

        self::assertSame(['invoice_id' => 17, 'name' => 'wichtig'], $options->toArray());
    }
}
