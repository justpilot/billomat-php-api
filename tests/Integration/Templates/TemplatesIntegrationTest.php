<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Templates;

use Justpilot\Billomat\Api\TemplateCreateOptions;
use Justpilot\Billomat\Api\TemplateUpdateOptions;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateThumbFormat;
use Justpilot\Billomat\Model\Template;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class TemplatesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canListTemplatesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $templates = $billomat->templates->list([
            'per_page' => 10,
        ]);

        self::assertIsArray($templates);
        self::assertContainsOnlyInstancesOf(Template::class, $templates);

        if ([] !== $templates) {
            $first = $templates[0];

            self::assertGreaterThan(0, $first->id);

            // type / templateType können je nach Sandbox immer gesetzt sein
            self::assertNotNull($first->type);
            self::assertNotNull($first->templateType);

            // name kann leer sein, daher nicht zu hart prüfen
        }
    }

    #[Group('integration')]
    #[Test]
    public function canGetSingleTemplateFromSandboxWhenAvailable(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $templates = $billomat->templates->list(['per_page' => 1]);

        if ([] === $templates) {
            self::markTestSkipped('No templates available in sandbox to test get().');
        }

        $id = $templates[0]->id;

        if (null === $id) {
            self::markTestSkipped('Template id missing in list response.');
        }

        $tpl = $billomat->templates->get($id);

        self::assertInstanceOf(Template::class, $tpl);
        self::assertSame($id, $tpl->id);
        self::assertNotNull($tpl->type);
        self::assertNotNull($tpl->templateType);

        // Für UPLOADED kann format/base64file gesetzt sein – muss aber nicht immer
        if ('UPLOADED' === $tpl->templateType->value) {
            // format/base64file sind laut Doku erst beim single GET vorhanden
            // aber wir prüfen nur "nicht kaputt"
        }
    }

    #[Group('integration')]
    #[Test]
    public function canFetchTemplateThumbFromSandboxWhenAvailable(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $templates = $billomat->templates->list(['per_page' => 1]);

        if ([] === $templates) {
            self::markTestSkipped('No templates available in sandbox to test thumb().');
        }

        $id = $templates[0]->id;

        if (null === $id) {
            self::markTestSkipped('Template id missing in list response.');
        }

        $raw = $billomat->templates->thumb($id, TemplateThumbFormat::PNG);

        self::assertIsString($raw);
        self::assertNotSame('', $raw, 'Thumb response should not be empty.');
    }

    /**
     * Anlegen dauert bei Billomat über 10 s (Konvertierung der Vorlage); der SDK-Standard-Timeout ist 30 s.
     */
    #[Group('integration')]
    #[Test]
    public function canCreateUpdateAndDeleteTemplateInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $rtf = '{\\rtf1\\ansi\\deff0 {\\fonttbl {\\f0 Arial;}}\\f0\\fs24 SDK-Integrationstest\\par}';

        $template = $billomat->templates->create(new TemplateCreateOptions(
            type: TemplateDocumentType::INVOICE,
            name: 'SDK-Integrationstest '.date('His'),
            format: TemplateFormat::RTF,
            base64File: base64_encode($rtf),
        ));

        try {
            self::assertSame(TemplateDocumentType::INVOICE, $template->type);
            self::assertFalse($template->isDefault);

            $fetched = $billomat->templates->getOrFail($template->id);
            self::assertSame(TemplateFormat::RTF, $fetched->format);
            self::assertNotSame('', (string) $fetched->base64File);

            $updated = $billomat->templates->update($template->id, new TemplateUpdateOptions(name: 'SDK-Integrationstest umbenannt'));
            self::assertSame('SDK-Integrationstest umbenannt', $updated->name);
        } finally {
            $billomat->templates->delete($template->id);
        }

        self::assertNull($billomat->templates->get($template->id));
    }
}
