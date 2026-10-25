<?php

declare(strict_types=1);

namespace Justpilot\Billomat;

use Http\Discovery\Psr18ClientDiscovery;
use Justpilot\Billomat\Api\AccountApi;
use Justpilot\Billomat\Api\ActivitiesApi;
use Justpilot\Billomat\Api\ArticlesApi;
use Justpilot\Billomat\Api\ClientsApi;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\ContactsApi;
use Justpilot\Billomat\Api\CountriesApi;
use Justpilot\Billomat\Api\CountryTaxesApi;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\CurrenciesApi;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\EmailTemplatesApi;
use Justpilot\Billomat\Api\FreeTextsApi;
use Justpilot\Billomat\Api\InboxDocumentsApi;
use Justpilot\Billomat\Api\IncomingCategoriesApi;
use Justpilot\Billomat\Api\IncomingsApi;
use Justpilot\Billomat\Api\InvoicesApi;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\RecurringsApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Api\ReminderTextsApi;
use Justpilot\Billomat\Api\RolesApi;
use Justpilot\Billomat\Api\SearchApi;
use Justpilot\Billomat\Api\SettingsApi;
use Justpilot\Billomat\Api\SuppliersApi;
use Justpilot\Billomat\Api\TaxesApi;
use Justpilot\Billomat\Api\TemplatesApi;
use Justpilot\Billomat\Api\UnitsApi;
use Justpilot\Billomat\Api\UsersApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

final readonly class BillomatClient
{
    private BillomatHttpClient $http;

    public SettingsApi $settings;

    // Dokumente; Positionen, Zahlungen, Kommentare und Schlagworte hängen an der jeweiligen Ressource,
    // z. B. `$billomat->invoices->comments`.
    public InvoicesApi $invoices;
    public RecurringsApi $recurrings;
    public OffersApi $offers;
    public ConfirmationsApi $confirmations;
    public DeliveryNotesApi $deliveryNotes;
    public CreditNotesApi $creditNotes;
    public RemindersApi $reminders;
    public LettersApi $letters;
    public IncomingsApi $incomings;
    public InboxDocumentsApi $inboxDocuments;

    // Stammdaten; Schlagworte und Attribute hängen an der jeweiligen Ressource, z. B. `$billomat->clients->tags`.
    public ClientsApi $clients;
    public ContactsApi $contacts;
    public ArticlesApi $articles;
    public SuppliersApi $suppliers;

    public EmailTemplatesApi $emailTemplates;
    public FreeTextsApi $freeTexts;
    public ReminderTextsApi $reminderTexts;
    public UnitsApi $units;
    public UsersApi $users;
    public CountriesApi $countries;
    public CurrenciesApi $currencies;
    public CountryTaxesApi $countryTaxes;
    public IncomingCategoriesApi $incomingCategories;
    public RolesApi $roles;
    public TemplatesApi $templates;
    public TaxesApi $taxes;

    public AccountApi $account;
    public ActivitiesApi $activities;
    public SearchApi $search;

    /**
     * @param ClientInterface|null         $httpClient     beliebiger PSR-18-Client; ohne Angabe sucht das SDK einen per php-http/discovery
     * @param RequestFactoryInterface|null $requestFactory PSR-17-Factory; ohne Angabe per php-http/discovery
     * @param StreamFactoryInterface|null  $streamFactory  PSR-17-Factory; ohne Angabe per php-http/discovery
     */
    public function __construct(
        BillomatConfig $config,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
    ) {
        $this->http = new BillomatHttpClient(
            $httpClient ?? $this->discoverHttpClient($config),
            $config,
            $requestFactory,
            $streamFactory,
        );

        // APIs
        $this->settings = new SettingsApi($this->http);
        $this->clients = new ClientsApi($this->http);
        $this->invoices = new InvoicesApi($this->http);
        $this->recurrings = new RecurringsApi($this->http);
        $this->offers = new OffersApi($this->http);
        $this->confirmations = new ConfirmationsApi($this->http);
        $this->deliveryNotes = new DeliveryNotesApi($this->http);
        $this->creditNotes = new CreditNotesApi($this->http);
        $this->reminders = new RemindersApi($this->http);
        $this->letters = new LettersApi($this->http);
        $this->articles = new ArticlesApi($this->http);
        $this->suppliers = new SuppliersApi($this->http);
        $this->contacts = new ContactsApi($this->http);
        $this->incomings = new IncomingsApi($this->http);
        $this->inboxDocuments = new InboxDocumentsApi($this->http);
        $this->emailTemplates = new EmailTemplatesApi($this->http);
        $this->freeTexts = new FreeTextsApi($this->http);
        $this->reminderTexts = new ReminderTextsApi($this->http);
        $this->units = new UnitsApi($this->http);
        $this->users = new UsersApi($this->http);
        $this->countries = new CountriesApi($this->http);
        $this->currencies = new CurrenciesApi($this->http);
        $this->countryTaxes = new CountryTaxesApi($this->http);
        $this->incomingCategories = new IncomingCategoriesApi($this->http);
        $this->roles = new RolesApi($this->http);
        $this->taxes = new TaxesApi($this->http);
        $this->templates = new TemplatesApi($this->http);

        $this->account = new AccountApi($this->http);
        $this->activities = new ActivitiesApi($this->http);
        $this->search = new SearchApi($this->http);
    }

    public static function create(
        string $billomatId,
        string $apiKey,
        ?string $appId = null,
        ?string $appSecret = null,
        float $timeout = 10.0,
        ?ClientInterface $httpClient = null,
        RetryPolicy $retry = new RetryPolicy(),
        ?string $language = 'de-de',
    ): self {
        $config = new BillomatConfig(
            billomatId: $billomatId,
            apiKey: $apiKey,
            appId: $appId,
            appSecret: $appSecret,
            timeout: $timeout,
            retry: $retry,
            language: $language,
        );

        return new self($config, $httpClient);
    }

    public function getHttpClient(): BillomatHttpClient
    {
        return $this->http;
    }

    /**
     * Sucht einen installierten PSR-18-Client.
     *
     * Symfony HttpClient wird bevorzugt, weil sich dort der Timeout aus der Config setzen lässt;
     * andere Clients (Guzzle, …) laufen mit ihrer eigenen Timeout-Einstellung.
     */
    private function discoverHttpClient(BillomatConfig $config): ClientInterface
    {
        if (class_exists(Psr18Client::class)) {
            return new Psr18Client(HttpClient::create(['timeout' => $config->timeout]));
        }

        return Psr18ClientDiscovery::find();
    }
}
