<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Reminders;

use DateTimeImmutable;
use Justpilot\Billomat\Api\ReminderCreateOptions;
use Justpilot\Billomat\Api\ReminderUpdateOptions;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\ReminderStatus;
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
            self::assertNotNull($first->id);
            self::assertIsInt($first->clientId);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateAndDeleteReminderDraftInSandbox(): void
    {
        $invoiceId = $this->findOverdueInvoiceIdOrSkip();
        $billomat = $this->createBillomatClientOrSkip();

        $opts = new ReminderCreateOptions(invoiceId: $invoiceId);
        $opts->date = new DateTimeImmutable('today');
        $opts->subject = 'Integrationstest-Mahnung '.date('d.m.Y H:i:s');
        $opts->label = 'Reminder Integrationstest';

        $reminder = $billomat->reminders->create($opts);

        self::assertInstanceOf(Reminder::class, $reminder);
        self::assertNotNull($reminder->id);
        self::assertSame(ReminderStatus::DRAFT, $reminder->status);

        // Update
        $update = new ReminderUpdateOptions();
        $update->subject = 'Geänderter Mahnungs-Betreff';
        $updated = $billomat->reminders->update($reminder->id, $update);

        self::assertSame($reminder->id, $updated->id);

        // Cleanup
        self::assertTrue($billomat->reminders->delete($reminder->id));
        self::assertNull($billomat->reminders->get($reminder->id));
    }
}
