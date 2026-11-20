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
    public function toArrayContainsOnlyTheName(): void
    {
        $options = new InvoiceTagCreateOptions(name: 'wichtig');

        self::assertSame(['name' => 'wichtig'], $options->toArray());
    }
}
