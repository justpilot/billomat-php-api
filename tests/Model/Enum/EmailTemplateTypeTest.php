<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\EmailTemplateType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(EmailTemplateType::class)]
final class EmailTemplateTypeTest extends TestCase
{
    #[Test]
    public function everyCaseHasItsGermanLabel(): void
    {
        $labels = [];
        foreach (EmailTemplateType::cases() as $case) {
            $labels[$case->value] = $case->label();
        }

        self::assertSame(['INVOICES' => 'Rechnungen', 'OFFERS' => 'Angebote', 'CONFIRMATIONS' => 'Auftragsbestätigungen', 'CREDIT_NOTES' => 'Gutschriften', 'DELIVERY_NOTES' => 'Lieferscheine', 'REMINDERS' => 'Mahnungen', 'PAYMENT_THANKS' => 'Zahlungsdank'], $labels);
    }
}
