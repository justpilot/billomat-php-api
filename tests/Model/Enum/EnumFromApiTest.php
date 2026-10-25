<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model\Enum;

use BackedEnum;
use Justpilot\Billomat\Model\Enum\RecurringEmailReceiverType;
use PHPUnit\Framework\Attributes\CoversNamespace;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

/**
 * Prüft `fromApi()` auf allen Enums in `src/Model/Enum/`.
 */
#[CoversNamespace('Justpilot\\Billomat\\Model\\Enum')]
final class EnumFromApiTest extends TestCase
{
    /**
     * @return iterable<string, array{class-string<BackedEnum>}>
     */
    public static function enums(): iterable
    {
        foreach ((array) glob(__DIR__.'/../../../src/Model/Enum/*.php') as $file) {
            $class = 'Justpilot\\Billomat\\Model\\Enum\\'.basename((string) $file, '.php');
            if (is_subclass_of($class, BackedEnum::class)) {
                yield basename((string) $file, '.php') => [$class];
            }
        }
    }

    /**
     * @param class-string<BackedEnum> $enum
     */
    #[Test]
    #[DataProvider('enums')]
    public function everyCaseIsFoundByItsBillomatValue(string $enum): void
    {
        self::assertTrue(method_exists($enum, 'fromApi'), $enum.' hat kein fromApi().');

        foreach ($enum::cases() as $case) {
            self::assertSame($case, $enum::fromApi((string) $case->value));
        }
    }

    /**
     * @param class-string<BackedEnum> $enum
     */
    #[Test]
    #[DataProvider('enums')]
    public function emptyAndUnknownValuesBecomeNull(string $enum): void
    {
        self::assertTrue(method_exists($enum, 'fromApi'), $enum.' hat kein fromApi().');

        self::assertNull($enum::fromApi(null));
        self::assertNull($enum::fromApi(''));
        self::assertNull($enum::fromApi('GIBT_ES_NICHT'));
    }

    #[Test]
    public function recurringEmailReceiverTypeIgnoresCase(): void
    {
        self::assertSame(RecurringEmailReceiverType::BCC, RecurringEmailReceiverType::fromApi('bcc'));
        self::assertSame(RecurringEmailReceiverType::CC, RecurringEmailReceiverType::fromApi('CC'));
        self::assertSame(RecurringEmailReceiverType::TO, RecurringEmailReceiverType::fromApi('To'));
    }
}
