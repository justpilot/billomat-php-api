<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\FreeTextType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(FreeTextType::class)]
final class FreeTextTypeTest extends TestCase
{
    #[Test]
    public function everyCaseHasItsGermanLabel(): void
    {
        $labels = [];
        foreach (FreeTextType::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        self::assertSame(['INVOICE' => 'Rechnung', 'CORRECTION' => 'Korrekturrechnung', 'OFFER' => 'Angebot', 'CONFIRMATION' => 'Auftragsbestätigung', 'CREDIT_NOTE' => 'Gutschrift', 'DELIVERY_NOTE' => 'Lieferschein', 'LETTER' => 'Brief'], $labels);
    }
}
