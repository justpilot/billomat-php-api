<?php

declare(strict_types=1);

namespace Justpilot\Billomat;

use Justpilot\Billomat\Api\ArticlePropertyValuesApi;
use Justpilot\Billomat\Api\ArticlesApi;
use Justpilot\Billomat\Api\ArticleTagsApi;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\ConfirmationCommentsApi;
use Justpilot\Billomat\Api\ConfirmationItemsApi;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\ConfirmationTagsApi;
use Justpilot\Billomat\Api\CreditNoteCommentsApi;
use Justpilot\Billomat\Api\CreditNoteItemsApi;
use Justpilot\Billomat\Api\CreditNotePaymentsApi;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\CreditNoteTagsApi;
use Justpilot\Billomat\Api\DeliveryNoteCommentsApi;
use Justpilot\Billomat\Api\DeliveryNoteItemsApi;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\DeliveryNoteTagsApi;
use Justpilot\Billomat\Api\InvoiceCommentsApi;
use Justpilot\Billomat\Api\InvoiceItemsApi;
use Justpilot\Billomat\Api\InvoicePaymentsApi;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\InvoiceTagsApi;
use Justpilot\Billomat\Api\LetterCommentsApi;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Api\LetterTagsApi;
use Justpilot\Billomat\Api\OfferCommentsApi;
use Justpilot\Billomat\Api\OfferItemsApi;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\OfferTagsApi;
use Justpilot\Billomat\Api\RecurringEmailReceiversApi;
use Justpilot\Billomat\Api\RecurringItemsApi;
use Justpilot\Billomat\Api\RecurringsApi;
use Justpilot\Billomat\Api\RecurringTagsApi;
use Justpilot\Billomat\Api\ReminderItemsApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Api\ReminderTagsApi;
use Justpilot\Billomat\Api\SettingsApi;
use Justpilot\Billomat\Api\TaxesApi;
use Justpilot\Billomat\Api\TemplatesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Contracts\HttpClient\HttpClientInterface;

final readonly class BillomatClient
{
    private BillomatHttpClient $http;

    public SettingsApi $settings;
    public ClientsApi $clients;
    public InvoicesApi $invoices;
    public InvoiceItemsApi $invoiceItems;
    public InvoicePaymentsApi $invoicePayments;
    public InvoiceCommentsApi $invoiceComments;
    public InvoiceTagsApi $invoiceTags;

    public RecurringsApi $recurrings;
    public RecurringItemsApi $recurringItems;
    public RecurringTagsApi $recurringTags;
    public RecurringEmailReceiversApi $recurringEmailReceivers;

    public OffersApi $offers;
    public OfferItemsApi $offerItems;
    public OfferCommentsApi $offerComments;
    public OfferTagsApi $offerTags;

    public ConfirmationsApi $confirmations;
    public ConfirmationItemsApi $confirmationItems;
    public ConfirmationCommentsApi $confirmationComments;
    public ConfirmationTagsApi $confirmationTags;

    public DeliveryNotesApi $deliveryNotes;
    public DeliveryNoteItemsApi $deliveryNoteItems;
    public DeliveryNoteCommentsApi $deliveryNoteComments;
    public DeliveryNoteTagsApi $deliveryNoteTags;

    public CreditNotesApi $creditNotes;
    public CreditNoteItemsApi $creditNoteItems;
    public CreditNoteCommentsApi $creditNoteComments;
    public CreditNoteTagsApi $creditNoteTags;
    public CreditNotePaymentsApi $creditNotePayments;

    public RemindersApi $reminders;
    public ReminderItemsApi $reminderItems;
    public ReminderTagsApi $reminderTags;

    public LettersApi $letters;
    public LetterCommentsApi $letterComments;
    public LetterTagsApi $letterTags;

    public ArticlesApi $articles;
    public ArticleTagsApi $articleTags;
    public ArticlePropertyValuesApi $articlePropertyValues;

    public TemplatesApi $templates;
    public TaxesApi $taxes;

    public function __construct(
        BillomatConfig $config,
        ?HttpClientInterface $httpClient = null,
    ) {
        $httpClient ??= HttpClient::create();

        $this->http = new BillomatHttpClient($httpClient, $config);

        // APIs
        $this->settings = new SettingsApi($this->http);
        $this->clients = new ClientsApi($this->http);
        $this->invoices = new InvoicesApi($this->http);
        $this->invoiceItems = new InvoiceItemsApi($this->http);
        $this->invoicePayments = new InvoicePaymentsApi($this->http);
        $this->invoiceComments = new InvoiceCommentsApi($this->http);
        $this->invoiceTags = new InvoiceTagsApi($this->http);
        $this->recurrings = new RecurringsApi($this->http);
        $this->recurringItems = new RecurringItemsApi($this->http);
        $this->recurringTags = new RecurringTagsApi($this->http);
        $this->recurringEmailReceivers = new RecurringEmailReceiversApi($this->http);
        $this->offers = new OffersApi($this->http);
        $this->offerItems = new OfferItemsApi($this->http);
        $this->offerComments = new OfferCommentsApi($this->http);
        $this->offerTags = new OfferTagsApi($this->http);
        $this->confirmations = new ConfirmationsApi($this->http);
        $this->confirmationItems = new ConfirmationItemsApi($this->http);
        $this->confirmationComments = new ConfirmationCommentsApi($this->http);
        $this->confirmationTags = new ConfirmationTagsApi($this->http);
        $this->deliveryNotes = new DeliveryNotesApi($this->http);
        $this->deliveryNoteItems = new DeliveryNoteItemsApi($this->http);
        $this->deliveryNoteComments = new DeliveryNoteCommentsApi($this->http);
        $this->deliveryNoteTags = new DeliveryNoteTagsApi($this->http);
        $this->creditNotes = new CreditNotesApi($this->http);
        $this->creditNoteItems = new CreditNoteItemsApi($this->http);
        $this->creditNoteComments = new CreditNoteCommentsApi($this->http);
        $this->creditNoteTags = new CreditNoteTagsApi($this->http);
        $this->creditNotePayments = new CreditNotePaymentsApi($this->http);
        $this->reminders = new RemindersApi($this->http);
        $this->reminderItems = new ReminderItemsApi($this->http);
        $this->reminderTags = new ReminderTagsApi($this->http);
        $this->letters = new LettersApi($this->http);
        $this->letterComments = new LetterCommentsApi($this->http);
        $this->letterTags = new LetterTagsApi($this->http);
        $this->articles = new ArticlesApi($this->http);
        $this->articleTags = new ArticleTagsApi($this->http);
        $this->articlePropertyValues = new ArticlePropertyValuesApi($this->http);
        $this->taxes = new TaxesApi($this->http);
        $this->templates = new TemplatesApi($this->http);
    }

    public static function create(
        string $billomatId,
        string $apiKey,
        ?string $appId = null,
        ?string $appSecret = null,
        float $timeout = 10.0,
        ?HttpClientInterface $httpClient = null,
    ): self {
        $config = new BillomatConfig(
            billomatId: $billomatId,
            apiKey: $apiKey,
            appId: $appId,
            appSecret: $appSecret,
            timeout: $timeout,
        );

        return new self($config, $httpClient);
    }

    public function getHttpClient(): BillomatHttpClient
    {
        return $this->http;
    }
}
