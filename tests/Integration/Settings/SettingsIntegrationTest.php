<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Settings;

use Justpilot\Billomat\Api\SettingsUpdateOptions;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Settings;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

final class SettingsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    public function test_can_get_settings_from_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $settings = $billomat->settings->get();

        self::assertInstanceOf(Settings::class, $settings);

        // Basis-Checks (nicht zu strikt, aber sinnvoll)
        self::assertNotNull($settings->currencyCode);
        self::assertNotSame('', trim((string)$settings->currencyCode));

        self::assertNotNull($settings->locale);
        self::assertNotSame('', trim((string)$settings->locale));

        self::assertNotNull($settings->netGross);
        self::assertInstanceOf(NetGross::class, $settings->netGross);

        // created/updated sind im Raw Response vorhanden (wenn Billomat sie liefert)
        if ($settings->created !== null) {
            self::assertInstanceOf(\DateTimeImmutable::class, $settings->created);
        }
        if ($settings->updated !== null) {
            self::assertInstanceOf(\DateTimeImmutable::class, $settings->updated);
        }

        // priceGroups kann leer sein, aber wenn vorhanden: richtige Struktur
        foreach ($settings->priceGroups as $idx => $name) {
            self::assertIsInt($idx);
            self::assertIsString($name);
        }
    }

    #[Group('integration')]
    public function test_can_update_settings_in_sandbox_when_explicitly_enabled(): void
    {
        // Sicherheitsbremse: PUT nur wenn explizit aktiviert
        $enabled = getenv('BILLOMAT_SETTINGS_PUT');
        if ($enabled !== '1') {
            $this->markTestSkipped('Set BILLOMAT_SETTINGS_PUT=1 to enable PUT /settings integration test.');
        }

        $billomat = $this->createBillomatClientOrSkip();

        // 1) Ausgangszustand lesen
        $before = $billomat->settings->get();
        self::assertInstanceOf(Settings::class, $before);

        // 2) Wir ändern bewusst nur ein "harmloses" Feld: print_version (0/1)
        //    und setzen es danach wieder zurück.
        $newPrintVersion = !((bool)$before->printVersion);

        $opts = new SettingsUpdateOptions();
        $opts->printVersion = $newPrintVersion;

        $updated = $billomat->settings->update($opts);
        self::assertInstanceOf(Settings::class, $updated);

        // 3) Prüfen, dass es übernommen wurde (GET zur Sicherheit nochmal)
        $after = $billomat->settings->get();
        self::assertInstanceOf(Settings::class, $after);

        if ($after->printVersion !== null) {
            self::assertSame($newPrintVersion, $after->printVersion);
        }

        // 4) Rollback (zurück auf vorherigen Wert)
        $rollback = new SettingsUpdateOptions();
        $rollback->printVersion = (bool)$before->printVersion;

        $billomat->settings->update($rollback);

        // 5) Optional: finaler Check
        $final = $billomat->settings->get();
        if ($final->printVersion !== null) {
            self::assertSame((bool)$before->printVersion, $final->printVersion);
        }
    }
}