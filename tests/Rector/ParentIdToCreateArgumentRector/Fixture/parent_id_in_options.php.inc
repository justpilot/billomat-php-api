<?php

namespace Justpilot\Billomat\Tests\Rector\ParentIdToCreateArgumentRector\Fixture;

use Justpilot\Billomat\Api\ClientPropertyValueCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

final class ParentIdInOptions
{
    public function run(BillomatClient $billomat, int $id, array $args): void
    {
        $billomat->invoices->payments->create(new InvoicePaymentCreateOptions(invoiceId: $id, amount: 1.0));
        $billomat->creditNotes->payments->create(new CreditNotePaymentCreateOptions(amount: 1.0, creditNoteId: $id));
        $billomat->invoices->comments->create(new InvoiceCommentCreateOptions($id, 'Text'));
        $billomat->offers->tags->create(options: new OfferTagCreateOptions(offerId: 7, name: 'x'));
        $billomat->clients->propertyValues->create(new ClientPropertyValueCreateOptions(clientId: $id, clientPropertyId: 2, value: 'a'));
        $billomat->recurrings->emailReceivers->create(new RecurringEmailReceiverCreateOptions($id, RecurringEmailReceiverType::CC, 'a@example.com'));
        $billomat->recurrings->emailReceivers->create(options: new RecurringEmailReceiverCreateOptions(recurringId: $id, type: RecurringEmailReceiverType::BCC, address: 'b@example.com'));
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\ParentIdToCreateArgumentRector\Fixture;

use Justpilot\Billomat\Api\ClientPropertyValueCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;

final class ParentIdInOptions
{
    public function run(BillomatClient $billomat, int $id, array $args): void
    {
        $billomat->invoices->payments->create($id, new InvoicePaymentCreateOptions(amount: 1.0));
        $billomat->creditNotes->payments->create($id, new CreditNotePaymentCreateOptions(amount: 1.0));
        $billomat->invoices->comments->create($id, new InvoiceCommentCreateOptions('Text'));
        $billomat->offers->tags->create(parentId: 7, options: new OfferTagCreateOptions(name: 'x'));
        $billomat->clients->propertyValues->create($id, new ClientPropertyValueCreateOptions(clientPropertyId: 2, value: 'a'));
        $billomat->recurrings->emailReceivers->create($id, new RecurringEmailReceiverCreateOptions(RecurringEmailReceiverType::CC, 'a@example.com'));
        $billomat->recurrings->emailReceivers->create(recurringId: $id, options: new RecurringEmailReceiverCreateOptions(type: RecurringEmailReceiverType::BCC, address: 'b@example.com'));
    }
}
?>
