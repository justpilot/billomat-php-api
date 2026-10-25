<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Api\Filters;

use BackedEnum;
use DateTimeImmutable;
use Justpilot\Billomat\Api\ArticleFilter;
use Justpilot\Billomat\Api\ClientFilter;
use Justpilot\Billomat\Api\ConfirmationFilter;
use Justpilot\Billomat\Api\CreditNoteFilter;
use Justpilot\Billomat\Api\DeliveryNoteFilter;
use Justpilot\Billomat\Api\IncomingFilter;
use Justpilot\Billomat\Api\InvoiceFilter;
use Justpilot\Billomat\Api\LetterFilter;
use Justpilot\Billomat\Api\ListFilter;
use Justpilot\Billomat\Api\OfferFilter;
use Justpilot\Billomat\Api\OrderBy;
use Justpilot\Billomat\Api\RecurringFilter;
use Justpilot\Billomat\Api\ReminderFilter;
use Justpilot\Billomat\Api\SupplierFilter;
use Justpilot\Billomat\Api\TemplateFilter;
use Justpilot\Billomat\Api\UserFilter;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\PaymentType;
use Justpilot\Billomat\Model\Enum\RecurringCycle;
use Justpilot\Billomat\Model\Enum\TemplateDocumentType;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;
use ReflectionClass;
use ReflectionMethod;
use ReflectionNamedType;
use ReflectionParameter;
use ReflectionType;
use ReflectionUnionType;
use UnitEnum;

#[CoversClass(ArticleFilter::class)]
#[CoversClass(ClientFilter::class)]
#[CoversClass(ConfirmationFilter::class)]
#[CoversClass(CreditNoteFilter::class)]
#[CoversClass(DeliveryNoteFilter::class)]
#[CoversClass(IncomingFilter::class)]
#[CoversClass(InvoiceFilter::class)]
#[CoversClass(LetterFilter::class)]
#[CoversClass(OfferFilter::class)]
#[CoversClass(RecurringFilter::class)]
#[CoversClass(ReminderFilter::class)]
#[CoversClass(SupplierFilter::class)]
#[CoversClass(TemplateFilter::class)]
#[CoversClass(UserFilter::class)]
#[UsesClass(OrderBy::class)]
final class ListFiltersTest extends TestCase
{
    #[Test]
    public function emptyFilterSendsNothing(): void
    {
        self::assertSame([], new InvoiceFilter()->toQuery());
    }

    #[Test]
    public function serializesAllFieldKinds(): void
    {
        $filter = new InvoiceFilter(
            clientId: 7,
            invoiceNumber: 'RE-1',
            status: [InvoiceStatus::OPEN, InvoiceStatus::OVERDUE],
            paymentType: [PaymentType::CASH],
            from: new DateTimeImmutable('2026-01-01 13:45'),
            to: new DateTimeImmutable('2026-03-31'),
            tags: ['vip', 'b2b'],
            orderBy: OrderBy::desc('date')->thenAsc('invoice_number'),
            page: 2,
            perPage: 50,
        );

        self::assertSame([
            'client_id' => 7,
            'invoice_number' => 'RE-1',
            'status' => 'OPEN,OVERDUE',
            'payment_type' => 'CASH',
            'from' => '2026-01-01',
            'to' => '2026-03-31',
            'tags' => 'vip,b2b',
            'order_by' => 'date+DESC,invoice_number+ASC',
            'page' => 2,
            'per_page' => 50,
        ], $filter->toQuery());
    }

    #[Test]
    public function serializesIdListsSingleEnumsAndBools(): void
    {
        self::assertSame(['invoice_id' => '1,2,3'], new ClientFilter(invoiceId: [1, 2, 3])->toQuery());
        self::assertSame(['cycle' => 'MONTHLY'], new RecurringFilter(cycle: RecurringCycle::MONTHLY)->toQuery());
        self::assertSame(['is_default' => 1], new TemplateFilter(isDefault: true)->toQuery());
        self::assertSame(['is_default' => 0], new TemplateFilter(isDefault: false)->toQuery());
        self::assertSame(['type' => 'INVOICE,OFFER'], new TemplateFilter(type: [TemplateDocumentType::INVOICE, TemplateDocumentType::OFFER])->toQuery());
    }

