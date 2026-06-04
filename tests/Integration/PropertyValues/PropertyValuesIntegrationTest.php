<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\PropertyValues;

use Justpilot\Billomat\Api\ArticleCreateOptions;
use Justpilot\Billomat\Api\ArticlePropertyValueCreateOptions;
use Justpilot\Billomat\Api\IncomingCreateOptions;
use Justpilot\Billomat\Api\IncomingPropertyValueCreateOptions;
use Justpilot\Billomat\Api\PropertyCreateOptions;
use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Model\ArticlePropertyValue;
use Justpilot\Billomat\Model\Enum\PropertyType;
use Justpilot\Billomat\Model\IncomingPropertyValue;
use Justpilot\Billomat\Model\SupplierPropertyValue;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Sammel-Test: Werte für die drei PropertyValue-Ressourcen
 * (Article, Supplier, Incoming) lebenszyklus-getestet:
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

        $opts = new SupplierCreateOptions(name: 'IT-Lieferant-PV '.date('His'));
        $opts->countryCode = 'DE';
        $created = $billomat->suppliers->create($opts);
        $id = $created->id;
        self::assertNotNull($id);

        return $id;
    }

    #[Group('integration')]
    #[Test]
    public function canManageArticlePropertyValuesInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $propOpts = new PropertyCreateOptions(name: 'IT-ArtProp-'.date('His'));
        $propOpts->type = PropertyType::TEXTFIELD;
        $prop = $billomat->articleProperties->create($propOpts);
        self::assertNotNull($prop->id);

        $articleOpts = new ArticleCreateOptions(title: 'IT-PV-Artikel '.date('His'));
        $articleOpts->salesPrice = 9.99;
        $articleOpts->unit = 'Stück';
        $article = $billomat->articles->create($articleOpts);
        self::assertNotNull($article->id);

        try {
            $value = $billomat->articlePropertyValues->create(new ArticlePropertyValueCreateOptions(
                articleId: $article->id,
                articlePropertyId: $prop->id,
                value: 'Testwert',
            ));
            self::assertNotNull($value->id);

            $values = $billomat->articlePropertyValues->list(['article_id' => $article->id]);
            self::assertContainsOnlyInstancesOf(ArticlePropertyValue::class, $values);
            self::assertGreaterThanOrEqual(1, \count($values));
        } finally {
            $billomat->articles->delete($article->id);
            $billomat->articleProperties->delete($prop->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageSupplierPropertyValuesInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $propOpts = new PropertyCreateOptions(name: 'IT-SupProp-'.date('His'));
        $propOpts->type = PropertyType::TEXTFIELD;
        $prop = $billomat->supplierProperties->create($propOpts);
        self::assertNotNull($prop->id);

        $supplierId = $this->ensureSupplierId($billomat);

        try {
            $value = $billomat->supplierPropertyValues->create(new SupplierPropertyValueCreateOptions(
                supplierId: $supplierId,
                supplierPropertyId: $prop->id,
                value: 'Testwert',
            ));
            self::assertNotNull($value->id);

            $values = $billomat->supplierPropertyValues->list(['supplier_id' => $supplierId]);
            self::assertContainsOnlyInstancesOf(SupplierPropertyValue::class, $values);
            self::assertGreaterThanOrEqual(1, \count($values));
        } finally {
            $billomat->supplierProperties->delete($prop->id);
        }
    }

    #[Group('integration')]
    #[Test]
    public function canManageIncomingPropertyValuesInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $propOpts = new PropertyCreateOptions(name: 'IT-IncProp-'.date('His'));
        $propOpts->type = PropertyType::TEXTFIELD;
        $prop = $billomat->incomingProperties->create($propOpts);
        self::assertNotNull($prop->id);

        $incomingOpts = new IncomingCreateOptions(supplierId: $this->ensureSupplierId($billomat));
        $incomingOpts->incomingNumber = 'IT-IN-PV-'.date('His');
        $incoming = $billomat->incomings->create($incomingOpts);
        self::assertNotNull($incoming->id);

        try {
            $value = $billomat->incomingPropertyValues->create(new IncomingPropertyValueCreateOptions(
                incomingId: $incoming->id,
                incomingPropertyId: $prop->id,
                value: 'Testwert',
            ));
            self::assertNotNull($value->id);

            $values = $billomat->incomingPropertyValues->list(['incoming_id' => $incoming->id]);
            self::assertContainsOnlyInstancesOf(IncomingPropertyValue::class, $values);
            self::assertGreaterThanOrEqual(1, \count($values));
        } finally {
            $billomat->incomings->delete($incoming->id);
            $billomat->incomingProperties->delete($prop->id);
        }
    }
}
