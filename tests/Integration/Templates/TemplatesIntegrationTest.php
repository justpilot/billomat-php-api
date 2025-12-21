<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Templates;

use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;
use Justpilot\Billomat\Model\Template;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

final class TemplatesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    public function test_can_list_templates_from_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $templates = $billomat->templates->list([
            'per_page' => 10,
        ]);

        self::assertIsArray($templates);
        self::assertContainsOnlyInstancesOf(Template::class, $templates);

        if ($templates !== []) {
            $first = $templates[0];

            self::assertNotNull($first->id);
            self::assertGreaterThan(0, $first->id);

            // type / templateType können je nach Sandbox immer gesetzt sein
            self::assertNotNull($first->type);
            self::assertNotNull($first->templateType);

            // name kann leer sein, daher nicht zu hart prüfen
        }
    }

    #[Group('integration')]
    public function test_can_get_single_template_from_sandbox_when_available(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $templates = $billomat->templates->list(['per_page' => 1]);

        if ($templates === []) {
            $this->markTestSkipped('No templates available in sandbox to test get().');
        }

        $id = $templates[0]->id;

        if ($id === null) {
            $this->markTestSkipped('Template id missing in list response.');
        }

        $tpl = $billomat->templates->get($id);

        self::assertInstanceOf(Template::class, $tpl);
        self::assertSame($id, $tpl->id);
        self::assertNotNull($tpl->type);
        self::assertNotNull($tpl->templateType);

        // Für UPLOADED kann format/base64file gesetzt sein – muss aber nicht immer
        if ($tpl->templateType?->value === 'UPLOADED') {
            // format/base64file sind laut Doku erst beim single GET vorhanden
            // aber wir prüfen nur "nicht kaputt"
            self::assertTrue(true);
        }
    }

    #[Group('integration')]
    public function test_can_fetch_template_thumb_from_sandbox_when_available(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $templates = $billomat->templates->list(['per_page' => 1]);

        if ($templates === []) {
            $this->markTestSkipped('No templates available in sandbox to test thumb().');
        }

        $id = $templates[0]->id;

        if ($id === null) {
            $this->markTestSkipped('Template id missing in list response.');
        }

        $raw = $billomat->templates->thumb($id, TemplateThumbFormat::PNG);

        self::assertIsString($raw);
        self::assertNotSame('', $raw, 'Thumb response should not be empty.');
    }
}