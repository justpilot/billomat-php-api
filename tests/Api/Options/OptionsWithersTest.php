<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use BackedEnum;
use DateTimeImmutable;
use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Api\ArticlePropertyValueCreateOptions;
use Justpilot\Billomat\Api\ArticleTagCreateOptions;
use Justpilot\Billomat\Api\ArticleUpdateOptions;
use Justpilot\Billomat\Api\Clear;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ClientPropertyValueCreateOptions;
use Justpilot\Billomat\Api\ClientTagCreateOptions;
use Justpilot\Billomat\Api\ClientUpdateOptions;
use Justpilot\Billomat\Api\ConfirmationCommentCreateOptions;
use Justpilot\Billomat\Api\ConfirmationCreateOptions;
use Justpilot\Billomat\Api\ConfirmationItemCreateOptions;
use Justpilot\Billomat\Api\ConfirmationTagCreateOptions;
use Justpilot\Billomat\Api\ConfirmationUpdateOptions;
use Justpilot\Billomat\Api\ContactCreateOptions;
use Justpilot\Billomat\Api\ContactUpdateOptions;
use Justpilot\Billomat\Api\CreditNoteCommentCreateOptions;
use Justpilot\Billomat\Api\CreditNoteCreateOptions;
use Justpilot\Billomat\Api\CreditNoteItemCreateOptions;
use Justpilot\Billomat\Api\CreditNotePaymentCreateOptions;
use Justpilot\Billomat\Api\CreditNoteTagCreateOptions;
use Justpilot\Billomat\Api\CreditNoteUpdateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCommentCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteItemCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteTagCreateOptions;
use Justpilot\Billomat\Api\DeliveryNoteUpdateOptions;
use Justpilot\Billomat\Api\EmailOptions;
use Justpilot\Billomat\Api\InboxDocumentCreateOptions;
use Justpilot\Billomat\Api\IncomingCommentCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingPaymentCreateOptions;
use Justpilot\Billomat\Api\IncomingPropertyValueCreateOptions;
use Justpilot\Billomat\Api\IncomingTagCreateOptions;
use Justpilot\Billomat\Api\IncomingUpdateOptions;
use Justpilot\Billomat\Api\InvoiceCommentCreateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Api\InvoiceItemCreateOptions;
use Justpilot\Billomat\Api\InvoiceMailOptions;
use Justpilot\Billomat\Api\InvoicePaymentCreateOptions;
use Justpilot\Billomat\Api\InvoiceTagCreateOptions;
use Justpilot\Billomat\Api\InvoiceUpdateOptions;
use Justpilot\Billomat\Api\LetterCommentCreateOptions;
use Justpilot\Billomat\Api\LetterCreateOptions;
use Justpilot\Billomat\Api\LetterTagCreateOptions;
use Justpilot\Billomat\Api\LetterUpdateOptions;
use Justpilot\Billomat\Api\OfferCommentCreateOptions;
use Justpilot\Billomat\Api\OfferCreateOptions;
use Justpilot\Billomat\Api\OfferItemCreateOptions;
use Justpilot\Billomat\Api\OfferTagCreateOptions;
use Justpilot\Billomat\Api\OfferUpdateOptions;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Api\RecurringCreateOptions;
use Justpilot\Billomat\Api\RecurringEmailReceiverCreateOptions;
use Justpilot\Billomat\Api\RecurringItemCreateOptions;
use Justpilot\Billomat\Api\RecurringTagCreateOptions;
use Justpilot\Billomat\Api\RecurringUpdateOptions;
use Justpilot\Billomat\Api\ReminderCreateOptions;
use Justpilot\Billomat\Api\ReminderTagCreateOptions;
use Justpilot\Billomat\Api\ReminderUpdateOptions;
use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\SupplierTagCreateOptions;
use Justpilot\Billomat\Api\SupplierUpdateOptions;
use Justpilot\Billomat\Api\TaxRateCreateOptions;
use Justpilot\Billomat\Api\TemplateCreateOptions;
use Justpilot\Billomat\Api\TemplateUpdateOptions;
use Justpilot\Billomat\Api\WriteOptions;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use UnitEnum;

