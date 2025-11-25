<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Http;

use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class BillomatHttpClientTest extends TestCase
{
    public function test_it_sends_requests_with_expected_headers_and_url(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            // capture info for later assertions
            $captured['method'] = $method;
            $captured['url'] = $url;
            $captured['options'] = $options;

            return new MockResponse('{"ok": true}', [
                'http_code' => 200,
            ]);
        });

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
            appId: 'app-id',
            appSecret: 'app-secret',
        );

        $client = new BillomatHttpClient($mock, $config);

        $response = $client->request('GET', '/clients');

        self::assertSame(200, $response->getStatusCode());

        // Assert method + URL
        self::assertSame('GET', $captured['method']);
        self::assertSame(
            'https://mycompany.billomat.net/api/clients',
            $captured['url']
        );

        // Normalise headers from options (can be associative or list of "Name: value" strings)
        $rawHeaders = $captured['options']['headers'] ?? [];
        $normalized = [];

        foreach ($rawHeaders as $key => $value) {
            if (is_int($key)) {
                // "Header-Name: value" style
                if (is_string($value) && str_contains($value, ':')) {
                    [$name, $val] = explode(':', $value, 2);
                    $normalized[strtolower(trim($name))] = trim($val);
                }
            } else {
                // "Header-Name" => "value" style
                $normalized[strtolower($key)] = is_array($value) ? implode(', ', $value) : $value;
            }
        }

        self::assertSame('secret-key', $normalized['x-billomatapikey'] ?? null);
        self::assertSame('app-id', $normalized['x-appid'] ?? null);
        self::assertSame('app-secret', $normalized['x-appsecret'] ?? null);
        self::assertSame('application/json', $normalized['accept'] ?? null);
    }
}