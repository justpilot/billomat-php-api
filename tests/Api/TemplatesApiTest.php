<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\TemplateCreateOptions;
use Justpilot\Billomat\Api\TemplateUpdateOptions;
use Justpilot\Billomat\Api\TemplatesApi;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;
use Justpilot\Billomat\Model\Enum\TemplateType;
use Justpilot\Billomat\Model\Template;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

final class TemplatesApiTest extends TestCase
{
    /**
     * @param array<string,mixed> $options
     * @return array<string,mixed>
     */
    private function extractJsonPayload(array $options): array
    {
        $payload = $options['json'] ?? null;

        if ($payload === null && isset($options['body']) && is_string($options['body']) && $options['body'] !== '') {
            /** @var array<string,mixed> $decoded */
            $decoded = json_decode($options['body'], true, flags: JSON_THROW_ON_ERROR);
            $payload = $decoded;
        }

        self::assertIsArray($payload, 'Expected JSON payload array (options[json] or decoded options[body]).');

        /** @var array<string,mixed> $payload */
        return $payload;
    }

    public function test_it_lists_templates(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'templates' => [
                    'template' => [
                        [
                            'id' => '10',
                            'created' => '2025-11-19T17:11:34+01:00',
                            'type' => 'INVOICE',
                            'template_type' => 'DEFINED',
                            'name' => 'Standard Rechnung',
                            'is_default' => '1',
                        ],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $list = $api->list(['per_page' => 10]);

        self::assertIsArray($list);
        self::assertCount(1, $list);
        self::assertContainsOnlyInstancesOf(Template::class, $list);

        $tpl = $list[0];
        self::assertSame(10, $tpl->id);
        self::assertSame(TemplateDocumentType::INVOICE, $tpl->type);
        self::assertSame(TemplateType::DEFINED, $tpl->templateType);
        self::assertSame('Standard Rechnung', $tpl->name);
        self::assertTrue($tpl->isDefault);
    }

    public function test_it_gets_single_template_and_can_include_base64file_for_uploaded(): void
    {
        $mock = new MockHttpClient([
            new MockResponse(json_encode([
                'template' => [
                    'id' => '11',
                    'created' => '2025-11-19T17:11:34+01:00',
                    'type' => 'INVOICE',
                    'template_type' => 'UPLOADED',
                    'name' => 'Meine Upload Vorlage',
                    'format' => 'docx',
                    'base64file' => 'BASE64...',
                    'is_default' => '0',
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $tpl = $api->get(11);

        self::assertInstanceOf(Template::class, $tpl);
        self::assertSame(11, $tpl->id);
        self::assertSame(TemplateType::UPLOADED, $tpl->templateType);
        self::assertSame(TemplateFormat::DOCX, $tpl->format);
        self::assertSame('BASE64...', $tpl->base64file);
    }

    public function test_it_creates_template_via_post_and_sends_wrapper_payload(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured = compact('method', 'url', 'options');

            return new MockResponse(json_encode([
                'template' => [
                    'id' => '99',
                    'type' => 'INVOICE',
                    'template_type' => 'DEFINED',
                    'name' => 'Neu',
                    'is_default' => '0',
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 201]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $opts = new TemplateCreateOptions(TemplateDocumentType::INVOICE);
        $opts->name = 'Neu';

        $created = $api->create($opts);

        self::assertInstanceOf(Template::class, $created);
        self::assertSame(99, $created->id);

        self::assertSame('POST', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/templates', $captured['url']);

        $options = $captured['options'] ?? [];
        $payload = $this->extractJsonPayload($options);

        self::assertArrayHasKey('template', $payload);
        self::assertIsArray($payload['template']);

        self::assertSame('INVOICE', $payload['template']['type'] ?? null);
        self::assertSame('Neu', $payload['template']['name'] ?? null);
    }

    public function test_it_updates_template_via_put(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured = compact('method', 'url', 'options');

            return new MockResponse(json_encode([
                'template' => [
                    'id' => '12',
                    'type' => 'INVOICE',
                    'template_type' => 'DEFINED',
                    'name' => 'Umbenannt',
                    'is_default' => '1',
                ],
            ], JSON_THROW_ON_ERROR), ['http_code' => 200]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $u = new TemplateUpdateOptions();
        $u->name = 'Umbenannt';
        $u->isDefault = true;

        $updated = $api->update(12, $u);

        self::assertSame(12, $updated->id);
        self::assertSame('Umbenannt', $updated->name);
        self::assertTrue($updated->isDefault);

        self::assertSame('PUT', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/templates/12', $captured['url']);

        $options = $captured['options'] ?? [];
        $payload = $this->extractJsonPayload($options);

        self::assertArrayHasKey('template', $payload);
        self::assertIsArray($payload['template']);

        self::assertSame('Umbenannt', $payload['template']['name'] ?? null);
        self::assertSame(1, $payload['template']['is_default'] ?? null);
    }

    public function test_it_deletes_template(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        self::assertTrue($api->delete(10));
    }

    public function test_it_fetches_thumb_as_raw_binary(): void
    {
        $captured = [];

        $mock = new MockHttpClient(function (string $method, string $url, array $options) use (&$captured) {
            $captured = compact('method', 'url', 'options');

            return new MockResponse("PNGDATA", [
                'http_code' => 200,
                'response_headers' => [
                    'content-type: image/png',
                ],
            ]);
        });

        $http = new BillomatHttpClient($mock, new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $raw = $api->thumb(7, TemplateThumbFormat::PNG);

        self::assertSame('PNGDATA', $raw);
        self::assertSame('GET', $captured['method']);
        self::assertSame('https://mycompany.billomat.net/api/templates/7/thumb?format=png', $captured['url']);
    }
}