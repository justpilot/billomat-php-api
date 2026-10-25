<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Options;

use BackedEnum;
use DateTimeImmutable;
use Justpilot\Billomat\Api\Clear;
use Justpilot\Billomat\Api\WriteOptions;
use Justpilot\Billomat\Model\Money;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionProperty;
use ReflectionUnionType;

/**
 * Prüft alle generierten Write-Options per Reflection gegen die `Billomat-Feld`-Angaben im Docblock:
 * jedes gesetzte Feld landet unter seinem Billomat-Namen im Payload, ungesetzte fehlen,
 * `Clear::Field` wird als null gesendet.
 */
#[CoversNamespace('Justpilot\\Billomat\\Api')]
#[UsesClass(Money::class)]
final class GeneratedOptionsTest extends TestCase
{
    private int $counter = 0;

    /**
     * @param class-string<WriteOptions> $class
     */
    #[Test]
    #[DataProvider('options')]
    public function everySetFieldIsSentUnderItsBillomatName(string $class): void
    {
        $arguments = [];
        foreach ($this->parameters($class) as $parameter) {
            $arguments[$parameter->name] = $this->sampleValue($parameter);
        }

        $payload = new $class(...$arguments)->toArray();

        $expected = [];
        foreach ($arguments as $name => $value) {
            $expected[$this->wireName($class, $name)] = $this->exported($value);
        }
        ksort($expected);
        ksort($payload);
        self::assertSame($expected, $this->normalizeBools($payload, $expected));
    }

    /**
     * @param class-string<WriteOptions> $class
     */
    #[Test]
    #[DataProvider('options')]
    public function unsetFieldsAreNotSent(string $class): void
    {
        $arguments = [];
        $expected = [];
        foreach ($this->parameters($class) as $parameter) {
            if ($parameter->isDefaultValueAvailable()) {
                $default = $parameter->getDefaultValue();
                // Bool-Defaults (z. B. `markInvoiceAsPaid = false`) werden immer gesendet.
                if (\is_bool($default)) {
                    $expected[$this->wireName($class, $parameter->name)] = $default;
                }

                continue;
            }
            $arguments[$parameter->name] = $this->sampleValue($parameter);
            $expected[$this->wireName($class, $parameter->name)] = $this->exported($arguments[$parameter->name]);
        }

        $payload = new $class(...$arguments)->toArray();

        ksort($expected);
        ksort($payload);
        self::assertSame($expected, $this->normalizeBools($payload, $expected));
    }

    /**
     * @param class-string<WriteOptions> $class
     */
    #[Test]
    #[DataProvider('options')]
    public function falseBoolsAreSent(string $class): void
    {
        $arguments = [];
        $expected = [];
        foreach ($this->parameters($class) as $parameter) {
            $type = $parameter->getType();
            if ($type instanceof ReflectionNamedType && 'bool' === $type->getName()) {
                $arguments[$parameter->name] = false;
                $expected[$this->wireName($class, $parameter->name)] = false;
            } elseif (!$parameter->isDefaultValueAvailable()) {
                $arguments[$parameter->name] = $this->sampleValue($parameter);
                $expected[$this->wireName($class, $parameter->name)] = $this->exported($arguments[$parameter->name]);
            }
        }

        $payload = new $class(...$arguments)->toArray();

        ksort($expected);
        ksort($payload);
        self::assertSame($expected, $this->normalizeBools($payload, $expected));
    }

    /**
     * @param class-string<WriteOptions> $class
     */
    #[Test]
    #[DataProvider('documentOptions')]
    public function withItemAppendsToExistingItems(string $class): void
    {
        $arguments = [];
        foreach ($this->parameters($class) as $parameter) {
            $arguments[$parameter->name] = $this->sampleValue($parameter);
        }
        $options = new $class(...$arguments);
        $first = $arguments['items'];
        self::assertIsArray($first);
        $second = $this->sampleItems($class, 'items');
        $items = new ReflectionProperty($class, 'items');

        $copy = new ReflectionMethod($class, 'withItem')->invoke($options, ...$second);

        self::assertSame([...$first, ...$second], $items->getValue($copy));
        self::assertSame($first, $items->getValue($options));
    }

    /**
     * @param class-string<WriteOptions> $class
     */
    #[Test]
    #[DataProvider('updateOptions')]
    public function clearedFieldsAreSentAsNull(string $class): void
    {
        $arguments = [];
        $expected = [];
        foreach ($this->parameters($class) as $parameter) {
            $type = $parameter->getType();
            $types = $type instanceof ReflectionUnionType ? $type->getTypes() : [$type];
            foreach ($types as $t) {
                if ($t instanceof ReflectionNamedType && Clear::class === $t->getName()) {
                    $arguments[$parameter->name] = Clear::Field;
                    $expected[$this->wireName($class, $parameter->name)] = null;
                }
            }
        }

        self::assertNotSame([], $arguments, $class.' hat keine leerbaren Felder.');
        $payload = new $class(...$arguments)->toArray();

        ksort($expected);
        ksort($payload);
        self::assertSame($expected, $payload);
    }

