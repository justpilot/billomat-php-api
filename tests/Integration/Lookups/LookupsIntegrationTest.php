<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Lookups;

use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Model\Country;
use Justpilot\Billomat\Model\Currency;
use Justpilot\Billomat\Model\DunningLevel;
use Justpilot\Billomat\Model\EmailTemplate;
use Justpilot\Billomat\Model\FreeText;
use Justpilot\Billomat\Model\ReminderText;
use Justpilot\Billomat\Model\Unit;
use Justpilot\Billomat\Model\User;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class LookupsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canListUsersFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $users = $billomat->users->list();

        self::assertIsArray($users);
        self::assertContainsOnlyInstancesOf(User::class, $users);
        self::assertGreaterThanOrEqual(1, \count($users));
    }

    #[Group('integration')]
    #[Test]
    public function canGetMyselfFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $me = $billomat->users->getMyself();

        self::assertInstanceOf(User::class, $me);
        self::assertNotNull($me->id);
    }

    #[Group('integration')]
    #[Test]
    public function canListCountriesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $countries = $billomat->countries->list();

        self::assertIsArray($countries);
        self::assertContainsOnlyInstancesOf(Country::class, $countries);
    }

    #[Group('integration')]
    #[Test]
    public function canListCurrenciesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $currencies = $billomat->currencies->list();

        self::assertIsArray($currencies);
        self::assertContainsOnlyInstancesOf(Currency::class, $currencies);
    }

    #[Group('integration')]
    #[Test]
    public function canListUnitsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $units = $billomat->units->list();

        self::assertIsArray($units);
        self::assertContainsOnlyInstancesOf(Unit::class, $units);
    }

    #[Group('integration')]
    #[Test]
    public function canListDunningLevelsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        // Manche Billomat-Tenants stellen /dunning-levels nicht bereit.
        try {
            $levels = $billomat->dunningLevels->list();
        } catch (NotFoundException) {
            self::markTestSkipped('Sandbox does not expose /dunning-levels (404).');
        }

        self::assertIsArray($levels);
        self::assertContainsOnlyInstancesOf(DunningLevel::class, $levels);
    }

    #[Group('integration')]
    #[Test]
    public function canListEmailTemplatesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $templates = $billomat->emailTemplates->list();

        self::assertIsArray($templates);
        self::assertContainsOnlyInstancesOf(EmailTemplate::class, $templates);
    }

    #[Group('integration')]
    #[Test]
    public function canListFreeTextsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $texts = $billomat->freeTexts->list();

        self::assertIsArray($texts);
        self::assertContainsOnlyInstancesOf(FreeText::class, $texts);
    }

    #[Group('integration')]
    #[Test]
    public function canListReminderTextsFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $texts = $billomat->reminderTexts->list();

        self::assertIsArray($texts);
        self::assertContainsOnlyInstancesOf(ReminderText::class, $texts);
    }
}
