<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api;

use Justpilot\Billomat\Api\TemplateCreateOptions;
use Justpilot\Billomat\Api\TemplatesApi;
use Justpilot\Billomat\Api\TemplateUpdateOptions;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Config\RetryPolicy;
use Justpilot\Billomat\Http\BillomatHttpClient;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;
use Justpilot\Billomat\Model\Enum\TemplateType;
use Justpilot\Billomat\Model\Template;
use Justpilot\Billomat\Tests\Support\CapturedRequest;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Psr18Client;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(TemplatesApi::class)]
#[CoversClass(Template::class)]
#[UsesClass(TemplateCreateOptions::class)]
#[UsesClass(TemplateUpdateOptions::class)]
#[UsesClass(BillomatConfig::class)]
#[UsesClass(RetryPolicy::class)]
#[UsesClass(BillomatHttpClient::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(TemplateDocumentType::class)]
#[UsesClass(TemplateFormat::class)]
#[UsesClass(TemplateType::class)]
final class TemplatesApiTest extends TestCase
{
    #[Test]
    public function itListsTemplates(): void
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

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret'));
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

    #[Test]
    public function itGetsSingleTemplateAndCanIncludeBase64fileForUploaded(): void
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

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $tpl = $api->get(11);

        self::assertInstanceOf(Template::class, $tpl);
        self::assertSame(11, $tpl->id);
        self::assertSame(TemplateType::UPLOADED, $tpl->templateType);
        self::assertSame(TemplateFormat::DOCX, $tpl->format);
        self::assertSame('BASE64...', $tpl->base64file);
    }

    #[Test]
    public function itCreatesTemplateViaPostAndSendsWrapperPayload(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

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

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $opts = new TemplateCreateOptions(TemplateDocumentType::INVOICE, name: 'Neu');

        $created = $api->create($opts);

        self::assertSame(99, $created->id);

        self::assertSame('POST', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/templates', $captured->url);

        $payload = $captured->json();

        self::assertArrayHasKey('template', $payload);
        self::assertIsArray($payload['template']);

        self::assertSame('INVOICE', $captured->jsonAt('template', 'type'));
        self::assertSame('Neu', $captured->jsonAt('template', 'name'));
    }

    #[Test]
    public function itUpdatesTemplateViaPut(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

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

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $u = new TemplateUpdateOptions(name: 'Umbenannt', isDefault: true);

        $updated = $api->update(12, $u);

        self::assertSame(12, $updated->id);
        self::assertSame('Umbenannt', $updated->name);
        self::assertTrue($updated->isDefault);

        self::assertSame('PUT', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/templates/12', $captured->url);

        $payload = $captured->json();

        self::assertArrayHasKey('template', $payload);
        self::assertIsArray($payload['template']);

        self::assertSame('Umbenannt', $captured->jsonAt('template', 'name'));
        self::assertSame(1, $captured->jsonAt('template', 'is_default'));
    }

    #[Test]
    public function itDeletesTemplate(): void
    {
        $mock = new MockHttpClient([
            new MockResponse('', ['http_code' => 200]),
        ]);

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $api->delete(10);

        self::assertSame(1, $mock->getRequestsCount());
    }

    #[Test]
    public function itFetchesThumbAsRawBinary(): void
    {
        $captured = new CapturedRequest();

        $mock = new MockHttpClient(static function (string $method, string $url, array $options) use ($captured): MockResponse {
            $captured->record($method, $url, $options);

            return new MockResponse('PNGDATA', [
                'http_code' => 200,
                'response_headers' => [
                    'content-type: image/png',
                ],
            ]);
        });

        $http = new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('mycompany', 'secret'));
        $api = new TemplatesApi($http);

        $raw = $api->thumb(7, TemplateThumbFormat::PNG);

        self::assertSame('PNGDATA', $raw);
        self::assertSame('GET', $captured->method);
        self::assertSame('https://mycompany.billomat.net/api/templates/7/thumb?format=png', $captured->url);
    }

    #[Test]
    public function listNormalisesSingleObjectIntoList(): void
    {
        // Billomat liefert bei genau einer Vorlage oft direkt ein Objekt statt einer Liste
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode([
                'templates' => [
                    'template' => [
                        'id' => 42,
                        'type' => 'INVOICE',
                        'template_type' => 'DEFINED',
                        'name' => 'Standard',
                    ],
                ],
            ], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $api = new TemplatesApi(new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('x', 'k')));

        $templates = $api->list();

        self::assertCount(1, $templates);
        self::assertSame(42, $templates[0]->id);
        self::assertSame('Standard', $templates[0]->name);
    }

    #[Test]
    public function listReturnsEmptyListWhenNodeMissing(): void
    {
        $mock = new MockHttpClient(static fn (): MockResponse => new MockResponse(
            json_encode(['templates' => []], JSON_THROW_ON_ERROR),
            ['http_code' => 200],
        ));

        $api = new TemplatesApi(new BillomatHttpClient(new Psr18Client($mock), new BillomatConfig('x', 'k')));

        self::assertSame([], $api->list());
    }
}