/**
 * Prüft alle with*()-Methoden der Options per Reflection: Kopie mit geändertem Feld, Original unverändert.
 *
 * Neue Options-Klassen mit Withern müssen hier als CoversClass eingetragen werden – sonst schlägt
 * der Test wegen strikter Coverage-Metadaten fehl.
 */
#[CoversClass(ArticleCreateOptions::class)]
#[CoversClass(ArticlePropertyValueCreateOptions::class)]
#[CoversClass(ArticleTagCreateOptions::class)]
#[CoversClass(ArticleUpdateOptions::class)]
#[CoversClass(ClientCreateOptions::class)]
#[CoversClass(ClientPropertyValueCreateOptions::class)]
#[CoversClass(ClientTagCreateOptions::class)]
#[CoversClass(ClientUpdateOptions::class)]
#[CoversClass(ConfirmationCommentCreateOptions::class)]
#[CoversClass(ConfirmationCreateOptions::class)]
#[CoversClass(ConfirmationItemCreateOptions::class)]
#[CoversClass(ConfirmationTagCreateOptions::class)]
#[CoversClass(ConfirmationUpdateOptions::class)]
#[CoversClass(ContactCreateOptions::class)]
#[CoversClass(ContactUpdateOptions::class)]
#[CoversClass(CreditNoteCommentCreateOptions::class)]
#[CoversClass(CreditNoteCreateOptions::class)]
#[CoversClass(CreditNoteItemCreateOptions::class)]
#[CoversClass(CreditNotePaymentCreateOptions::class)]
#[CoversClass(CreditNoteTagCreateOptions::class)]
#[CoversClass(CreditNoteUpdateOptions::class)]
#[CoversClass(DeliveryNoteCommentCreateOptions::class)]
#[CoversClass(DeliveryNoteCreateOptions::class)]
#[CoversClass(DeliveryNoteItemCreateOptions::class)]
#[CoversClass(DeliveryNoteTagCreateOptions::class)]
#[CoversClass(DeliveryNoteUpdateOptions::class)]
#[CoversClass(EmailOptions::class)]
#[CoversClass(InboxDocumentCreateOptions::class)]
#[CoversClass(IncomingCommentCreateOptions::class)]
#[CoversClass(IncomingCreateOptions::class)]
#[CoversClass(IncomingPaymentCreateOptions::class)]
#[CoversClass(IncomingPropertyValueCreateOptions::class)]
#[CoversClass(IncomingTagCreateOptions::class)]
#[CoversClass(IncomingUpdateOptions::class)]
#[CoversClass(InvoiceCommentCreateOptions::class)]
#[CoversClass(InvoiceCreateOptions::class)]
#[CoversClass(InvoiceItemCreateOptions::class)]
#[CoversClass(InvoiceMailOptions::class)]
#[CoversClass(InvoicePaymentCreateOptions::class)]
#[CoversClass(InvoiceTagCreateOptions::class)]
#[CoversClass(InvoiceUpdateOptions::class)]
#[CoversClass(LetterCommentCreateOptions::class)]
#[CoversClass(LetterCreateOptions::class)]
#[CoversClass(LetterTagCreateOptions::class)]
#[CoversClass(LetterUpdateOptions::class)]
#[CoversClass(OfferCommentCreateOptions::class)]
#[CoversClass(OfferCreateOptions::class)]
#[CoversClass(OfferItemCreateOptions::class)]
#[CoversClass(OfferTagCreateOptions::class)]
#[CoversClass(OfferUpdateOptions::class)]
#[CoversClass(PropertyCreateOptions::class)]
#[CoversClass(RecurringCreateOptions::class)]
#[CoversClass(RecurringEmailReceiverCreateOptions::class)]
#[CoversClass(RecurringItemCreateOptions::class)]
#[CoversClass(RecurringTagCreateOptions::class)]
#[CoversClass(RecurringUpdateOptions::class)]
#[CoversClass(ReminderCreateOptions::class)]
#[CoversClass(ReminderTagCreateOptions::class)]
#[CoversClass(ReminderUpdateOptions::class)]
#[CoversClass(SettingsUpdateOptions::class)]
#[CoversClass(SupplierCreateOptions::class)]
#[CoversClass(SupplierPropertyValueCreateOptions::class)]
#[CoversClass(SupplierTagCreateOptions::class)]
#[CoversClass(SupplierUpdateOptions::class)]
#[CoversClass(TaxRateCreateOptions::class)]
#[CoversClass(TemplateCreateOptions::class)]
#[CoversClass(TemplateUpdateOptions::class)]
final class OptionsWithersTest extends TestCase
{
    #[Test]
    #[DataProvider('withers')]
    public function witherReturnsChangedCopyAndKeepsOriginal(string $class, string $method, string $property): void
    {
        $original = $this->instantiate($class);
        $wither = new ReflectionMethod($class, $method);
        $parameter = $wither->getParameters()[0];
        $reflectionProperty = new ReflectionClass($class)->getProperty($property);
        $before = $reflectionProperty->getValue($original);
        $value = $this->sampleValue($parameter->getType(), $parameter, $before);

        $copy = $wither->invoke($original, $value);

        self::assertTrue($wither->isPublic());
        self::assertNotSame($before, $value);
        self::assertInstanceOf($class, $copy);
        self::assertNotSame($original, $copy);
        self::assertSame($value, $reflectionProperty->getValue($copy));
        self::assertSame($before, $reflectionProperty->getValue($original));
    }

