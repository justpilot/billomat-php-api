<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use DateTimeImmutable;
use Justpilot\Billomat\Api\SettingsApi;
use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\NumberRangeMode;
use Justpilot\Billomat\Model\Enum\TemplateEngine;
use Justpilot\Billomat\Model\Settings;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(SettingsApi::class)]
#[CoversClass(Settings::class)]
final class SettingsApiTest extends TestCase
{
    #[Test]
    public function itGetsSettingsAndMapsTypes(): void
    {
        $body = json_encode([
            'settings' => [
                'created' => '2025-11-19T17:11:34+01:00',
                'updated' => '2025-11-25T17:19:22+01:00',
                'bgcolor' => '999999',
                'currency_code' => 'EUR',
                'locale' => 'de_DE',
                'net_gross' => 'NET',
                'number_range_mode' => 'CONSIDER_PREFIX',
                'article_number_length' => '0',
                'article_number_next' => '1',
                'client_number_length' => '5',
                'client_number_next' => '93',
                'invoice_number_pre' => 'RE-[Date.year]-',
                'invoice_number_length' => '5',
                'invoice_number_next' => '1',
                'due_days' => '14',
                'discount_rate' => '0',
                'discount_days' => '7',
                'template_engine' => 'DEFAULT',
                'print_version' => '0',
                'default_email_sender' => 'Example GmbH <info@example.test>',
                'bcc_addresses' => '',
                'price_group2' => 'Preisgruppe 2',
                'price_group3' => 'Preisgruppe 3',
            ],
        ], JSON_THROW_ON_ERROR);

        $mock = new MockHttpClient([
            new MockResponse($body, ['http_code' => 200]),
        ]);

        $config = new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key');
        $http = new BillomatHttpClient($mock, $config);
        $api = new SettingsApi($http);

        $settings = $api->get();

        self::assertInstanceOf(Settings::class, $settings);

        self::assertInstanceOf(DateTimeImmutable::class, $settings->created);
        self::assertInstanceOf(DateTimeImmutable::class, $settings->updated);

        self::assertSame('999999', $settings->bgcolor);
        self::assertSame('EUR', $settings->currencyCode);
        self::assertSame('de_DE', $settings->locale);

        self::assertSame(NetGross::NET, $settings->netGross);
        self::assertSame(NumberRangeMode::CONSIDER_PREFIX, $settings->numberRangeMode);
        self::assertSame(TemplateEngine::DEFAULT, $settings->templateEngine);

        self::assertSame(0, $settings->articleNumberLength);
        self::assertSame(1, $settings->articleNumberNext);
        self::assertSame(5, $settings->clientNumberLength);
        self::assertSame(93, $settings->clientNumberNext);

        self::assertSame('RE-[Date.year]-', $settings->invoiceNumberPre);
        self::assertSame(5, $settings->invoiceNumberLength);
        self::assertSame(1, $settings->invoiceNumberNext);

        self::assertSame(14, $settings->dueDays);
        self::assertSame(0.0, $settings->discountRate);
        self::assertSame(7, $settings->discountDays);

        self::assertFalse($settings->printVersion);
        self::assertSame('Example GmbH <info@example.test>', $settings->defaultEmailSender);
        self::assertSame([], $settings->bccAddresses);

        self::assertSame([
            2 => 'Preisgruppe 2',
            3 => 'Preisgruppe 3',
        ], $settings->priceGroups);
    }

