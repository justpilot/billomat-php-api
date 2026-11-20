<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\PropertyValues;

use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Api\ArticlePropertyValueCreateOptions;
use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\Api\ClientPropertyValueCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingPropertyValueCreateOptions;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\ArticlePropertyValue;
use Justpilot\Billomat\Model\ClientPropertyValue;
use Justpilot\Billomat\Model\Enum\PropertyType;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use Justpilot\Billomat\Model\Money;
use Justpilot\Billomat\Model\SupplierPropertyValue;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Sammel-Test: Werte für die vier PropertyValue-Ressourcen
 * (Article, Client, Supplier, Incoming) lebenszyklus-getestet:
 *  Property-Definition → Parent-Resource → PropertyValue → cleanup beidseitig.
 */
#[CoversNothing]
final class PropertyValuesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private function ensureSupplierId(BillomatClient $billomat): int
    {
        $suppliers = $billomat->suppliers->list(['per_page' => 1]);
        if ([] !== $suppliers) {
            $existing = $suppliers[0]->id;
            self::assertNotNull($existing);

            return $existing;
        }

        $opts = new SupplierCreateOptions(name: 'IT-Lieferant-PV '.date('His'), countryCode: 'DE');
        $created = $billomat->suppliers->create($opts);

        return $created->id;
    }

    #[Group('integration')]
    #[Test]
    public function canManageArticlePropertyValuesInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $propOpts = new PropertyCreateOptions(name: 'IT-ArtProp-'.date('His'), type: PropertyType::TEXTFIELD);
        $prop = $billomat->articles->properties->create($propOpts);
        self::assertNotNull($prop->id);

        $articleOpts = new ArticleCreateOptions(
            title: 'IT-PV-Artikel '.date('His'),
            salesPrice: Money::of('9.99'),
            unit: 'Stück',
        );
        $article = $billomat->articles->create($articleOpts);

        try {
            $value = $billomat->articles->propertyValues->create($article->id, new ArticlePropertyValueCreateOptions(
                articlePropertyId: $prop->id,
                value: 'Testwert',
            ));
            self::assertNotNull($value->id);

            $values = $billomat->articles->propertyValues->list($article->id);
            self::assertContainsOnlyInstancesOf(ArticlePropertyValue::class, $values);
            self::assertGreaterThanOrEqual(1, \count($values));
        } finally {
            $billomat->articles->delete($article->id);
            $billomat->articles->properties->delete($prop->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageClientPropertyValuesInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $propOpts = new PropertyCreateOptions(name: 'IT-CliProp-'.date('His'), type: PropertyType::TEXTFIELD);
        $prop = $billomat->clients->properties->create($propOpts);
        self::assertNotNull($prop->id);

        $clientOpts = new ClientCreateOptions(name: 'IT-PV-Kunde '.date('His'));
        $client = $billomat->clients->create($clientOpts);

        try {
            $value = $billomat->clients->propertyValues->create($client->id, new ClientPropertyValueCreateOptions(
                clientPropertyId: $prop->id,
                value: 'Testwert',
            ));
            self::assertNotNull($value->id);
            self::assertSame($client->id, $value->clientId);

            $values = $billomat->clients->propertyValues->list($client->id);
            self::assertContainsOnlyInstancesOf(ClientPropertyValue::class, $values);
            self::assertGreaterThanOrEqual(1, \count($values));
        } finally {
            $billomat->clients->delete($client->id);
            $billomat->clients->properties->delete($prop->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageSupplierPropertyValuesInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $propOpts = new PropertyCreateOptions(name: 'IT-SupProp-'.date('His'), type: PropertyType::TEXTFIELD);
        $prop = $billomat->suppliers->properties->create($propOpts);
        self::assertNotNull($prop->id);

        $supplierId = $this->ensureSupplierId($billomat);

        try {
            $value = $billomat->suppliers->propertyValues->create($supplierId, new SupplierPropertyValueCreateOptions(
                supplierPropertyId: $prop->id,
                value: 'Testwert',
            ));
            self::assertNotNull($value->id);

            $values = $billomat->suppliers->propertyValues->list($supplierId);
            self::assertContainsOnlyInstancesOf(SupplierPropertyValue::class, $values);
            self::assertGreaterThanOrEqual(1, \count($values));
        } finally {
            $billomat->suppliers->properties->delete($prop->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageIncomingPropertyValuesInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $propOpts = new PropertyCreateOptions(name: 'IT-IncProp-'.date('His'), type: PropertyType::TEXTFIELD);
        $prop = $billomat->incomings->properties->create($propOpts);
        self::assertNotNull($prop->id);

        $incomingOpts = new IncomingCreateOptions(
            supplierId: $this->ensureSupplierId($billomat),
            incomingNumber: 'IT-IN-PV-'.date('His'),
        );
        $incoming = $billomat->incomings->create($incomingOpts);

        try {
            $value = $billomat->incomings->propertyValues->create($incoming->id, new IncomingPropertyValueCreateOptions(
                incomingPropertyId: $prop->id,
                value: 'Testwert',
            ));
            self::assertNotNull($value->id);

            $values = $billomat->incomings->propertyValues->list($incoming->id);
            self::assertContainsOnlyInstancesOf(IncomingPropertyValue::class, $values);
            self::assertGreaterThanOrEqual(1, \count($values));
        } finally {
            $billomat->incomings->delete($incoming->id);
            $billomat->incomings->properties->delete($prop->id);
        }
    }
}
