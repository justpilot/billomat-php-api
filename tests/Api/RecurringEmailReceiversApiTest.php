<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\AbstractSubResourceApi;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiversApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use Justpilot\Billomat\Model\RecurringEmailReceiver;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(RecurringEmailReceiversApi::class)]
#[CoversClass(AbstractSubResourceApi::class)]
#[CoversClass(RecurringEmailReceiver::class)]
#[CoversClass(RecurringEmailReceiverCreateOptions::class)]
#[CoversClass(RecurringEmailReceiverType::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(HttpException::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
final class RecurringEmailReceiversApiTest extends TestCase
{
    #[Test]
    public function listByRecurringReturnsTypedReceivers(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->url = $url;

            $body = json_encode([
                'recurring-email-receivers' => [
                    'recurring-email-receiver' => [
                        ['id' => 1, 'recurring_id' => 42, 'type' => 'To', 'address' => 'a@example.com'],
                        ['id' => 2, 'recurring_id' => 42, 'type' => 'Cc', 'address' => 'b@example.com'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringEmailReceiversApi($http);

        $receivers = $api->list(42);

        self::assertCount(2, $receivers);
        self::assertContainsOnlyInstancesOf(RecurringEmailReceiver::class, $receivers);
        self::assertSame(RecurringEmailReceiverType::TO, $receivers[0]->type);
        self::assertSame(RecurringEmailReceiverType::CC, $receivers[1]->type);

        $parts = parse_url($captured->url);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame('42', $query['recurring_id'] ?? null);
    }

    #[Test]
    public function createPostsReceiverAndHydratesResponse(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            $body = json_encode([
                'recurring-email-receiver' => ['id' => 10, 'recurring_id' => 42, 'type' => 'Bcc', 'address' => 'log@example.com'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringEmailReceiversApi($http);

        $receiver = $api->create(new RecurringEmailReceiverCreateOptions(
            recurringId: 42,
            type: RecurringEmailReceiverType::BCC,
            address: 'log@example.com',
        ));

        self::assertSame(10, $receiver->id);
        self::assertSame(RecurringEmailReceiverType::BCC, $receiver->type);
        self::assertSame('POST', $captured->method);

        $payload = $captured->json();
        self::assertIsArray($payload);
        self::assertSame('Bcc', $captured->jsonAt('recurring-email-receiver', 'type'));
    }

    #[Test]
    public function deleteAndGetNotFoundWork(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
            new MockResponse('', ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringEmailReceiversApi($http);

        self::assertNull($api->get(999));
        $api->delete(10);
    }
}
