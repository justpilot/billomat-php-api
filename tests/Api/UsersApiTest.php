<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\UsersApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ListCaster;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\User;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(UsersApi::class)]
#[CoversClass(User::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(ListCaster::class)]
final class UsersApiTest extends TestCase
{
    #[Test]
    public function itListsUsers(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'users' => [
                    'user' => [
                        ['id' => 1, 'email' => 'admin@example.com', 'first_name' => 'Max', 'last_name' => 'Mustermann'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new UsersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $users = $api->list();
        self::assertCount(1, $users);
        self::assertSame('admin@example.com', $users[0]->email);
    }

    #[Test]
    public function itGetsMyself(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'user' => ['id' => 42, 'email' => 'me@example.com'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new UsersApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $user = $api->getMyself();
        self::assertSame(42, $user->id);
        self::assertSame('https://mycompany.billomat.net/api/users/myself', $captured->url);
    }

    #[Test]
    public function getMyselfThrowsInsteadOfReturningNull(): void
    {
        $api = new UsersApi(new BillomatHttpClient(
            new Psr18Client(new MockHttpClient(new MockResponse('', ['http_code' => 404]))),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(NotFoundException::class);

        (void) $api->getMyself();
    }

    #[Test]
    public function getMyselfRejectsAnUnexpectedEnvelope(): void
    {
        $api = new UsersApi(new BillomatHttpClient(
            new Psr18Client(new MockHttpClient(new MockResponse('{"client":{"id":"1"}}'))),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('Unexpected response from Billomat when fetching own user via /users/myself.');

        (void) $api->getMyself();
    }
}