    /**
     * @return iterable<string, array{class-string<WriteOptions>, string, string}>
     */
    public static function withers(): iterable
    {
        foreach ((array) glob(__DIR__.'/../../../src/Api/*Options.php') as $file) {
            /** @var class-string<WriteOptions> $class */
            $class = 'Justpilot\\Billomat\\Api\\'.basename($file, '.php');
            foreach (new ReflectionClass($class)->getMethods() as $method) {
                // withItem() hängt an statt zu ersetzen und hat einen eigenen Test.
                if (!str_starts_with($method->name, 'with') || 'withItem' === $method->name) {
                    continue;
                }

                $property = lcfirst(substr($method->name, 4));
                yield basename($file, '.php').'::'.$method->name => [$class, $method->name, $property];
            }
        }
    }

    /**
     * @param class-string<WriteOptions> $class
     */
    private function instantiate(string $class): WriteOptions
    {
        $arguments = [];
        foreach (new ReflectionClass($class)->getConstructor()?->getParameters() ?? [] as $parameter) {
            if (!$parameter->isDefaultValueAvailable()) {
                $arguments[$parameter->name] = $this->sampleValue($parameter->getType(), $parameter);
            }
        }

        return new $class(...$arguments);
    }

    /**
     * Ein Wert, der sich vom Default und von `$avoid` unterscheidet (nie null oder Clear).
     */
    private function sampleValue(?ReflectionType $type, ReflectionParameter $parameter, mixed $avoid = null): mixed
    {
        $types = $type instanceof ReflectionUnionType ? $type->getTypes() : [$type];
        $named = array_values(array_filter(
            $types,
            static fn (?ReflectionType $t): bool => $t instanceof ReflectionNamedType && !\in_array($t->getName(), ['null', Clear::class], true),
        ));
        $name = $named[0] instanceof ReflectionNamedType ? $named[0]->getName() : 'mixed';

        $value = match (true) {
            'int' === $name => 7,
            'float' === $name => 1.5,
            'string' === $name, 'mixed' === $name => 'wert-'.$parameter->name,
            'bool' === $name => !$parameter->isDefaultValueAvailable() || true !== $parameter->getDefaultValue(),
            'array' === $name => ['wert'],
            DateTimeImmutable::class === $name => new DateTimeImmutable('2026-01-02'),
            is_subclass_of($name, UnitEnum::class) => $this->lastCase($name),
            default => self::fail('Kein Beispielwert für Typ '.$name),
        };

        // Pflichtfelder tragen schon den Beispielwert – dann einen anderen nehmen.
        if ($value !== $avoid) {
            return $value;
        }

        return match (true) {
            \is_int($value) => 8,
            \is_float($value) => 2.5,
            \is_string($value) => $value.'-neu',
            \is_bool($value) => !$value,
            \is_array($value) => ['neu'],
            $value instanceof UnitEnum => $name::cases()[0],
            default => self::fail('Kein zweiter Beispielwert für Typ '.$name),
        };
    }

    /**
     * @param class-string<UnitEnum> $enum
     */
    private function lastCase(string $enum): UnitEnum|BackedEnum
    {
        return array_last($enum::cases()) ?? self::fail($enum.' hat keine Cases.');
    }
}
