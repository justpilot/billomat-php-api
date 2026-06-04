<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Api\UserPropertiesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\PropertyType;
use Justpilot\Billomat\Model\UserProperty;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(UserPropertiesApi::class)]
#[CoversClass(UserProperty::class)]
#[CoversClass(PropertyCreateOptions::class)]
#[CoversClass(PropertyType::class)]
final class UserPropertiesApiTest extends TestCase
{
    private function client(MockHttpClient $mock): BillomatHttpClient
    {
        return new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret-key'));
    }

    #[Test]
    public function itListsUserProperties(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertSame('GET', $method);
            self::assertStringContainsString('/api/user-properties', $url);

            return new MockResponse(json_encode([
                'user-properties' => [
                    'user-property' => [
                        ['id' => 7, 'name' => 'Premium-Kunden-Mitarbeiter', 'type' => 'CHECKBOX'],
                        ['id' => 11, 'name' => 'Geburtstag', 'type' => 'TEXTFIELD'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new UserPropertiesApi($this->client($mock));

        $props = $api->list();
        self::assertCount(2, $props);
        self::assertSame(7, $props[0]->id);
        self::assertSame(PropertyType::CHECKBOX, $props[0]->type);
        self::assertSame(PropertyType::TEXTFIELD, $props[1]->type);
    }

    #[Test]
    public function itGetsSingleUserProperty(): void
    {
        $mock = new MockHttpClient(static function (string $method, string $url): MockResponse {
            self::assertStringEndsWith('/api/user-properties/7', $url);

            return new MockResponse(json_encode([
                'user-property' => [
                    'id' => 7,
                    'name' => 'Premium-Kunden-Mitarbeiter',
                    'type' => 'CHECKBOX',
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new UserPropertiesApi($this->client($mock));

        $prop = $api->get(7);
        self::assertNotNull($prop);
        self::assertSame('Premium-Kunden-Mitarbeiter', $prop->name);
    }

    #[Test]
    public function itReturnsNullForUnknownUserProperty(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse('Not Found', ['http_code' => 404]));

        $api = new UserPropertiesApi($this->client($mock));

        self::assertNull($api->get(999));
    }

    #[Test]
    public function itCreatesUserPropertyAndWrapsPayload(): void
    {
        $capturedMethod = null;
        $capturedUrl = null;
        $capturedBody = '';

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use (&$capturedMethod, &$capturedUrl, &$capturedBody): MockResponse {
            $capturedMethod = $method;
            $capturedUrl = $url;
            $body = $options['body'] ?? '';
            $capturedBody = \is_string($body) ? $body : '';

            return new MockResponse(json_encode([
                'user-property' => [
                    'id' => 11,
                    'name' => 'Geburtstag',
                    'type' => 'TEXTFIELD',
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 201]);
        });

        $api = new UserPropertiesApi($this->client($mock));

        $opts = new PropertyCreateOptions(name: 'Geburtstag');
        $opts->type = PropertyType::TEXTFIELD;

        $created = $api->create($opts);

        self::assertSame('POST', $capturedMethod);
        self::assertStringEndsWith('/api/user-properties', (string) $capturedUrl);
        $decoded = json_decode($capturedBody, true, 16, JSON_THROW_ON_ERROR);
        self::assertSame(['user-property' => ['name' => 'Geburtstag', 'type' => 'TEXTFIELD']], $decoded);

        self::assertSame(11, $created->id);
        self::assertSame(PropertyType::TEXTFIELD, $created->type);
    }

    #[Test]
    public function itUpdatesUserPropertyViaPut(): void
    {
        $capturedMethod = null;
        $capturedUrl = null;

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$capturedMethod, &$capturedUrl): MockResponse {
            $capturedMethod = $method;
            $capturedUrl = $url;

            return new MockResponse(json_encode([
                'user-property' => ['id' => 11, 'name' => 'private Anschrift', 'type' => 'TEXTAREA'],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $api = new UserPropertiesApi($this->client($mock));

        $opts = new PropertyCreateOptions(name: 'private Anschrift');
        $opts->type = PropertyType::TEXTAREA;

        $updated = $api->update(11, $opts);

        self::assertSame('PUT', $capturedMethod);
        self::assertStringEndsWith('/api/user-properties/11', (string) $capturedUrl);
        self::assertSame(PropertyType::TEXTAREA, $updated->type);
    }

    #[Test]
    public function itDeletesUserProperty(): void
    {
        $capturedMethod = null;
        $capturedUrl = null;

        $mock = new MockHttpClient(static function (string $method, string $url) use (&$capturedMethod, &$capturedUrl): MockResponse {
            $capturedMethod = $method;
            $capturedUrl = $url;

            return new MockResponse('', ['http_code' => 200]);
        });

        $api = new UserPropertiesApi($this->client($mock));

        self::assertTrue($api->delete(11));
        self::assertSame('DELETE', $capturedMethod);
        self::assertStringEndsWith('/api/user-properties/11', (string) $capturedUrl);
    }

    #[Test]
    public function listNormalisesSingleObjectIntoList(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(json_encode([
            'user-properties' => [
                'user-property' => ['id' => 7, 'name' => 'Solo', 'type' => 'CHECKBOX'],
            ],
        ], JSON_THROW_ON_ERROR), ['http_code' => 200]));

        $api = new UserPropertiesApi($this->client($mock));

        $props = $api->list();
        self::assertCount(1, $props);
        self::assertSame('Solo', $props[0]->name);
    }
}
