<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiversApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use Justpilot\Billomat\Model\RecurringEmailReceiver;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(RecurringEmailReceiversApi::class)]
#[CoversClass(RecurringEmailReceiver::class)]
#[CoversClass(RecurringEmailReceiverCreateOptions::class)]
#[CoversClass(RecurringEmailReceiverType::class)]
final class RecurringEmailReceiversApiTest extends TestCase
{
    #[Test]
    public function listByRecurringReturnsTypedReceivers(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['url' => $url];

            $body = json_encode([
                'recurring-email-receivers' => [
                    'recurring-email-receiver' => [
                        ['id' => 1, 'recurring_id' => 42, 'type' => 'to', 'address' => 'a@example.com'],
                        ['id' => 2, 'recurring_id' => 42, 'type' => 'cc', 'address' => 'b@example.com'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringEmailReceiversApi($http);

        $receivers = $api->listByRecurring(42);

        self::assertCount(2, $receivers);
        self::assertContainsOnlyInstancesOf(RecurringEmailReceiver::class, $receivers);
        self::assertSame(RecurringEmailReceiverType::TO, $receivers[0]->type);
        self::assertSame(RecurringEmailReceiverType::CC, $receivers[1]->type);

        $parts = parse_url((string) $captured['url']);
        $query = [];
        parse_str($parts['query'] ?? '', $query);
        self::assertSame('42', $query['recurring_id'] ?? null);
    }

    #[Test]
    public function createPostsReceiverAndHydratesResponse(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function ($method, $url, $options) use (&$captured): MockResponse {
            $captured = ['method' => $method, 'url' => $url, 'options' => $options];

            $body = json_encode([
                'recurring-email-receiver' => ['id' => 10, 'recurring_id' => 42, 'type' => 'bcc', 'address' => 'log@example.com'],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 201]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringEmailReceiversApi($http);

        $receiver = $api->create(new RecurringEmailReceiverCreateOptions(
            recurringId: 42,
            type: RecurringEmailReceiverType::BCC,
            address: 'log@example.com',
        ));

        self::assertSame(10, $receiver->id);
        self::assertSame(RecurringEmailReceiverType::BCC, $receiver->type);
        self::assertSame('POST', $captured['method']);

        $payload = $this->extractJsonPayload($captured['options']);
        self::assertIsArray($payload);
        self::assertSame('bcc', $payload['recurring-email-receiver']['type']);
    }

    #[Test]
    public function deleteAndGetNotFoundWork(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 404]),
            new MockResponse('', ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig(billomatId: 'mycompany', apiKey: 'k'));
        $api = new RecurringEmailReceiversApi($http);

        self::assertNull($api->get(999));
        self::assertTrue($api->delete(10));
    }

    /**
     * @param array<string,mixed> $options
     *
     * @return array<string,mixed>|null
     */
    private function extractJsonPayload(array $options): ?array
    {
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body']) && '' !== $options['body']) {
            $decoded = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);

            if (\is_array($decoded)) {
                return $decoded;
            }
        }

        return \is_array($payload) ? $payload : null;
    }
}
