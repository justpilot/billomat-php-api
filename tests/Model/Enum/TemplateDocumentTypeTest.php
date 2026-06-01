<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

#[CoversClass(TemplateDocumentType::class)]
final class TemplateDocumentTypeTest extends TestCase
{
    #[Test]
    public function itExposesAllExpectedCases(): void
    {
        self::assertSame(
            [
                'INVOICE',
                'OFFER',
                'CONFIRMATION',
                'REMINDER',
                'DELIVERY_NOTE',
                'CREDIT_NOTE',
                'LETTER',
            ],
            array_map(static fn (TemplateDocumentType $c): string => $c->value, TemplateDocumentType::cases())
        );
    }

    #[Test]
    public function fromApiHandlesNullAndUnknown(): void
    {
        self::assertNull(TemplateDocumentType::fromApi(null));
        self::assertNull(TemplateDocumentType::fromApi('BOGUS'));
        self::assertSame(TemplateDocumentType::INVOICE, TemplateDocumentType::fromApi('INVOICE'));
    }

    #[Test]
    public function eachCaseHasNonEmptyLabel(): void
    {
        foreach (TemplateDocumentType::cases() as $case) {
            self::assertNotSame('', $case->label());
        }
    }
}
