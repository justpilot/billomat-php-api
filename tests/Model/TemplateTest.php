<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use DateTimeImmutable;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use Justpilot\Billomat\Model\Enum\TemplateType;
use Justpilot\Billomat\Model\Template;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(Template::class)]
final class TemplateTest extends TestCase
{
    #[Test]
    public function itHydratesUploadedTemplateWithBase64File(): void
    {
        $template = Template::fromArray([
            'id' => '42',
            'created' => '2024-02-10T08:00:00+01:00',
            'type' => 'INVOICE',
            'template_type' => 'UPLOADED',
            'name' => 'Custom Invoice',
            'format' => 'docx',
            'base64file' => 'BASE64==',
            'is_default' => '1',
        ]);

        self::assertSame(42, $template->id);
        self::assertInstanceOf(DateTimeImmutable::class, $template->created);
        self::assertSame(TemplateDocumentType::INVOICE, $template->type);
        self::assertSame(TemplateType::UPLOADED, $template->templateType);
        self::assertSame('Custom Invoice', $template->name);
        self::assertSame(TemplateFormat::DOCX, $template->format);
        self::assertSame('BASE64==', $template->base64file);
        self::assertTrue($template->isDefault);
    }

    #[Test]
    public function itHydratesDefinedTemplateWithoutFormat(): void
    {
        $template = Template::fromArray([
            'id' => 1,
            'type' => 'OFFER',
            'template_type' => 'DEFINED',
            'name' => 'Standard',
            'is_default' => 0,
        ]);

        self::assertNull($template->format);
        self::assertNull($template->base64file);
        self::assertFalse($template->isDefault);
        self::assertSame(TemplateType::DEFINED, $template->templateType);
    }

    #[Test]
    public function itAcceptsIsDefaultAsBoolean(): void
    {
        $template = Template::fromArray(['id' => 1, 'is_default' => true]);

        self::assertTrue($template->isDefault);
    }

    #[Test]
    public function itHandlesUnknownEnumValuesAsNull(): void
    {
        $template = Template::fromArray([
            'id' => 1,
            'type' => 'BOGUS',
            'template_type' => 'BOGUS',
            'format' => 'jpg',
        ]);

        self::assertNull($template->type);
        self::assertNull($template->templateType);
        self::assertNull($template->format);
    }

    #[Test]
    public function itHandlesInvalidCreatedDateGracefully(): void
    {
        $template = Template::fromArray(['id' => 1, 'created' => 'invalid']);

        self::assertNull($template->created);
    }

    #[Test]
    public function toArrayDropsNullsAndSerializesEnums(): void
    {
        $template = Template::fromArray([
            'id' => 42,
            'type' => 'INVOICE',
            'template_type' => 'UPLOADED',
            'name' => 'Custom',
            'format' => 'docx',
            'base64file' => 'BASE64==',
            'is_default' => 1,
        ]);

        $array = $template->toArray();

        self::assertSame('INVOICE', $array['type']);
        self::assertSame('UPLOADED', $array['template_type']);
        self::assertSame('docx', $array['format']);
        self::assertSame(1, $array['is_default']);
        self::assertArrayNotHasKey('created', $array);
    }
}
