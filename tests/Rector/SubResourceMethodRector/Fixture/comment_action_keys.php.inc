<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

const ACTION_KEYS = [InvoiceCommentActionKey::PAYMENT];

final class CommentActionKeys
{
    /**
     * @param list<InvoiceCommentActionKey>      $keys
     * @param list<InvoiceCommentActionKey>|null $maybeKeys
     */
    public function run(BillomatClient $billomat, array $keys, ?array $maybeKeys): void
    {
        $billomat->invoiceComments->listByInvoice(1, null);
        $billomat->invoiceComments->listByInvoice(2, [InvoiceCommentActionKey::PAYMENT]);
        $billomat->offerComments->listByOffer(3, $keys);
        $billomat->letterComments->listByLetter(4, $maybeKeys);
        $billomat->creditNoteComments->listByCreditNote(creditNoteId: 5, actionKeys: $keys);
        $billomat->invoiceComments->listByInvoice(...[6, $keys]);
        $billomat->invoiceComments->listByInvoice(7, ...[$keys]);
        $billomat->invoiceComments->listByInvoice(8, ACTION_KEYS);
        $billomat->invoiceComments->listByInvoice(actionKeys: null, invoiceId: 9);
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\SubResourceMethodRector\Fixture;

use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\Enum\InvoiceCommentActionKey;

const ACTION_KEYS = [InvoiceCommentActionKey::PAYMENT];

final class CommentActionKeys
{
    /**
     * @param list<InvoiceCommentActionKey>      $keys
     * @param list<InvoiceCommentActionKey>|null $maybeKeys
     */
    public function run(BillomatClient $billomat, array $keys, ?array $maybeKeys): void
    {
        $billomat->invoiceComments->list(1);
        $billomat->invoiceComments->list(2, new \Justpilot\Billomat\Api\CommentFilter(actionKeys: [InvoiceCommentActionKey::PAYMENT]));
        $billomat->offerComments->list(3, new \Justpilot\Billomat\Api\CommentFilter(actionKeys: $keys));
        $billomat->letterComments->list(4, new \Justpilot\Billomat\Api\CommentFilter(actionKeys: $maybeKeys ?? []));
        $billomat->creditNoteComments->list(parentId: 5, filters: new \Justpilot\Billomat\Api\CommentFilter(actionKeys: $keys));
        $billomat->invoiceComments->list(...[6, $keys]);
        $billomat->invoiceComments->list(7, ...[$keys]);
        $billomat->invoiceComments->list(8, new \Justpilot\Billomat\Api\CommentFilter(actionKeys: ACTION_KEYS));
        $billomat->invoiceComments->list(parentId: 9);
    }
}
?>
