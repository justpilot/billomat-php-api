<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\CountriesApi;
use Justpilot\Billomat\Api\CurrenciesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Country;
use Justpilot\Billomat\Model\Currency;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(CountriesApi::class)]
#[CoversClass(CurrenciesApi::class)]
#[CoversClass(Country::class)]
#[CoversClass(Currency::class)]
final class CountriesCurrenciesApiTest extends TestCase
{
    #[Test]
    public function itListsCountries(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'countries' => [
                    'country' => [
                        ['code' => 'DE', 'name' => 'Germany', 'name_de' => 'Deutschland', 'eu' => 1],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CountriesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $countries = $api->list();
        self::assertCount(1, $countries);
        self::assertSame('DE', $countries[0]->code);
        self::assertTrue($countries[0]->eu);
    }

    #[Test]
    public function itListsCurrencies(): void
    {
        $mock = new MockHttpClient(static function (): MockResponse {
            $body = json_encode([
                'currencies' => [
                    'currency' => [
                        ['code' => 'EUR', 'name' => 'Euro', 'symbol' => '€'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR);

            return new MockResponse($body, ['http_code' => 200]);
        });

        $api = new CurrenciesApi(new BillomatHttpClient(
            $mock,
            new BillomatConfig('mycompany', 'secret-key'),
        ));

        $currencies = $api->list();
        self::assertCount(1, $currencies);
        self::assertSame('EUR', $currencies[0]->code);
    }
}