    /**
     * @return iterable<string, array{class-string<WriteOptions>}>
     */
    public static function options(): iterable
    {
        foreach ((array) glob(__DIR__.'/../../../src/Api/*Options.php') as $file) {
            if (!str_contains((string) file_get_contents((string) $file), 'Generiert aus')) {
                continue;
            }
            $name = basename((string) $file, '.php');
            /** @var class-string<WriteOptions> $class */
            $class = 'Justpilot\\Billomat\\Api\\'.$name;

            yield $name => [$class];
        }
    }

    /**
     * @return iterable<string, array{class-string<WriteOptions>}>
     */
    public static function documentOptions(): iterable
    {
        foreach (self::options() as $name => $arguments) {
            if (method_exists($arguments[0], 'withItem')) {
                yield $name => $arguments;
            }
        }
    }

    /**
     * @return iterable<string, array{class-string<WriteOptions>}>
     */
    public static function updateOptions(): iterable
    {
        foreach (self::options() as $name => $arguments) {
            if (str_ends_with($name, 'UpdateOptions')) {
                yield $name => $arguments;
            }
        }
    }

    /**
     * @param class-string<WriteOptions> $class
     *
     * @return list<ReflectionParameter>
     */
    private function parameters(string $class): array
    {
        return new ReflectionClass($class)->getConstructor()?->getParameters() ?? [];
    }

    /**
     * Billomat-Feldname laut Docblock; Positionen liegen als `{resource}-items` im Payload.
     *
     * @param class-string<WriteOptions> $class
     */
    private function wireName(string $class, string $property): string
    {
        $docComment = (string) new ReflectionProperty($class, $property)->getDocComment();
        if (1 === preg_match('/Billomat-Feld: (\S+)/', $docComment, $match)) {
            return $match[1];
        }
        if (1 === preg_match('/@var list<(\w+)ItemCreateOptions>/', $docComment, $match)) {
            return $this->kebab($match[1]).'-items';
        }

        self::fail($class.'::$'.$property.' nennt kein Billomat-Feld.');
    }

    /**
     * Ein eigener Wert pro Feld, damit vertauschte Felder auffallen (nie null oder Clear).
     */
    private function sampleValue(ReflectionParameter $parameter): mixed
    {
        $type = $parameter->getType();
        $types = $type instanceof ReflectionUnionType ? $type->getTypes() : [$type];
        $named = array_values(array_filter(
            $types,
            static fn (mixed $t): bool => $t instanceof ReflectionNamedType && !\in_array($t->getName(), ['null', Clear::class], true),
        ));
        $name = $named[0] instanceof ReflectionNamedType ? $named[0]->getName() : 'mixed';
        $n = ++$this->counter;

        return match (true) {
            'int' === $name => $n,
            'float' === $name => $n + 0.5,
            'string' === $name, 'mixed' === $name => 'wert-'.$parameter->name,
            // Gegenteil des Defaults, damit der Wert sichtbar wird.
            'bool' === $name => !$parameter->isDefaultValueAvailable() || true !== $parameter->getDefaultValue(),
            'array' === $name => $this->sampleItems($parameter->getDeclaringClass()?->getName() ?? '', $parameter->name),
            DateTimeImmutable::class === $name => new DateTimeImmutable('2026-01-01')->modify('+'.$n.' days'),
            Money::class === $name => Money::of($n.'.25'),
            is_subclass_of($name, BackedEnum::class) => array_last($name::cases()),
            default => self::fail('Kein Beispielwert für Typ '.$name),
        };
    }

    /**
     * @return list<WriteOptions>
     */
    private function sampleItems(string $class, string $property): array
    {
        $docComment = (string) new ReflectionProperty($class, $property)->getDocComment();
        if (1 !== preg_match('/@var list<(\w+)>/', $docComment, $match)) {
            self::fail($class.'::$'.$property.' hat keinen Listentyp.');
        }
        /** @var class-string<WriteOptions> $itemClass */
        $itemClass = 'Justpilot\\Billomat\\Api\\'.$match[1];
        $arguments = [];
        foreach ($this->parameters($itemClass) as $itemParameter) {
            $arguments[$itemParameter->name] = $this->sampleValue($itemParameter);
        }

        return [new $itemClass(...$arguments)];
    }

    private function exported(mixed $value): mixed
    {
        return match (true) {
            $value instanceof Money => $value->toString(),
            $value instanceof BackedEnum => $value->value,
            $value instanceof DateTimeImmutable => $value->format('Y-m-d'),
            \is_array($value) && [] !== $value && $value[0] instanceof WriteOptions => [
                $this->kebab(substr(new ReflectionClass($value[0])->getShortName(), 0, -\strlen('CreateOptions'))) => array_map(
                    static fn (WriteOptions $item): array => $item->toArray(),
                    $value,
                ),
            ],
            default => $value,
        };
    }

    /**
     * Billomat erwartet Bools teils als 0/1; der Test vergleicht nur den Wahrheitswert.
     *
     * @param array<mixed> $payload
     * @param array<mixed> $expected
     *
     * @return array<mixed>
     */
    private function normalizeBools(array $payload, array $expected): array
    {
        foreach ($expected as $key => $value) {
            if (\is_bool($value) && \in_array($payload[$key] ?? null, [0, 1], true)) {
                $payload[$key] = 1 === $payload[$key];
            }
        }

        return $payload;
    }

    private function kebab(string $name): string
    {
        return strtolower((string) preg_replace('/(?<!^)[A-Z]/', '-$0', $name));
    }
}
