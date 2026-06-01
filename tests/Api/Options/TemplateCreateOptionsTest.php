<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use Justpilot\Billomat\Api\TemplateCreateOptions;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use Justpilot\Billomat\Model\Enum\TemplateFormat;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateCreateOptions::class)]
final class TemplateCreateOptionsTest extends TestCase
{
    #[Test]
    public function minimalPayloadOnlyContainsRequiredType(): void
    {
        $options = new TemplateCreateOptions(TemplateDocumentType::INVOICE);

        self::assertSame(['type' => 'INVOICE'], $options->toArray());
    }

    #[Test]
    public function itSerializesFormatEnumAsStringValue(): void
    {
        $options = new TemplateCreateOptions(TemplateDocumentType::OFFER);
        $options->format = TemplateFormat::DOCX;
        $options->base64file = 'BASE64==';

        $payload = $options->toArray();

        self::assertSame('OFFER', $payload['type']);
        self::assertSame('docx', $payload['format']);
        self::assertSame('BASE64==', $payload['base64file']);
    }

    #[Test]
    public function isDefaultIsSerializedAsOneOrZeroAndOmittedWhenNull(): void
    {
        $options = new TemplateCreateOptions(TemplateDocumentType::INVOICE);

        // null → weggelassen
        self::assertArrayNotHasKey('is_default', $options->toArray());

        $options->isDefault = true;
        self::assertSame(1, $options->toArray()['is_default']);

        $options->isDefault = false;
        self::assertSame(0, $options->toArray()['is_default']);
    }
}
