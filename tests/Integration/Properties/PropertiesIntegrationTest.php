<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Properties;

use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Model\ArticleProperty;
use Justpilot\Billomat\Model\ClientProperty;
use Justpilot\Billomat\Model\Enum\PropertyType;
use Justpilot\Billomat\Model\IncomingProperty;
use Justpilot\Billomat\Model\SupplierProperty;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class PropertiesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function canListArticlePropertiesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $props = $billomat->articles->properties->list();

        self::assertIsArray($props);
        self::assertContainsOnlyInstancesOf(ArticleProperty::class, $props);
    }

    #[Group('integration')]
    #[Test]
    public function canListClientPropertiesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $props = $billomat->clients->properties->list();

        self::assertIsArray($props);
        self::assertContainsOnlyInstancesOf(ClientProperty::class, $props);
    }

    #[Group('integration')]
    #[Test]
    public function canListSupplierPropertiesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $props = $billomat->suppliers->properties->list();

        self::assertIsArray($props);
        self::assertContainsOnlyInstancesOf(SupplierProperty::class, $props);
    }

    #[Group('integration')]
    #[Test]
    public function canListIncomingPropertiesFromSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $props = $billomat->incomings->properties->list();

        self::assertIsArray($props);
        self::assertContainsOnlyInstancesOf(IncomingProperty::class, $props);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateAndDeleteArticlePropertyInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $opts = new PropertyCreateOptions(name: 'IT-Prop-'.date('His'));
        $opts->type = PropertyType::TEXTFIELD;

        $prop = $billomat->articles->properties->create($opts);

        self::assertNotNull($prop->id);
        self::assertSame(PropertyType::TEXTFIELD, $prop->type);

        $billomat->articles->properties->delete($prop->id);
    }

    #[Group('integration')]
    #[Test]
    public function canCreateAndDeleteClientPropertyInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $opts = new PropertyCreateOptions(name: 'IT-ClientProp-'.date('His'));
        $opts->type = PropertyType::TEXTFIELD;

        $prop = $billomat->clients->properties->create($opts);

        self::assertNotNull($prop->id);

        $billomat->clients->properties->delete($prop->id);
    }
}
