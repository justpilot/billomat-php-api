<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Model\Enum\InboxDocumentType;
use Justpilot\Billomat\Model\InboxDocument;
use Justpilot\Billomat\Tests\Support\RequiredFields;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;

#[CoversClass(InboxDocument::class)]
#[UsesClass(InboxDocumentType::class)]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
final class InboxDocumentTest extends TestCase
{
    /**
     * Formate im Sandbox gemessen: Liste, einzelnes Paar ohne Liste, leerer String ohne Metadaten.
     *
     * @return iterable<string, array{mixed, array<string, string>}>
     */
    public static function metadata(): iterable
    {
        yield 'Liste' => [
            ['data' => [['key' => 'SENDER_CITY', 'value' => 'Siegen'], ['key' => 'WWW', 'value' => 'example.com']]],
            ['SENDER_CITY' => 'Siegen', 'WWW' => 'example.com'],
        ];
        yield 'einzelnes Paar' => [['data' => ['key' => 'SENDER_CITY', 'value' => 'Siegen']], ['SENDER_CITY' => 'Siegen']];
        yield 'leer' => ['', []];
        yield 'fehlt' => [null, []];
        yield 'ohne data' => [['foo' => 'bar'], []];
        yield 'kaputte Paare' => [['data' => ['nope', ['key' => 'WWW'], ['value' => 'x'], ['key' => 'FAX', 'value' => '1']]], ['FAX' => '1']];
    }

    /**
     * @param array<string, string> $expected
     */
    #[Test]
    #[DataProvider('metadata')]
    public function itReadsMetadataAsMap(mixed $wire, array $expected): void
    {
        $doc = InboxDocument::fromArray([...RequiredFields::of(InboxDocument::class), 'metadata' => $wire]);

        self::assertSame($expected, $doc->metadata);
    }

    #[Test]
    public function toArrayWritesMetadataInWireFormat(): void
    {
        $doc = InboxDocument::fromArray([...RequiredFields::of(InboxDocument::class), 'metadata' => ['data' => ['key' => 'WWW', 'value' => 'example.com']]]);

        self::assertSame(['data' => [['key' => 'WWW', 'value' => 'example.com']]], $doc->toArray()['metadata']);
        self::assertSame('', InboxDocument::fromArray(RequiredFields::of(InboxDocument::class))->toArray()['metadata']);
    }

    #[Test]
    public function itReadsDocumentTypeAndKeepsUnknownFields(): void
    {
        $doc = InboxDocument::fromArray([...RequiredFields::of(InboxDocument::class), 'document_type' => 'FUEL_RECEIPT', 'new_field' => 'x']);

        self::assertSame(InboxDocumentType::FUEL_RECEIPT, $doc->documentType);
        self::assertSame(['new_field' => 'x'], $doc->extra);
        self::assertSame('x', $doc->toArray()['new_field']);
        self::assertNull(InboxDocument::fromArray([...RequiredFields::of(InboxDocument::class), 'document_type' => ''])->documentType);
    }
}
