<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AccountApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Account;
use Justpilot\Billomat\Model\AccountQuota;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(AccountApi::class)]
#[CoversClass(Account::class)]
#[CoversClass(AccountQuota::class)]
#[CoversClass(Client::class)]
final class AccountApiTest extends TestCase
{
    #[Test]
    public function itFetchesOwnAccountWithPlanAndQuotas(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;

            $body = json_encode([
                'client' => [
                    'id' => 12345,
                    'name' => 'Beispiel GmbH',
                    'email' => 'info@example.com',
                    'country_code' => 'DE',
                    'plan' => 'XL',
                    'quotas' => [
                        'quota' => [
                            ['entity' => 'documents', 'available' => 2500, 'used' => 0],
                            ['entity' => 'clients', 'available' => 5000, 'used' => 4],
                            ['entity' => 'articles', 'available' => 20000, 'used' => 1],
                            ['entity' => 'storage', 'available' => -1, 'used' => 8_740_060],
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new AccountApi($http);

        $account = $api->get();

        self::assertSame('GET', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/clients/myself', $captured['url']);

        self::assertSame('XL', $account->plan);
        self::assertSame(12345, $account->client->id);
        self::assertSame('Beispiel GmbH', $account->client->name);

        self::assertCount(4, $account->quotas);

        $storage = $account->quota('storage');
        self::assertNotNull($storage);
        self::assertSame(-1, $storage->available);
        self::assertSame(8_740_060, $storage->used);
        self::assertTrue($storage->isUnlimited());

        $documents = $account->quota('documents');
        self::assertNotNull($documents);
        self::assertSame(2500, $documents->available);
        self::assertSame(0, $documents->used);
        self::assertFalse($documents->isUnlimited());
    }

    #[Test]
    public function itHandlesAccountWithoutPlanOrQuotas(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode([
                'client' => [
                    'id' => 1,
                    'name' => 'Free Account',
                ],
            ], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new AccountApi($http);

        $account = $api->get();

        self::assertNull($account->plan);
        self::assertSame([], $account->quotas);
        self::assertNull($account->quota('documents'));
    }

    #[Test]
    public function itAcceptsQuotaAsSingleObject(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode([
                'client' => [
                    'id' => 1,
                    'name' => 'Solo',
                    'plan' => 'S',
                    'quotas' => [
                        'quota' => ['entity' => 'documents', 'available' => 100, 'used' => 12],
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $account = new AccountApi($http)->get();

        self::assertCount(1, $account->quotas);
        self::assertSame('documents', $account->quotas[0]->entity);
    }
}
