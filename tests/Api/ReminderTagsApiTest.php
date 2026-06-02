<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\ReminderTagCreateOptions;
use Justpilot\Billomat\Api\ReminderTagsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\ReminderTag;
use Justpilot\Billomat\Model\ReminderTagCloudEntry;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(ReminderTagsApi::class)]
#[CoversClass(ReminderTagCreateOptions::class)]
#[CoversClass(ReminderTag::class)]
#[CoversClass(ReminderTagCloudEntry::class)]
final class ReminderTagsApiTest extends TestCase
{
    #[Test]
    public function itListsAndCreatesTagsAndDeletes(): void
    {
        $captured = [];
        $callCount = 0;

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$captured, &$callCount): MockResponse {
            $captured[] = ['method' => $method, 'url' => $url];
            ++$callCount;

            if (str_contains($url, '/reminder-tags?reminder_id=42')) {
                $body = json_encode([
                    'reminder-tags' => [
                        'reminder-tag' => [['id' => 1, 'reminder_id' => 42, 'name' => 'eilig']],
                    ],
                ], JSON_THROW_ON_ERROR);

                return new MockResponse($body, ['http_code' => 200]);
            }

            if ('POST' === $method) {
                $body = json_encode([
                    'reminder-tag' => ['id' => 99, 'reminder_id' => 42, 'name' => 'neu'],
                ], JSON_THROW_ON_ERROR);

                return new MockResponse($body, ['http_code' => 201]);
            }

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new ReminderTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $tags = $api->listByReminder(42);
        self::assertCount(1, $tags);
        self::assertSame('eilig', $tags[0]->name);

        $tag = $api->create(new ReminderTagCreateOptions(reminderId: 42, name: 'neu'));
        self::assertSame(99, $tag->id);

        self::assertTrue($api->delete(99));
    }

    #[Test]
    public function itLoadsCloud(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'reminder-tags' => [
                    'tag' => [['id' => 1, 'name' => 'eilig', 'count' => 5]],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ReminderTagsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $cloud = $api->cloud();
        self::assertCount(1, $cloud);
        self::assertContainsOnlyInstancesOf(ReminderTagCloudEntry::class, $cloud);
    }
}
