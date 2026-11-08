<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Invoices;

use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * GET /invoices/preset liefert einen Rechnungsentwurf ohne Positionen, den create() anlegt.
 */
#[CoversNothing]
#[Group('integration')]
final class InvoicePresetIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Test]
    public function aPresetFromAConfirmationCanBeCreated(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $confirmations = $billomat->confirmations->list(['per_page' => 1]);
        if ([] === $confirmations) {
            self::markTestSkipped('No confirmation in the sandbox to build a preset from.');
        }
        $confirmation = $confirmations[0];

        $options = $billomat->invoices->preset(confirmationId: $confirmation->id);

        self::assertSame($confirmation->clientId, $options->clientId);
        self::assertSame($confirmation->id, $options->confirmationId);
        self::assertSame([], $options->items);

        $invoice = $billomat->invoices->create($options->withLabel('IT-Preset'));
        try {
            self::assertSame(InvoiceStatus::DRAFT, $invoice->status);
            self::assertSame($confirmation->clientId, $invoice->clientId);
            self::assertSame('IT-Preset', $invoice->label);
            // Billomat übernimmt die Positionen der Quelle nicht.
            self::assertSame([], $billomat->invoices->items->list($invoice->id));
        } finally {
            $billomat->invoices->delete($invoice->id);
        }
    }

    #[Test]
    public function aPresetFromAClientUsesTheAccountDefaults(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clients = $billomat->clients->list(['per_page' => 1]);
        if ([] === $clients) {
            self::markTestSkipped('No client in the sandbox to build a preset from.');
        }

        $options = $billomat->invoices->preset(clientId: $clients[0]->id);

        self::assertSame($clients[0]->id, $options->clientId);
        self::assertNotNull($options->numberPre);
    }

    #[Test]
    public function aPresetWithoutSourceIsRejected(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $this->expectException(ValidationException::class);

        (void) $billomat->invoices->preset();
    }
}
