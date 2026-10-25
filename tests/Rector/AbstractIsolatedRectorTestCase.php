<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Rector;

use Iterator;
use Override;
use Rector\Testing\Fixture\FixtureFileFinder;
use Rector\Testing\PHPUnit\AbstractRectorTestCase;
use RuntimeException;

/**
 * Basis der Rector-Fixture-Tests, damit Infection die Regeln zuverlässig mutieren kann.
 *
 * Zwei Eigenheiten von `AbstractRectorTestCase` lassen Mutanten sonst zufällig überleben:
 *
 * - PHPStans `AutoloadSourceLocator` ersetzt beim Auflösen von Klassen kurz den `file://`-Stream-Wrapper
 *   und ruft danach `stream_wrapper_restore('file')` auf. Das stellt PHPs eingebauten Wrapper wieder
 *   her und entfernt dabei Infections `IncludeInterceptor`, der die mutierte Datei unterschiebt.
 *   Wird eine Regelklasse erst danach geladen, testet der Lauf den Originalcode. Ob das passiert,
 *   hängt von der Testreihenfolge ab. Deshalb lädt `setUpBeforeClass()` alle Klassen aus
 *   `rector/src/`, bevor Rector zum ersten Mal PHPStan benutzt.
 * - `doTestFile()` schreibt die Eingabe neben das Fixture (`Fixture/foo.php.inc` → `Fixture/foo.php`),
 *   lässt Rector die Datei überschreiben, liest das Ergebnis von dort zurück und löscht sie in
 *   `tearDown()`. Parallele PHPUnit-Prozesse (Infection mit `--threads`) teilen sich diese Datei und
 *   lesen dann fremde Ergebnisse oder finden sie gelöscht vor. Deshalb liefert
 *   `yieldFilesFromDirectory()` Kopien der Fixtures in einem eigenen Verzeichnis pro Prozess und Testklasse.
 */
abstract class AbstractIsolatedRectorTestCase extends AbstractRectorTestCase
{
    private const string RULES_NAMESPACE = 'Justpilot\Billomat\Rector\\';

    /**
     * Kopie-Verzeichnis je Testklasse.
     *
     * @var array<class-string, string>
     */
    private static array $isolatedDirectories = [];

    #[Override]
    public static function setUpBeforeClass(): void
    {
        parent::setUpBeforeClass();

        $files = glob(\dirname(__DIR__, 2).'/rector/src/*.php');
        foreach (false === $files ? [] : $files as $file) {
            $class = self::RULES_NAMESPACE.basename($file, '.php');
            if (!class_exists($class)) {
                throw new RuntimeException(\sprintf('Klasse "%s" aus "%s" konnte nicht geladen werden.', $class, $file));
            }
        }
    }

    #[Override]
    public static function tearDownAfterClass(): void
    {
        parent::tearDownAfterClass();

        $directory = self::$isolatedDirectories[static::class] ?? null;
        if (null === $directory) {
            return;
        }

        $files = glob($directory.'/*');
        foreach (false === $files ? [] : $files as $file) {
            unlink($file);
        }
        rmdir($directory);
        unset(self::$isolatedDirectories[static::class]);
    }

    /**
     * Wie das Original, aber mit Kopien der Fixtures; der Schlüssel bleibt der Pfad im Repository.
     *
     * @return Iterator<string, array<int, string>>
     */
    #[Override]
    protected static function yieldFilesFromDirectory(string $directory, string $suffix = '*.php.inc'): Iterator
    {
        $isolatedDirectory = sys_get_temp_dir().'/billomat-rector-'.bin2hex(random_bytes(8));
        if (!mkdir($isolatedDirectory, 0o700, true) && !is_dir($isolatedDirectory)) {
            throw new RuntimeException(\sprintf('Verzeichnis "%s" konnte nicht angelegt werden.', $isolatedDirectory));
        }
        self::$isolatedDirectories[static::class] = $isolatedDirectory;

        foreach (FixtureFileFinder::yieldDirectory($directory, $suffix) as $name => [$fixture]) {
            $copy = $isolatedDirectory.'/'.basename($fixture);
            if (!copy($fixture, $copy)) {
                throw new RuntimeException(\sprintf('Fixture "%s" konnte nicht kopiert werden.', $fixture));
            }

            yield $name => [$copy];
        }
    }
}