    #[Test]
    public function extraParametersAreAddedAndOverrideFields(): void
    {
        $filter = new ClientFilter(name: 'ACME', extra: ['name' => 'Override', 'client_property_7' => 'x']);

        self::assertSame(['name' => 'Override', 'client_property_7' => 'x'], $filter->toQuery());
        self::assertSame(['name' => 'ACME', 'a' => 1], $filter->withExtra(['a' => 1])->toQuery());
    }

    #[Test]
    public function listWithersTakeVariadicValues(): void
    {
        $filter = new InvoiceFilter()->withStatus(InvoiceStatus::PAID, InvoiceStatus::CANCELED)->withTags('a', 'b');

        self::assertSame([InvoiceStatus::PAID, InvoiceStatus::CANCELED], $filter->status);
        self::assertSame(['status' => 'PAID,CANCELED', 'tags' => 'a,b'], $filter->toQuery());
    }

    /**
     * @param class-string<ListFilter> $class
     */
    #[Test]
    #[DataProvider('withers')]
    public function witherReturnsChangedCopyAndKeepsOriginal(string $class, string $method, string $property): void
    {
        $original = new $class();
        $wither = new ReflectionMethod($class, $method);
        $parameter = $wither->getParameters()[0];
        $reflectionProperty = new ReflectionClass($class)->getProperty($property);
        $before = $reflectionProperty->getValue($original);
        $value = $this->sampleValue($parameter->getType(), $parameter);
        // Listen-Wither sind variadisch und speichern die Werte als Liste.
        $expected = $parameter->isVariadic() ? [$value] : $value;

        $copy = $wither->invoke($original, $value);

        self::assertTrue($wither->isPublic());
        self::assertNotSame($before, $expected);
        self::assertInstanceOf($class, $copy);
        self::assertNotSame($original, $copy);
        self::assertSame($expected, $reflectionProperty->getValue($copy));
        self::assertSame($before, $reflectionProperty->getValue($original));
        if ($parameter->isVariadic()) {
            // Benannte Argumente landen mit String-Schlüssel im Variadic – gespeichert wird trotzdem eine Liste.
            self::assertSame($expected, $reflectionProperty->getValue($wither->invokeArgs($original, ['beliebig' => $value])));
        }
        // Ein leerer Filter sendet nichts, die Kopie genau den geänderten Parameter.
        $wire = 'extra' === $property ? 'extra_param' : strtolower((string) preg_replace('/[A-Z]/', '_$0', $property));
        self::assertSame([], $original->toQuery());
        self::assertSame([$wire], array_keys($copy->toQuery()));
    }

    /**
     * @return iterable<string, array{class-string<ListFilter>, string, string}>
     */
    public static function withers(): iterable
    {
        foreach ((array) glob(__DIR__.'/../../../src/Api/*Filter.php') as $file) {
            $name = basename((string) $file, '.php');
            if ('ListFilter' === $name) {
                continue;
            }
            /** @var class-string<ListFilter> $class */
            $class = 'Justpilot\\Billomat\\Api\\'.$name;
            // Alle Methoden, auch nicht-öffentliche: ein Wither muss public sein.
            foreach (new ReflectionClass($class)->getMethods() as $method) {
                if (!str_starts_with($method->name, 'with')) {
                    continue;
                }

                yield $name.'::'.$method->name => [$class, $method->name, lcfirst(substr($method->name, 4))];
            }
        }
    }

    private function sampleValue(?ReflectionType $type, ReflectionParameter $parameter): mixed
    {
        $types = $type instanceof ReflectionUnionType ? $type->getTypes() : [$type];
        $named = array_values(array_filter(
            $types,
            static fn (?ReflectionType $t): bool => $t instanceof ReflectionNamedType && 'null' !== $t->getName(),
        ));
        $name = $named[0] instanceof ReflectionNamedType ? $named[0]->getName() : 'mixed';

        return match (true) {
            'int' === $name => 7,
            'string' === $name => 'wert-'.$parameter->name,
            'bool' === $name => true,
            'array' === $name => ['extra_param' => 'x'],
            DateTimeImmutable::class === $name => new DateTimeImmutable('2026-01-02'),
            OrderBy::class === $name => OrderBy::desc('id'),
            is_subclass_of($name, UnitEnum::class) => $this->lastCase($name),
            default => self::fail('Kein Beispielwert für Typ '.$name),
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
