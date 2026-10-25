<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\EmailTemplatesApi;
use Justpilot\Billomat\Api\FreeTextsApi;
use Justpilot\Billomat\Api\ReminderTextsApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\EmailTemplate;
use Justpilot\Billomat\Model\FreeText;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\ReminderText;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(EmailTemplatesApi::class)]
#[CoversClass(FreeTextsApi::class)]
#[CoversClass(ReminderTextsApi::class)]
#[CoversClass(EmailTemplate::class)]
#[CoversClass(FreeText::class)]
#[CoversClass(ReminderText::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(Money::class)]
final class TextResourcesApiTest extends TestCase
{
    #[Test]
    public function itListsEmailTemplates(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'email-templates' => [
                    'email-template' => [
                        [
                            'id' => '1',
                            'name' => 'Standard Rechnung',
                            'type' => 'INVOICES',
                            'subject' => 'Ihre Rechnung',
                            'text' => 'Im Anhang Ihre Rechnung.',
                            'bcc' => '1',
                            'is_default' => '1',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new EmailTemplatesApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $templates = $api->list();
        self::assertCount(1, $templates);
        self::assertSame('Standard Rechnung', $templates[0]->name);
        self::assertSame('INVOICES', $templates[0]->type);
        self::assertSame('Im Anhang Ihre Rechnung.', $templates[0]->body);
        self::assertTrue($templates[0]->bcc);
        self::assertTrue($templates[0]->isDefault);
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
            new Psr18Client($mock),
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
                        [
                            'id' => '1',
                            'name' => '1. Mahnung',
                            'sorting' => '2',
                            'charge_name' => 'Mahngebühr',
                            'charge_description' => 'Pauschale',
                            'charge_amount' => '5.5',
                            'due_days' => 14,
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new ReminderTextsApi(new BillomatHttpClient(
            new Psr18Client($mock),
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $texts = $api->list();
        self::assertCount(1, $texts);
        self::assertSame(2, $texts[0]->sort);
        self::assertSame('Mahngebühr', $texts[0]->chargeName);
        self::assertSame('Pauschale', $texts[0]->chargeDescription);
        self::assertSame('5.5', $texts[0]->chargeAmount?->toString());
    }
}
