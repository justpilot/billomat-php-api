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
use Psr\EventDispatcher\EventDispatcherInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpClient\HttpClient;
use Symfony\Component\HttpClient\Psr18Client;

final class BillomatClient
{
    private readonly BillomatHttpClient $http;

    public private(set) SettingsApi $settings {
        get => $this->settings ??= new SettingsApi($this->http);
    }

    // Dokumente; Positionen, Zahlungen, Kommentare und Schlagworte hängen an der jeweiligen Ressource,
    // z. B. `$billomat->invoices->comments`.
    public private(set) InvoicesApi $invoices {
        get => $this->invoices ??= new InvoicesApi($this->http);
    }
    public private(set) RecurringsApi $recurrings {
        get => $this->recurrings ??= new RecurringsApi($this->http);
    }
    public private(set) OffersApi $offers {
        get => $this->offers ??= new OffersApi($this->http);
    }
    public private(set) ConfirmationsApi $confirmations {
        get => $this->confirmations ??= new ConfirmationsApi($this->http);
    }
    public private(set) DeliveryNotesApi $deliveryNotes {
        get => $this->deliveryNotes ??= new DeliveryNotesApi($this->http);
    }
    public private(set) CreditNotesApi $creditNotes {
        get => $this->creditNotes ??= new CreditNotesApi($this->http);
    }
    public private(set) RemindersApi $reminders {
        get => $this->reminders ??= new RemindersApi($this->http);
    }
    public private(set) LettersApi $letters {
        get => $this->letters ??= new LettersApi($this->http);
    }
    public private(set) IncomingsApi $incomings {
        get => $this->incomings ??= new IncomingsApi($this->http);
    }
    public private(set) InboxDocumentsApi $inboxDocuments {
        get => $this->inboxDocuments ??= new InboxDocumentsApi($this->http);
    }

    // Stammdaten; Schlagworte und Attribute hängen an der jeweiligen Ressource, z. B. `$billomat->clients->tags`.
    public private(set) ClientsApi $clients {
        get => $this->clients ??= new ClientsApi($this->http);
    }
    public private(set) ContactsApi $contacts {
        get => $this->contacts ??= new ContactsApi($this->http);
    }
    public private(set) ArticlesApi $articles {
        get => $this->articles ??= new ArticlesApi($this->http);
    }
    public private(set) SuppliersApi $suppliers {
        get => $this->suppliers ??= new SuppliersApi($this->http);
    }

    public private(set) EmailTemplatesApi $emailTemplates {
        get => $this->emailTemplates ??= new EmailTemplatesApi($this->http);
    }
    public private(set) FreeTextsApi $freeTexts {
        get => $this->freeTexts ??= new FreeTextsApi($this->http);
    }
    public private(set) ReminderTextsApi $reminderTexts {
        get => $this->reminderTexts ??= new ReminderTextsApi($this->http);
    }
    public private(set) UnitsApi $units {
        get => $this->units ??= new UnitsApi($this->http);
    }
    public private(set) UsersApi $users {
        get => $this->users ??= new UsersApi($this->http);
    }
    public private(set) CountriesApi $countries {
        get => $this->countries ??= new CountriesApi($this->http);
    }
    public private(set) CurrenciesApi $currencies {
        get => $this->currencies ??= new CurrenciesApi($this->http);
    }
    public private(set) CountryTaxesApi $countryTaxes {
        get => $this->countryTaxes ??= new CountryTaxesApi($this->http);
    }
    public private(set) IncomingCategoriesApi $incomingCategories {
        get => $this->incomingCategories ??= new IncomingCategoriesApi($this->http);
    }
    public private(set) RolesApi $roles {
        get => $this->roles ??= new RolesApi($this->http);
    }
    public private(set) TemplatesApi $templates {
        get => $this->templates ??= new TemplatesApi($this->http);
    }
    public private(set) TaxesApi $taxes {
        get => $this->taxes ??= new TaxesApi($this->http);
    }

    public private(set) AccountApi $account {
        get => $this->account ??= new AccountApi($this->http);
    }
    public private(set) ActivitiesApi $activities {
        get => $this->activities ??= new ActivitiesApi($this->http);
    }
    public private(set) SearchApi $search {
        get => $this->search ??= new SearchApi($this->http);
    }

    /**
     * @param ClientInterface|null          $httpClient     beliebiger PSR-18-Client; ohne Angabe sucht das SDK einen per php-http/discovery
     * @param RequestFactoryInterface|null  $requestFactory PSR-17-Factory; ohne Angabe per php-http/discovery
     * @param StreamFactoryInterface|null   $streamFactory  PSR-17-Factory; ohne Angabe per php-http/discovery
     * @param LoggerInterface|null          $logger         PSR-3-Logger für Antworten, Retries und Fehler (ohne Header und Bodies)
     * @param EventDispatcherInterface|null $events         PSR-14-Dispatcher für die Events aus `Justpilot\Billomat\Event`
     */
    public function __construct(
        BillomatConfig $config,
        ?ClientInterface $httpClient = null,
        ?RequestFactoryInterface $requestFactory = null,
        ?StreamFactoryInterface $streamFactory = null,
        ?LoggerInterface $logger = null,
        ?EventDispatcherInterface $events = null,
    ) {
        $this->http = new BillomatHttpClient(
            $httpClient ?? $this->discoverHttpClient($config),
            $config,
            $requestFactory,
            $streamFactory,
            logger: $logger,
            events: $events,
        );
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
        ?LoggerInterface $logger = null,
        ?EventDispatcherInterface $events = null,
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

        return new self($config, $httpClient, logger: $logger, events: $events);
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
