<?php

namespace Justpilot\Billomat\Tests\Rector\NestedSubResourceRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class FlatProperties
{
    public function __construct(private BillomatClient $billomat)
    {
    }

    public function run(BillomatClient $billomat): void
    {
        $this->billomat->invoiceComments->listByInvoice(1);
        $billomat->creditNotePayments->list();
        $billomat->deliveryNoteItems->get(2);
        $billomat->recurringEmailReceivers->listByRecurring(3);
        $billomat->reminderItems->listByReminder(4);
        $billomat->incomingPropertyValues->list();
        $billomat->userProperties->list();
        $tags = $billomat->clientTags;
        $levels = $billomat->dunningLevels->list();
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\NestedSubResourceRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class FlatProperties
{
    public function __construct(private BillomatClient $billomat)
    {
    }

    public function run(BillomatClient $billomat): void
    {
        $this->billomat->invoices->comments->listByInvoice(1);
        $billomat->creditNotes->payments->list();
        $billomat->deliveryNotes->items->get(2);
        $billomat->recurrings->emailReceivers->listByRecurring(3);
        $billomat->reminders->items->listByReminder(4);
        $billomat->incomings->propertyValues->list();
        $billomat->users->properties->list();
        $tags = $billomat->clients->tags;
        $levels = $billomat->reminderTexts->list();
    }
}
?>
