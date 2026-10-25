<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use BackedEnum;
use DateTimeImmutable;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesNamespace;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;

/**
 * Prüft alle Read-Models mit `fromArray()` und `toArray()` per Reflection: `fromArray(toArray())`
 * ergibt dasselbe Model – mit einem eigenen Wert pro Feld und mit leeren Werten (null, false) –,
 * und fehlende Pflichtfelder fallen auf den Leerwert ihres Typs zurück.
 *
 * Deckt damit jedes Feld-Mapping des Generators ab, ohne pro Model einen eigenen Test zu pflegen.
 */
#[CoversNamespace('Justpilot\\Billomat\\Model')]
#[UsesNamespace('Justpilot\\Billomat\\Internal')]
final class ModelRoundTripTest extends TestCase
{
    private int $counter = 0;

    /**
     * @param class-string $class
     */
    #[Test]
    #[DataProvider('models')]
    public function roundTripKeepsEveryField(string $class): void
    {
        $model = $this->sample($class, empty: false);
        $data = $this->export($model);

        self::assertSame(array_filter(array_keys($data), is_string(...)), array_keys($data));
        self::assertEquals($model, $class::fromArray($data));
    }

    /**
     * @param class-string $class
     */
    #[Test]
    #[DataProvider('models')]
    public function roundTripKeepsEmptyValues(string $class): void
    {
        $model = $this->sample($class, empty: true);

        self::assertEquals($model, $class::fromArray($this->export($model)));
    }

    /**
     * @param class-string $class
     */
    #[Test]
    #[DataProvider('generatedModels')]
    public function unknownFieldsAreKeptInExtra(string $class): void
    {
        $known = $this->export($this->sample($class, empty: false));
        $data = [...$known, 'zz_neues_feld' => 'wert', 'zz_neue_liste' => ['a' => '1']];

        $model = $class::fromArray($data);
        self::assertIsObject($model);
        $exported = $this->export($model);

        self::assertSame(['zz_neues_feld' => 'wert', 'zz_neue_liste' => ['a' => '1']], new ReflectionProperty($class, 'extra')->getValue($model));
        self::assertSame('wert', $exported['zz_neues_feld']);
        self::assertSame(['a' => '1'], $exported['zz_neue_liste']);
        // Abgebildete Felder überschreiben gleichnamige Einträge in `extra`.
        self::assertEquals($known, array_diff_key($exported, ['zz_neues_feld' => true, 'zz_neue_liste' => true]));
    }

    /**
     * @param class-string $class
     */
    #[Test]
    #[DataProvider('generatedModels')]
    public function missingFieldsFallBackToEmptyValues(string $class): void
    {
        $model = $class::fromArray([]);
        self::assertIsObject($model);

        foreach (new ReflectionClass($class)->getConstructor()?->getParameters() ?? [] as $parameter) {
            $type = $parameter->getType();
            $value = new ReflectionProperty($class, $parameter->name)->getValue($model);
            if (!$type instanceof ReflectionNamedType || $type->allowsNull()) {
                self::assertNull($value, $class.'::$'.$parameter->name);

                continue;
            }

            $expected = match ($type->getName()) {
                'int' => 0,
                'float' => 0.0,
                'string' => '',
                'bool' => false,
                'array' => [],
                Money::class => Money::zero(),
                default => self::fail('Kein Leerwert für Typ '.$type->getName()),
            };
            self::assertEquals($expected, $value, $class.'::$'.$parameter->name);
        }
    }

    /**
     * @return iterable<string, array{class-string}>
     */
    public static function models(): iterable
    {
        foreach ((array) glob(__DIR__.'/../../src/Model/*.php') as $file) {
            $name = basename((string) $file, '.php');
            $class = 'Justpilot\\Billomat\\Model\\'.$name;
            // Grenzt den Namen für PHPStan auf class-string ein.
            if (!class_exists($class)) {
                continue;
            }
            // Account liest den Client flach aus der Antwort, toArray() verschachtelt ihn unter `client`.
            if (!method_exists($class, 'fromArray') || !method_exists($class, 'toArray') || 'Account' === $name) {
                continue;
            }

            yield $name => [$class];
        }
    }

