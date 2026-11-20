<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Reminders;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ReminderCreateOptions;
use Justpilot\Billomat\Api\ReminderItemCreateOptions;
use Justpilot\Billomat\Api\ReminderItemUpdateOptions;
use Justpilot\Billomat\Api\ReminderUpdateOptions;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\ReminderStatus;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\Reminder;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class RemindersIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    /**
     * Mahnungen können laut Billomat nur für OVERDUE-Rechnungen erstellt werden.
     */
    private function findOverdueInvoiceIdOrSkip(): int
    {
        $billomat = $this->createBillomatClientOrSkip();
        $invoices = $billomat->invoices->list(['per_page' => 100, 'status' => InvoiceStatus::OVERDUE->value]);
        foreach ($invoices as $invoice) {
            if (null !== $invoice->id && InvoiceStatus::OVERDUE === $invoice->status) {
                return $invoice->id;
            }
        }

        self::markTestSkipped('No OVERDUE invoice found in sandbox – reminder creation requires one.');
    }

    #[Group('integration')]
    #[Test]
    public function canListRemindersFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $reminders = $billomat->reminders->list(['per_page' => 5]);

        self::assertIsArray($reminders);
        self::assertContainsOnlyInstancesOf(Reminder::class, $reminders);

        if ([] !== $reminders) {
            $first = $reminders[0];
            self::assertIsInt($first->clientId);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateAndDeleteReminderDraftInSandbox(): void
    {
        $invoiceId = $this->findOverdueInvoiceIdOrSkip();
        $billomat = $this->createBillomatClientOrSkip();

        $opts = new ReminderCreateOptions(
            invoiceId: $invoiceId,
            date: new DateTimeImmutable('today'),
            subject: 'Integrationstest-Mahnung '.date('d.m.Y H:i:s'),
            label: 'Reminder Integrationstest',
        );

        $reminder = $billomat->reminders->create($opts);

        self::assertSame(ReminderStatus::DRAFT, $reminder->status);

        // Update
        $update = new ReminderUpdateOptions(subject: 'Geänderter Mahnungs-Betreff');
        $updated = $billomat->reminders->update($reminder->id, $update);

        self::assertSame($reminder->id, $updated->id);

        // Cleanup
        $billomat->reminders->delete($reminder->id);
        self::assertNull($billomat->reminders->get($reminder->id));
    }

    /**
     * Billomat legt die Positionen der Rechnung selbst an; weitere lassen sich ergänzen, ändern und löschen.
     */
    #[Group('integration')]
    #[Test]
    public function itemsOfAReminderDraftCanBeManaged(): void
    {
        $invoiceId = $this->findOverdueInvoiceIdOrSkip();
        $billomat = $this->createBillomatClientOrSkip();
        $reminder = $billomat->reminders->create(new ReminderCreateOptions(invoiceId: $invoiceId, date: new DateTimeImmutable('today')));

        try {
            $item = $billomat->reminders->items->create($reminder->id, new ReminderItemCreateOptions(quantity: 1.0, unitPrice: Money::of('2.50'), title: 'Porto'));
            self::assertSame($reminder->id, $item->reminderId);
            self::assertSame('2.5', $item->unitPrice->toString());

            self::assertSame('Porto und Versand', $billomat->reminders->items->update($item->id, new ReminderItemUpdateOptions(title: 'Porto und Versand'))->title);

            $billomat->reminders->items->delete($item->id);
            self::assertNull($billomat->reminders->items->get($item->id));
        } finally {
            $billomat->reminders->delete($reminder->id);
        }
    }
}
