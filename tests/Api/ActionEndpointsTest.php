<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Closure;
use Justpilot\Billomat\Api\ConfirmationsApi;
use Justpilot\Billomat\Api\ContactsApi;
use Justpilot\Billomat\Api\CreditNotesApi;
use Justpilot\Billomat\Api\DeliveryNotesApi;
use Justpilot\Billomat\Api\LettersApi;
use Justpilot\Billomat\Api\MailOptions;
use Justpilot\Billomat\Api\OffersApi;
use Justpilot\Billomat\Api\RecurringEmailReceiversApi;
use Justpilot\Billomat\Api\RecurringEmailReceiverUpdateOptions;
use Justpilot\Billomat\Api\RecurringsApi;
use Justpilot\Billomat\Api\RemindersApi;
use Justpilot\Billomat\Api\UsersApi;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

/**
 * Aktionen, die vor 4.0.0-beta.1 dazugekommen sind: Pixelletter-Versand aller Dokumenttypen,
 * `recurrings->stop()`, `emailReceivers->update()` und die Avatare von Kontakten und Benutzern.
 */
#[CoversClass(OffersApi::class)]
#[CoversClass(ConfirmationsApi::class)]
#[CoversClass(CreditNotesApi::class)]
#[CoversClass(DeliveryNotesApi::class)]
#[CoversClass(LettersApi::class)]
#[CoversClass(RemindersApi::class)]
#[CoversClass(RecurringsApi::class)]
#[CoversClass(RecurringEmailReceiversApi::class)]
#[CoversClass(ContactsApi::class)]
#[CoversClass(UsersApi::class)]
#[UsesClass(BillomatClient::class)]
#[UsesNamespace('Justpilot\\Billomat\\Api')]
#[UsesNamespace('Justpilot\\Billomat\\Config')]
#[UsesNamespace('Justpilot\\Billomat\\Http')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
#[UsesNamespace('Justpilot\\Billomat\\Model')]
final class ActionEndpointsTest extends TestCase
{
    private CapturedRequest $request;

    protected function setUp(): void
    {
        $this->request = new CapturedRequest();
    }

    /**
     * @return iterable<string, array{string, Closure(BillomatClient, ?MailOptions): void}>
     */
    public static function mailers(): iterable
    {
        yield 'offers' => ['offers', static fn (BillomatClient $b, ?MailOptions $o) => $b->offers->mail(7, $o)];
        yield 'confirmations' => ['confirmations', static fn (BillomatClient $b, ?MailOptions $o) => $b->confirmations->mail(7, $o)];
        yield 'credit notes' => ['credit-notes', static fn (BillomatClient $b, ?MailOptions $o) => $b->creditNotes->mail(7, $o)];
        yield 'delivery notes' => ['delivery-notes', static fn (BillomatClient $b, ?MailOptions $o) => $b->deliveryNotes->mail(7, $o)];
        yield 'letters' => ['letters', static fn (BillomatClient $b, ?MailOptions $o) => $b->letters->mail(7, $o)];
        yield 'reminders' => ['reminders', static fn (BillomatClient $b, ?MailOptions $o) => $b->reminders->mail(7, $o)];
    }

    /**
     * @param Closure(BillomatClient, ?MailOptions): void $mail
     */
    #[Test]
    #[DataProvider('mailers')]
    public function mailSendsThePixelletterOptions(string $slug, Closure $mail): void
    {
        $mail($this->client('{}'), new MailOptions(color: true, duplex: false, paperWeight: 90));

        self::assertSame('POST', $this->request->method);
        self::assertSame("https://mycompany.billomat.net/api/{$slug}/7/mail", $this->request->url);
        self::assertSame(['mail' => ['color' => 1, 'duplex' => 0, 'paper_weight' => 90]], $this->request->json());
    }

    /**
     * @param Closure(BillomatClient, ?MailOptions): void $mail
     */
    #[Test]
    #[DataProvider('mailers')]
    public function mailWithoutOptionsSendsAnEmptyEnvelope(string $slug, Closure $mail): void
    {
        $mail($this->client('{}'), null);

        self::assertSame("https://mycompany.billomat.net/api/{$slug}/7/mail", $this->request->url);
        self::assertSame(['mail' => []], $this->request->json());
    }

    #[Test]
    public function stopPutsToTheStopAction(): void
    {
        $this->client('')->recurrings->stop(5);

        self::assertSame('PUT', $this->request->method);
        self::assertSame('https://mycompany.billomat.net/api/recurrings/5/stop', $this->request->url);
    }

    #[Test]
    public function emailReceiverUpdateSendsOnlyTheChangedFields(): void
    {
        $billomat = $this->client('{"recurring-email-receiver":{"id":"3","recurring_id":"5","type":"Cc","address":"neu@example.com"}}');

        $receiver = $billomat->recurrings->emailReceivers->update(3, new RecurringEmailReceiverUpdateOptions(address: 'neu@example.com'));

        self::assertSame('PUT', $this->request->method);
        self::assertSame('https://mycompany.billomat.net/api/recurring-email-receivers/3', $this->request->url);
        self::assertSame(['recurring-email-receiver' => ['address' => 'neu@example.com']], $this->request->json());
        self::assertSame('neu@example.com', $receiver->address);
        self::assertSame(RecurringEmailReceiverType::CC, $receiver->type);
    }

    /**
     * @return iterable<string, array{string, Closure(BillomatClient, ?int): string}>
     */
    public static function avatars(): iterable
    {
        yield 'contacts' => ['contacts', static fn (BillomatClient $b, ?int $size): string => $b->contacts->avatar(4, $size)];
        yield 'users' => ['users', static fn (BillomatClient $b, ?int $size): string => $b->users->avatar(4, $size)];
    }

    /**
     * @param Closure(BillomatClient, ?int): string $avatar
     */
    #[Test]
    #[DataProvider('avatars')]
    public function avatarReturnsTheRawImage(string $slug, Closure $avatar): void
    {
        self::assertSame("\x89PNG", $avatar($this->client("\x89PNG"), 64));
        self::assertSame('GET', $this->request->method);
        self::assertSame("https://mycompany.billomat.net/api/{$slug}/4/avatar?size=64", $this->request->url);

        self::assertSame("\x89PNG", $avatar($this->client("\x89PNG"), null));
        self::assertSame("https://mycompany.billomat.net/api/{$slug}/4/avatar", $this->request->url);
    }

    private function client(string $body): BillomatClient
    {
        $request = $this->request;
        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($request, $body): MockResponse {
            $request->record($method, $url, $options);

            return new MockResponse($body);
        });

        return new BillomatClient(new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'), new Psr18Client($mock));
    }
}
