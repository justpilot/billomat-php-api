<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ContactCreateOptions;
use Justpilot\Billomat\Api\ContactsApi;
use Justpilot\Billomat\Api\ContactUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Contact;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ContactsApi::class)]
#[CoversClass(ContactCreateOptions::class)]
#[CoversClass(ContactUpdateOptions::class)]
#[CoversClass(Contact::class)]
final class ContactsApiTest extends TestCase
{
    #[Test]
    public function itListsContactsByClient(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured['url'] = $url;

            $body = json_encode([
                'contacts' => [
                    'contact' => [
                        ['id' => 1, 'client_id' => 42, 'first_name' => 'Anna', 'last_name' => 'Beispiel'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ContactsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $contacts = $api->listByClient(42);

        self::assertCount(1, $contacts);
        self::assertSame('Anna', $contacts[0]->firstName);
        self::assertStringContainsString('client_id=42', $captured['url']);
    }

    #[Test]
    public function itGetsSingleContact(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'contact' => [
                    'id' => 99,
                    'client_id' => 42,
                    'label' => 'Buchhaltung',
                    'email' => 'b@example.com',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ContactsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $contact = $api->get(99);

        self::assertInstanceOf(Contact::class, $contact);
        self::assertSame('Buchhaltung', $contact->label);
        self::assertSame('b@example.com', $contact->email);
    }

    #[Test]
    public function itCreatesContact(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'contact' => ['id' => 777, 'client_id' => 42, 'first_name' => 'Anna'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $api = new ContactsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ContactCreateOptions(clientId: 42);
        $opts->firstName = 'Anna';
        $opts->lastName = 'Beispiel';
        $opts->email = 'anna@example.com';

        $created = $api->create($opts);

        self::assertSame(777, $created->id);

        $payload = $captured['options']['json'] ?? null;
        if (null === $payload && isset($captured['options']['body']) && \is_string($captured['options']['body'])) {
            $payload = json_decode($captured['options']['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertSame(42, $payload['contact']['client_id']);
        self::assertSame('Anna', $payload['contact']['first_name']);
        self::assertSame('anna@example.com', $payload['contact']['email']);
    }

    #[Test]
    public function itUpdatesContact(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            $body = json_encode([
                'contact' => ['id' => 777, 'client_id' => 42, 'label' => 'Geändert'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ContactsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $opts = new ContactUpdateOptions();
        $opts->label = 'Geändert';

        $updated = $api->update(777, $opts);

        self::assertSame('Geändert', $updated->label);
        self::assertSame('PUT', $captured['method']);
    }

    #[Test]
    public function itDeletesContact(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url];

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ContactsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        self::assertTrue($api->delete(777));
        self::assertSame('DELETE', $captured['method']);
    }
}
