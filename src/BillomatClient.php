<?php

declare(strict_types=1);

namespace Justpilot\Billomat;

use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\InvoiceCommentsApi;
use Justpilot\Billomat\Api\InvoiceItemsApi;
use Justpilot\Billomat\Api\InvoicePaymentsApi;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\InvoiceTagsApi;
use Justpilot\Billomat\Api\RecurringEmailReceiversApi;
use Justpilot\Billomat\Api\RecurringItemsApi;
use Justpilot\Billomat\Api\RecurringsApi;
use Justpilot\Billomat\Api\RecurringTagsApi;
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
