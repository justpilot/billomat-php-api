<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\TaxRate;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

#[CoversClass(TaxRate::class)]
#[UsesClass(ScalarCaster::class)]
#[UsesClass(UnexpectedResponseException::class)]
final class TaxRateTest extends TestCase
{
    #[Test]
    public function itHydratesAllFieldsFromArray(): void
    {
        $tax = TaxRate::fromArray([
            'id' => '12',
            'account_id' => '99',
            'name' => 'MwSt',
            'rate' => '19.0',
            'is_default' => '1',
        ]);

        self::assertSame(12, $tax->id);
        self::assertSame('MwSt', $tax->name);
        self::assertSame(19.0, $tax->rate);
        self::assertTrue($tax->isDefault);
    }

    #[Test]
    public function itTreatsIsDefaultZeroAsFalse(): void
    {
        $tax = TaxRate::fromArray([
            'id' => 1,
            'name' => 'Reduced',
            'rate' => 7,
            'is_default' => '0',
        ]);

        self::assertFalse($tax->isDefault);
    }

    #[Test]
    public function itTreatsIsDefaultTrueStringAsTrue(): void
    {
        $tax = TaxRate::fromArray(['id' => 1, 'name' => 'MwSt', 'rate' => '19', 'is_default' => 'true']);

        self::assertTrue($tax->isDefault);
    }

    #[Test]
    public function itRejectsMissingRequiredFields(): void
    {
        $this->expectException(UnexpectedResponseException::class);
        $this->expectExceptionMessageIsOrContains('TaxRate without a valid "name" field');

        TaxRate::fromArray(['id' => 1, 'rate' => '19', 'is_default' => '0']);
    }

    #[Test]
    public function toArrayMapsCamelCaseBackToSnakeCase(): void
    {
        $tax = TaxRate::fromArray([
            'id' => 12,
            'account_id' => 99,
            'name' => 'MwSt',
            'rate' => 19,
            'is_default' => 1,
        ]);

        self::assertSame(
            [
                // `account_id` bildet das Model nicht mehr ab; es bleibt als unbekanntes Feld erhalten.
                'account_id' => 99,
                'id' => 12,
                'name' => 'MwSt',
                'rate' => 19.0,
                'is_default' => 1,
                'vat_category_code' => null,
                'vat_exemption_reason_code' => null,
                'vat_exemption_reason_text' => null,
                'created' => null,
                'updated' => null,
                'customfield' => null,
                'can_delete' => null,
            ],
            $tax->toArray()
        );
    }
}