    /**
     * Die Leerwert-Regel gilt für generierte Models; handgeschriebene haben teils eigene Defaults.
     *
     * @return iterable<string, array{class-string}>
     */
    public static function generatedModels(): iterable
    {
        foreach (self::models() as $name => $arguments) {
            if (str_contains((string) file_get_contents(__DIR__.'/../../src/Model/'.$name.'.php'), 'Generiert aus')) {
                yield $name => $arguments;
            }
        }
    }

    /**
     * Baut ein Model mit einem eigenen Wert pro Feld, damit vertauschte Felder auffallen.
     * Mit `$empty` sind optionale Felder null, Bools false und Listen leer.
     *
     * @param class-string $class
     */
    private function sample(string $class, bool $empty): object
    {
        $constructor = new ReflectionClass($class)->getConstructor();
        $docComment = (string) $constructor?->getDocComment();
        $arguments = [];
        foreach ($constructor?->getParameters() ?? [] as $parameter) {
            // `fooRaw` hält den Rohwert des Enums `foo` und wird aus demselben Feld gelesen.
            $enum = $arguments[substr($parameter->name, 0, -3)] ?? null;
            $arguments[$parameter->name] = match (true) {
                str_ends_with($parameter->name, 'Raw') && $enum instanceof BackedEnum => $enum->value,
                $empty => $this->emptyValue($parameter),
                default => $this->sampleValue($parameter, $docComment),
            };
        }

        return new $class(...$arguments);
    }

    private function sampleValue(ReflectionParameter $parameter, string $docComment): mixed
    {
        $type = $parameter->getType();
        $name = $type instanceof ReflectionNamedType ? $type->getName() : 'mixed';
        $n = ++$this->counter;

        return match (true) {
            'int' === $name => $n,
            'float' === $name => $n + 0.5,
            'string' === $name, 'mixed' === $name => 'wert-'.$parameter->name,
            'bool' === $name => true,
            'array' === $name => $this->sampleList($parameter, $docComment),
            DateTimeImmutable::class === $name => new DateTimeImmutable('2026-01-01')->modify('+'.$n.' days'),
            Money::class === $name => Money::of($n.'.25'),
            is_subclass_of($name, BackedEnum::class) => array_last($name::cases()),
            default => self::fail('Kein Beispielwert für Typ '.$name),
        };
    }

    private function emptyValue(ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();
        $name = $type instanceof ReflectionNamedType ? $type->getName() : 'mixed';

        return match (true) {
            'bool' === $name => false,
            'array' === $name => [],
            null === $type || $type->allowsNull() => null,
            default => $this->sampleValue($parameter, ''),
        };
    }

    /**
     * @return array<mixed>
     */
    private function export(object $model): array
    {
        $data = new ReflectionMethod($model, 'toArray')->invoke($model);
        self::assertIsArray($data);

        return $data;
    }

    /**
     * @return array<mixed>
     */
    private function sampleList(ReflectionParameter $parameter, string $docComment): array
    {
        // Verschachtelte Models: Typ aus `@param list<XItem> $items` bzw. `list<TaxSummary> $taxes` des Konstruktors.
        if (1 === preg_match('/@param\s+list<(\w+)>\s+\$'.$parameter->name.'\b/', $docComment, $match)
            && class_exists($itemClass = 'Justpilot\\Billomat\\Model\\'.$match[1])) {
            return [$this->sample($itemClass, empty: false)];
        }
        // Enum-Listen (z. B. `list<PaymentType> $paymentTypes`): zwei Werte, damit die Kommatrennung geprüft wird.
        if (1 === preg_match('/@param\s+list<(\w+)>\s+\$'.$parameter->name.'\b/', $docComment, $match)
            && is_subclass_of($enumClass = 'Justpilot\\Billomat\\Model\\Enum\\'.$match[1], BackedEnum::class)) {
            return [array_first($enumClass::cases()), array_last($enumClass::cases())];
        }
        // Enum-Maps (z. B. `array<string, AccessLevel> $rights`): echte Bereichsnamen, damit auch Role sie erkennt.
        if (1 === preg_match('/@param\s+array<string,\s*(\w+)>\s+\$'.$parameter->name.'\b/', $docComment, $match)
            && is_subclass_of($enumClass = 'Justpilot\\Billomat\\Model\\Enum\\'.$match[1], BackedEnum::class)) {
            return ['articles' => array_first($enumClass::cases()), 'settings_addons' => array_last($enumClass::cases())];
        }

        return [];
    }
}
