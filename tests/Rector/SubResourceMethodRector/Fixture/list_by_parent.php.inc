<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class ListByParent
{
    public function run(BillomatClient $billomat, array $options): void
    {
        $billomat->invoiceComments->listByInvoice(1, ['payment']);
        $billomat->offerTags->listByOffer(offerId: 2);
        $billomat->creditNoteItems->listByCreditNote(3, ['per_page' => 50]);
        $billomat->deliveryNoteItems->create(deliveryNoteId: 4, options: $options);
        $billomat->confirmationItems->create(5, $options);
        $billomat->reminderItems->listByReminder(reminderId: 6);
        $billomat->recurringEmailReceivers->listByRecurring(recurringId: 7);
        $billomat->invoiceComments->get(id: 8);
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\BillomatClient;

final class ListByParent
{
    public function run(BillomatClient $billomat, array $options): void
    {
        $billomat->invoiceComments->list(1, ['payment']);
        $billomat->offerTags->list(parentId: 2);
        $billomat->creditNoteItems->list(3, ['per_page' => 50]);
        $billomat->deliveryNoteItems->create(parentId: 4, options: $options);
        $billomat->confirmationItems->create(5, $options);
        $billomat->reminderItems->list(reminderId: 6);
        $billomat->recurringEmailReceivers->list(recurringId: 7);
        $billomat->invoiceComments->get(id: 8);
    }
}
?>
