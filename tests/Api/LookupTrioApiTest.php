<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\CountryTaxesApi;
use Justpilot\Billomat\Api\IncomingCategoriesApi;
use Justpilot\Billomat\Api\RolesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\CountryTax;
use Justpilot\Billomat\Model\IncomingCategory;
use Justpilot\Billomat\Model\Role;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(IncomingCategoriesApi::class)]
#[CoversClass(RolesApi::class)]
#[CoversClass(CountryTaxesApi::class)]
#[CoversClass(IncomingCategory::class)]
#[CoversClass(Role::class)]
#[CoversClass(CountryTax::class)]
final class LookupTrioApiTest extends TestCase
{
    private function client(MockHttpClient $mock): BillomatHttpClient
    {
        return new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret-key'));
    }

    #[Test]
    public function itListsIncomingCategories(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('GET', $method);
            self::assertStringContainsString('/api/incoming-categories', $url);

            return new MockResponse(json_encode([
                'incoming-categories' => [
                    'incoming-category' => [
                        ['id' => 'goods', 'title' => 'Waren', 'description' => 'Produkte'],
                        ['id' => 'services', 'title' => 'Dienstleistungen', 'description' => null],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new IncomingCategoriesApi($this->client($mock));

        $categories = $api->list();
        self::assertCount(2, $categories);
        self::assertSame('goods', $categories[0]->id);
        self::assertSame('Waren', $categories[0]->title);
        self::assertNull($categories[1]->description);
    }

    #[Test]
    public function itGetsSingleIncomingCategoryByStringId(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/incoming-categories/goods', $url);

            return new MockResponse(json_encode([
                'incoming-category' => [
                    'id' => 'goods',
                    'title' => 'Waren, Rohstoffe, Hilfsstoffe',
                    'description' => 'Fertige Produkte',
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new IncomingCategoriesApi($this->client($mock));

        $category = $api->get('goods');
        self::assertNotNull($category);
        self::assertSame('goods', $category->id);
        self::assertSame('Waren, Rohstoffe, Hilfsstoffe', $category->title);
    }

    #[Test]
    public function itReturnsNullForUnknownIncomingCategory(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('Not Found', ['http_code' => 404]));

        $api = new IncomingCategoriesApi($this->client($mock));

        self::assertNull($api->get('unknown'));
    }

    #[Test]
    public function itListsRolesAndExtractsPermissions(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringContainsString('/api/roles', $url);
            self::assertStringContainsString('name=Manager', $url);

            return new MockResponse(json_encode([
                'roles' => [
                    'role' => [
                        [
                            'id' => 1,
                            'name' => 'Master',
                            'articles' => 'DELETE',
                            'clients' => 'DELETE',
                            'invoices' => 'DELETE',
                            'settings_my_account' => 'UPDATE',
                            'settings_addons' => 'UPDATE',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new RolesApi($this->client($mock));

        $roles = $api->list(['name' => 'Manager']);
        self::assertCount(1, $roles);

        $role = $roles[0];
        self::assertSame(1, $role->id);
        self::assertSame('Master', $role->name);
        self::assertSame('DELETE', $role->permissions['articles']);
        self::assertSame('UPDATE', $role->permissions['settings_addons']);
        self::assertArrayNotHasKey('id', $role->permissions);
        self::assertArrayNotHasKey('name', $role->permissions);
    }

    #[Test]
    public function itGetsSingleRole(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/roles/42', $url);

            return new MockResponse(json_encode([
                'role' => [
                    'id' => 42,
                    'name' => 'Azubi',
                    'articles' => 'READ',
                    'offers' => '',
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new RolesApi($this->client($mock));

        $role = $api->get(42);
        self::assertNotNull($role);
        self::assertSame(42, $role->id);
        self::assertSame('READ', $role->permissions['articles']);
        self::assertNull($role->permissions['offers']);
    }

    #[Test]
    public function itListsCountryTaxes(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringContainsString('/api/country-taxes', $url);
            self::assertStringContainsString('country=CH', $url);

            return new MockResponse(json_encode([
                'country-taxes' => [
                    'country-tax' => [
                        ['id' => 7, 'country_code' => 'CH'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new CountryTaxesApi($this->client($mock));

        $rows = $api->list(['country' => 'CH']);
        self::assertCount(1, $rows);
        self::assertSame(7, $rows[0]->id);
        self::assertSame('CH', $rows[0]->countryCode);
    }

    #[Test]
    public function itGetsSingleCountryTax(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/country-taxes/7', $url);

            return new MockResponse(json_encode([
                'country-tax' => ['id' => 7, 'country_code' => 'CH'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new CountryTaxesApi($this->client($mock));

        $row = $api->get(7);
        self::assertNotNull($row);
        self::assertSame('CH', $row->countryCode);
    }

    #[Test]
    public function listNormalisesSingleObjectIntoList(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'country-taxes' => [
                'country-tax' => ['id' => 7, 'country_code' => 'CH'],
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new CountryTaxesApi($this->client($mock));

        $rows = $api->list();
        self::assertCount(1, $rows);
        self::assertSame('CH', $rows[0]->countryCode);
    }

    #[Test]
    public function listReturnsEmptyListWhenNodeMissing(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'roles' => [],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new RolesApi($this->client($mock));

        self::assertSame([], $api->list());
    }

    #[Test]
    public function getOnAuthErrorPropagatesAndIsNotSwallowed(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('forbidden', ['http_code' => 403]));

        $api = new RolesApi($this->client($mock));

        $this->expectException(AuthenticationException::class);
        $api->get(1);
    }
}
