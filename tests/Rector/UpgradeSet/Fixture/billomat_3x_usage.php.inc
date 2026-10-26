<?php

namespace Justpilot\Billomat\Tests\Rector\UpgradeSet\Fixture;

use Justpilot\Billomat\Api\ArticleTagsApi;
use Justpilot\Billomat\Api\InvoiceCommentsApi;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceEmailOptions;
use Justpilot\Billomat\Api\InvoiceMailOptions;
use Justpilot\Billomat\Api\InvoicePaymentsApi;
use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\Api\OfferCommentsApi;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Model\DunningLevel;
use Justpilot\Billomat\Model\Enum\ArticleType;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Enum\InvoicePaymentType;
use Justpilot\Billomat\Model\Enum\InvoicePdfType;
use Justpilot\Billomat\Model\Enum\OfferStatus;

final class Billomat3xUsage
{
    public function __construct(
        private BillomatClient $billomat,
        private InvoicePaymentsApi $payments,
        private OfferCommentsApi $offerComments,
    ) {
    }

    public function run(int $id, BillomatHttpClientInterface $http, InvoiceCreateOptions $options, InvoiceEmailOptions $email): void
    {
        $this->billomat->invoiceComments->listByInvoice($id);
        $this->billomat->invoiceItems->create(invoiceId: $id, options: $options);
        $this->billomat->invoicePayments->list(['invoice_id' => $id, 'order_by' => 'date+DESC']);
        $this->billomat->clientPropertyValues->iterateAll(['client_id' => $id], 50);
        $this->billomat->reminderItems->listByReminder($id);
        $this->billomat->contacts->listByClient($id);
        $this->billomat->invoices->email($id, $email);
        $this->payments->list(['invoice_id' => $id]);
        $this->offerComments->listByOffer($id);

        /** @var list<DunningLevel> $levels */
        $levels = $this->billomat->dunningLevels->list();

        $comments = new InvoiceCommentsApi($http);
        $tags = new ArticleTagsApi($http);
        $items = $options->getItems();

        $pdf = $this->billomat->offers->pdf($id, InvoicePdfType::PRINT);
        $binary = $this->billomat->invoices->pdf($id, InvoicePdfType::SIGNED, rawPdf: true);
        $tag = $this->billomat->invoiceTags->create(new InvoiceTagCreateOptions(invoiceId: $id, name: 'VIP'));
        $won = $this->billomat->offers->get($id)?->status === OfferStatus::ACCEPTED;
        $lost = OfferStatus::REJECTED;
        $types = [ArticleType::SERVICE, InvoiceItemType::PRODUCT, InvoicePaymentType::PAYPAL];
        $this->billomat->invoices->mail($id, new InvoiceMailOptions(color: true));
    }
}
?>
-----
<?php

namespace Justpilot\Billomat\Tests\Rector\UpgradeSet\Fixture;

use Justpilot\Billomat\Api\PaymentsApi;
use Justpilot\Billomat\Api\CommentsApi;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\ArticlesApi;
use Justpilot\Billomat\Model\Enum\PdfType;
use Justpilot\Billomat\Model\Enum\ItemType;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Api\MailOptions;
use Justpilot\Billomat\Model\ReminderText;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Http\BillomatHttpClientInterface;
use Justpilot\Billomat\Model\Enum\OfferStatus;

final class Billomat3xUsage
{
    public function __construct(
        private BillomatClient $billomat,
        private PaymentsApi $payments,
        private CommentsApi $offerComments,
    ) {
    }

    public function run(int $id, BillomatHttpClientInterface $http, InvoiceCreateOptions $options, EmailOptions $email): void
    {
        $this->billomat->invoices->comments->list($id);
        $this->billomat->invoices->items->create(parentId: $id, options: $options);
        $this->billomat->invoices->payments->list(filters: ['invoice_id' => $id, 'order_by' => 'date+DESC']);
        $this->billomat->clients->propertyValues->iterateAll(filters: ['client_id' => $id], pageSize: 50);
        $this->billomat->reminders->items->list($id);
        $this->billomat->contacts->list($id);
        $this->billomat->invoices->email($id, $email);
        $this->payments->list(filters: ['invoice_id' => $id]);
        $this->offerComments->list($id);

        /** @var list<ReminderText> $levels */
        $levels = $this->billomat->reminderTexts->list();

        $comments = (new InvoicesApi($http))->comments;
        $tags = (new ArticlesApi($http))->tags;
        $items = $options->items;

        $pdf = $this->billomat->offers->pdf($id, PdfType::PRINT);
        $binary = $this->billomat->invoices->pdfContent($id, PdfType::SIGNED);
        $tag = $this->billomat->invoices->tags->create($id, new InvoiceTagCreateOptions(name: 'VIP'));
        $won = $this->billomat->offers->get($id)?->status === OfferStatus::WON;
        $lost = OfferStatus::LOST;
        $types = [ItemType::SERVICE, ItemType::PRODUCT, PaymentType::PAYPAL];
        $this->billomat->invoices->mail($id, new MailOptions(color: true));
    }
}
?>
