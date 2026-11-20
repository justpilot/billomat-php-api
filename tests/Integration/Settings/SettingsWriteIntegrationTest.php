<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Settings;

use Justpilot\Billomat\Api\CountryTaxCreateOptions;
use Justpilot\Billomat\Api\CountryTaxUpdateOptions;
use Justpilot\Billomat\Api\EmailTemplateCreateOptions;
use Justpilot\Billomat\Api\EmailTemplateUpdateOptions;
use Justpilot\Billomat\Api\FreeTextCreateOptions;
use Justpilot\Billomat\Api\FreeTextUpdateOptions;
use Justpilot\Billomat\Api\ReminderTextCreateOptions;
use Justpilot\Billomat\Api\ReminderTextUpdateOptions;
use Justpilot\Billomat\Api\RoleCreateOptions;
use Justpilot\Billomat\Api\RoleUpdateOptions;
use Justpilot\Billomat\Api\UnitCreateOptions;
use Justpilot\Billomat\Api\UnitUpdateOptions;
use Justpilot\Billomat\Model\CountryTax;
use Justpilot\Billomat\Model\Enum\AccessLevel;
use Justpilot\Billomat\Model\Enum\EmailTemplateType;
use Justpilot\Billomat\Model\Enum\FreeTextType;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Anlegen, Ändern und Löschen der Einstellungs-Ressourcen, jeweils mit dem kleinsten Payload,
 * den die Create-Options erlauben (die Spec nennt mehr Pflichtfelder, als Billomat verlangt).
 */
#[CoversNothing]
#[Group('integration')]
final class SettingsWriteIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Test]
    public function unitsCanBeWritten(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $name = 'IT-Einheit '.bin2hex(random_bytes(3));

        $unit = $billomat->units->create(new UnitCreateOptions(name: $name));
        try {
            self::assertSame($name, $unit->name);
            self::assertSame($name.'-u', $billomat->units->update($unit->id, new UnitUpdateOptions(name: $name.'-u'))->name);
        } finally {
            $billomat->units->delete($unit->id);
        }
        self::assertNull($billomat->units->get($unit->id));
    }

    #[Test]
    public function emailTemplatesCanBeWritten(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $name = 'IT-Vorlage '.bin2hex(random_bytes(3));

        $template = $billomat->emailTemplates->create(new EmailTemplateCreateOptions(name: $name, type: EmailTemplateType::INVOICES));
        try {
            self::assertSame(EmailTemplateType::INVOICES, $template->type);
            $updated = $billomat->emailTemplates->update($template->id, new EmailTemplateUpdateOptions(subject: 'Ihre Rechnung', body: 'Hallo', bcc: true));
            self::assertSame('Ihre Rechnung', $updated->subject);
            self::assertSame('Hallo', $updated->body);
            self::assertTrue($updated->bcc);
            self::assertSame($name, $updated->name);
        } finally {
            $billomat->emailTemplates->delete($template->id);
        }
        self::assertNull($billomat->emailTemplates->get($template->id));
    }

    #[Test]
    public function freeTextsCanBeWritten(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $name = 'IT-Freitext '.bin2hex(random_bytes(3));

        $text = $billomat->freeTexts->create(new FreeTextCreateOptions(type: FreeTextType::OFFER, name: $name));
        try {
            self::assertSame(FreeTextType::OFFER, $text->type);
            $updated = $billomat->freeTexts->update($text->id, new FreeTextUpdateOptions(intro: 'Vielen Dank für Ihre Anfrage.'));
            self::assertSame('Vielen Dank für Ihre Anfrage.', $updated->intro);
            self::assertSame($name, $updated->name);
        } finally {
            $billomat->freeTexts->delete($text->id);
        }
        self::assertNull($billomat->freeTexts->get($text->id));
    }

    #[Test]
    public function reminderTextsCanBeWritten(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $name = 'IT-Mahnstufe '.bin2hex(random_bytes(3));

        $text = $billomat->reminderTexts->create(new ReminderTextCreateOptions(name: $name));
        try {
            self::assertSame($name, $text->name);
            $updated = $billomat->reminderTexts->update($text->id, new ReminderTextUpdateOptions(subject: 'Zahlungserinnerung'));
            self::assertSame('Zahlungserinnerung', $updated->subject);
        } finally {
            $billomat->reminderTexts->delete($text->id);
        }
        self::assertNull($billomat->reminderTexts->get($text->id));
    }

    /**
     * Billomat verlangt `country_code` auch beim Ändern; PUT geht auf /country-taxes (die Spec nennt /country_taxes).
     */
    #[Test]
    public function countryTaxesCanBeWritten(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $existing = array_map(static fn (CountryTax $tax): ?string => $tax->countryCode, $billomat->countryTaxes->list());
        $free = array_values(array_diff(['NO', 'IS', 'LI', 'SM', 'AD'], $existing));
        if (\count($free) < 2) {
            self::markTestSkipped('Not enough country codes left in the sandbox to create and change a country tax.');
        }

        $tax = $billomat->countryTaxes->create(new CountryTaxCreateOptions(countryCode: $free[0]));
        try {
            self::assertSame($free[0], $tax->countryCode);
            self::assertSame($free[1], $billomat->countryTaxes->update($tax->id, new CountryTaxUpdateOptions(countryCode: $free[1]))->countryCode);
        } finally {
            $billomat->countryTaxes->delete($tax->id);
        }
        self::assertNull($billomat->countryTaxes->get($tax->id));
    }

    #[Test]
    public function rolesCanBeWritten(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $name = 'IT-Rolle '.bin2hex(random_bytes(3));

        $role = $billomat->roles->create(new RoleCreateOptions(
            name: $name,
            permissions: ['articles' => AccessLevel::DELETE, 'settings_documents' => AccessLevel::UPDATE],
        ));
        try {
            self::assertSame(AccessLevel::DELETE, $role->permissions['articles'] ?? null);
            self::assertSame(AccessLevel::UPDATE, $role->permissions['settings_documents'] ?? null);

            // Ein entzogenes Recht fällt aus der Map, die übrigen bleiben.
            $updated = $billomat->roles->update($role->id, new RoleUpdateOptions(permissions: ['articles' => null, 'clients' => AccessLevel::READ]));
            self::assertArrayNotHasKey('articles', $updated->permissions);
            self::assertSame(AccessLevel::READ, $updated->permissions['clients'] ?? null);
            self::assertSame(AccessLevel::UPDATE, $updated->permissions['settings_documents'] ?? null);
            self::assertSame($name, $updated->name);
        } finally {
            $billomat->roles->delete($role->id);
        }
        self::assertNull($billomat->roles->get($role->id));
    }
}
