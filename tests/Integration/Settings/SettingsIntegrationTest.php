<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Settings;

use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class SettingsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canGetSettingsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $settings = $billomat->settings->get();

        // Basis-Checks (nicht zu strikt, aber sinnvoll)
        self::assertNotNull($settings->currencyCode);
        self::assertNotSame('', trim($settings->currencyCode));

        self::assertNotNull($settings->locale);
        self::assertNotSame('', trim($settings->locale));

        self::assertNotNull($settings->netGross);

        // priceGroups kann leer sein, aber wenn vorhanden: richtige Struktur
        foreach ($settings->priceGroups as $idx => $name) {
            self::assertIsInt($idx);
            self::assertIsString($name);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canUpdateSettingsInSandboxWhenExplicitlyEnabled(): void
    {
        // Sicherheitsbremse: PUT nur wenn explizit aktiviert
        $enabled = getenv('BILLOMAT_SETTINGS_PUT');
        if ('1' !== $enabled) {
            $this->markTestSkipped('Set BILLOMAT_SETTINGS_PUT=1 to enable PUT /settings integration test.');
        }

        $billomat = $this->createBillomatClientOrSkip();

        // 1) Ausgangszustand lesen
        $before = $billomat->settings->get();

        // 2) Wir ändern bewusst nur ein "harmloses" Feld: print_version (0/1)
        //    und setzen es danach wieder zurück.
        $newPrintVersion = !((bool) $before->printVersion);

        $opts = new SettingsUpdateOptions();
        $opts->printVersion = $newPrintVersion;

        $billomat->settings->update($opts);

        // 3) Prüfen, dass es übernommen wurde (GET zur Sicherheit nochmal)
        $after = $billomat->settings->get();

        if (null !== $after->printVersion) {
            self::assertSame($newPrintVersion, $after->printVersion);
        }

        // 4) Rollback (zurück auf vorherigen Wert)
        $rollback = new SettingsUpdateOptions();
        $rollback->printVersion = (bool) $before->printVersion;

        $billomat->settings->update($rollback);

        // 5) Optional: finaler Check
        $final = $billomat->settings->get();
        if (null !== $final->printVersion) {
            self::assertSame((bool) $before->printVersion, $final->printVersion);
        }
    }
}