    #[Test]
    public function itUpdatesSettingsViaPutAndSendsExpectedPayload(): void
    {
        $captured = [];

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$captured): MockResponse {
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            $responseBody = json_encode([
                'settings' => [
                    'currency_code' => 'EUR',
                    'locale' => 'de_DE',
                    'net_gross' => 'GROSS',
                    'number_range_mode' => 'IGNORE_PREFIX',
                    'due_days' => '30',
                    'print_version' => '1',
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($responseBody, ['http_code' => 200]);
        });

        $config = new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key');
        $http = new BillomatHttpClient($mock, $config);
        $api = new SettingsApi($http);

        $opts = new SettingsUpdateOptions();
        $opts->netGross = NetGross::GROSS;
        $opts->numberRangeMode = NumberRangeMode::IGNORE_PREFIX;
        $opts->dueDays = 30;
        $opts->printVersion = true;

        $updated = $api->update($opts);

        // Response Mapping prüfen
        self::assertInstanceOf(Settings::class, $updated);
        self::assertSame(NetGross::GROSS, $updated->netGross);
        self::assertSame(NumberRangeMode::IGNORE_PREFIX, $updated->numberRangeMode);
        self::assertSame(30, $updated->dueDays);
        self::assertTrue($updated->printVersion);

        // Request prüfen
        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/settings', $captured['url']);

        $options = $captured['options'] ?? [];

        // Payload robust lesen: json-Option oder body-String
        $payload = $options['json'] ?? null;

        if (null === $payload && isset($options['body']) && \is_string($options['body']) && '' !== $options['body']) {
            $payload = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
        }

        self::assertIsArray($payload, 'Expected JSON payload array (options[json] or decoded options[body]).');
        self::assertArrayHasKey('settings', $payload);
        self::assertIsArray($payload['settings']);

        // nur gesetzte Felder werden übertragen
        self::assertSame('GROSS', $payload['settings']['net_gross'] ?? null);
        self::assertSame('IGNORE_PREFIX', $payload['settings']['number_range_mode'] ?? null);
        self::assertSame(30, $payload['settings']['due_days'] ?? null);
        self::assertSame(1, $payload['settings']['print_version'] ?? null);

        // nicht gesetzt => nicht im Payload
        self::assertArrayNotHasKey('currency_code', $payload['settings']);
        self::assertArrayNotHasKey('locale', $payload['settings']);
    }

    #[Test]
    public function updateOptionsSerializeAllNewWritableFields(): void
    {
        $opts = new SettingsUpdateOptions();

        $opts->bgcolor = 'ffffff';
        $opts->color1 = 'aabbcc';
        $opts->articleNumberPre = 'A-';
        $opts->articleNumberLength = 5;

        $opts->invoiceLabel = 'Rechnung';
        $opts->invoiceIntro = 'Anbei …';
        $opts->invoiceNote = 'Bitte überweisen …';

        $opts->offerLabel = 'Angebot';
        $opts->offerIntro = 'Hier unser Angebot';
        $opts->offerNote = 'Gültig 30 Tage';
        $opts->offerFilename = 'angebot-{nr}';

        $opts->confirmationNumberPre = 'AB-';
        $opts->confirmationNumberLength = 4;
        $opts->confirmationLabel = 'Auftragsbestätigung';
        $opts->confirmationIntro = 'Wir bestätigen Ihren Auftrag';

        $opts->creditNoteNumberPre = 'GU-';
        $opts->creditNoteLabel = 'Gutschrift';

        $opts->deliveryNoteNumberPre = 'LS-';
        $opts->deliveryNoteLabel = 'Lieferschein';

        $opts->reminderFilename = 'mahnung-{nr}';
        $opts->reminderDueDays = 7;

        $opts->letterLabel = 'Brief';
        $opts->letterIntro = '…';

        $opts->bccAddresses = ['archiv@example.com', 'cc@example.com'];
        $opts->taxation = 'STANDARD';

        $opts->priceGroups = [2 => 'Sonderpreis', 3 => 'B2B'];

        $payload = $opts->toArray();

        self::assertSame('ffffff', $payload['bgcolor']);
        self::assertSame('A-', $payload['article_number_pre']);
        self::assertSame(5, $payload['article_number_length']);

        self::assertSame('Rechnung', $payload['invoice_label']);
        self::assertSame('Anbei …', $payload['invoice_intro']);
        self::assertSame('Bitte überweisen …', $payload['invoice_note']);

        self::assertSame('Angebot', $payload['offer_label']);
        self::assertSame('angebot-{nr}', $payload['offer_filename']);

        self::assertSame('AB-', $payload['confirmation_number_pre']);
        self::assertSame(4, $payload['confirmation_number_length']);
        self::assertSame('Auftragsbestätigung', $payload['confirmation_label']);

        self::assertSame('GU-', $payload['credit_note_number_pre']);
        self::assertSame('Gutschrift', $payload['credit_note_label']);

        self::assertSame('LS-', $payload['delivery_note_number_pre']);
        self::assertSame('Lieferschein', $payload['delivery_note_label']);

        self::assertSame('mahnung-{nr}', $payload['reminder_filename']);
        self::assertSame(7, $payload['reminder_due_days']);

        self::assertSame('Brief', $payload['letter_label']);

        self::assertSame('archiv@example.com,cc@example.com', $payload['bcc_addresses']);
        self::assertSame('STANDARD', $payload['taxation']);

        self::assertSame('Sonderpreis', $payload['price_group2']);
        self::assertSame('B2B', $payload['price_group3']);

        // nicht gesetzte Felder fallen heraus
        self::assertArrayNotHasKey('currency_code', $payload);
    }

    #[Test]
    public function itParsesBccAddressesFromCsvString(): void
    {
        $body = json_encode([
            'settings' => [
                'currency_code' => 'EUR',
                'net_gross' => 'NET',
                'bcc_addresses' => 'a@example.test, b@example.test ,c@example.test',
            ],
        ], JSON_THROW_ON_ERROR);

        $mock = new MockHttpClient([
            new MockResponse($body, ['http_code' => 200]),
        ]);

        $config = new BillomatConfig(billomatId: 'mycompany', apiKey: 'secret-key');
        $http = new BillomatHttpClient($mock, $config);
        $api = new SettingsApi($http);

        $settings = $api->get();

        self::assertSame(
            ['a@example.test', 'b@example.test', 'c@example.test'],
            $settings->bccAddresses
        );
    }
}
