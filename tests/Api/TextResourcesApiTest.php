<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\EmailTemplatesApi;
use Justpilot\Billomat\Api\FreeTextsApi;
use Justpilot\Billomat\Api\ReminderTextsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\EmailTemplate;
use Justpilot\Billomat\Model\FreeText;
use Justpilot\Billomat\Model\ReminderText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(EmailTemplatesApi::class)]
#[CoversClass(FreeTextsApi::class)]
#[CoversClass(ReminderTextsApi::class)]
#[CoversClass(EmailTemplate::class)]
#[CoversClass(FreeText::class)]
#[CoversClass(ReminderText::class)]
final class TextResourcesApiTest extends TestCase
{
    #[Test]
    public function itListsEmailTemplates(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'email-templates' => [
                    'email-template' => [
                        ['id' => 1, 'name' => 'Standard Rechnung', 'subject' => 'Ihre Rechnung'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new EmailTemplatesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $templates = $api->list();
        self::assertCount(1, $templates);
        self::assertSame('Standard Rechnung', $templates[0]->name);
    }

    #[Test]
    public function itListsFreeTexts(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'free-texts' => [
                    'free-text' => [
                        ['id' => 1, 'title' => 'Standard-Text', 'note' => 'Vielen Dank'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new FreeTextsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $texts = $api->list();
        self::assertCount(1, $texts);
        self::assertSame('Standard-Text', $texts[0]->title);
    }

    #[Test]
    public function itListsReminderTexts(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'reminder-texts' => [
                    'reminder-text' => [
                        ['id' => 1, 'name' => '1. Mahnung', 'due_days' => 14],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ReminderTextsApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $texts = $api->list();
        self::assertCount(1, $texts);
        self::assertSame(14, $texts[0]->dueDays);
    }
}
