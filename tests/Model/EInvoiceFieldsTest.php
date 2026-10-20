<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Account;
use Justpilot\Billomat\Model\AccountQuota;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Model\Enum\InvoiceItemType;
use Justpilot\Billomat\Model\Enum\InvoiceStatus;
use Justpilot\Billomat\Model\Enum\NetGross;
use Justpilot\Billomat\Model\Enum\SupplyDateType;
use Justpilot\Billomat\Model\InboxDocument;
use Justpilot\Billomat\Model\Invoice;
use Justpilot\Billomat\Model\InvoiceItem;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\UsesClass;
use PHPUnit\Framework\TestCase;

/**
 * E-Rechnungs-Felder (ZUGFeRD/Factur-X, XRechnung), die Billomat liefert,
 * aber nicht dokumentiert. Werte entsprechen den Sandbox-Antworten (2026-10).
 */
#[CoversClass(Client::class)]
#[CoversClass(Account::class)]
#[CoversClass(Invoice::class)]
#[CoversClass(InboxDocument::class)]
#[UsesClass(AccountQuota::class)]
#[UsesClass(InvoiceStatus::class)]
#[UsesClass(NetGross::class)]
#[UsesClass(SupplyDateType::class)]
#[UsesClass(InvoiceItem::class)]
#[UsesClass(InvoiceItemType::class)]
#[UsesClass(ScalarCaster::class)]
final class EInvoiceFieldsTest extends TestCase
{
    #[Test]
    public function clientExposesFacturxAndSellerIdentifier(): void
    {
        $client = Client::fromArray([
            'id' => '1',
            'name' => 'ACME',
            'facturx_identifier' => '04011000-12345-34',
            'seller_identifier' => 'K-4711',
        ]);

        self::assertSame('04011000-12345-34', $client->facturxIdentifier);
        self::assertSame('K-4711', $client->sellerIdentifier);
        self::assertSame('04011000-12345-34', $client->toArray()['facturx_identifier']);
        self::assertSame('K-4711', $client->toArray()['seller_identifier']);
    }

    #[Test]
    public function accountExposesTaxAuthorityAndFacturxFlag(): void
    {
        $account = Account::fromArray([
            'id' => '1',
            'name' => 'Eigene Firma',
            'seller_identifier' => 'S-1',
            'tax_authority_number' => '5133',
            'facturx_fields_enabled' => '1',
        ]);

        self::assertSame('5133', $account->taxAuthorityNumber);
        self::assertTrue($account->facturxFieldsEnabled);
        self::assertSame('S-1', $account->client->sellerIdentifier);
        self::assertTrue($account->toArray()['facturx_fields_enabled']);
    }

    #[Test]
    public function invoiceExposesFacturxReference(): void
    {
        $invoice = Invoice::fromArray(['id' => '1', 'facturx_reference' => 'PO-2026-17']);

        self::assertSame('PO-2026-17', $invoice->facturxReference);
        self::assertSame('PO-2026-17', $invoice->toArray()['facturx_reference']);
    }

    #[Test]
    public function inboxDocumentExposesXrechnungExtractionsId(): void
    {
        $doc = InboxDocument::fromArray(['id' => '1', 'xrechnung_extractions_id' => '815']);

        self::assertSame(815, $doc->xrechnungExtractionsId);
        self::assertSame(815, $doc->toArray()['xrechnung_extractions_id']);
    }

    #[Test]
    public function emptyEInvoiceFieldsBecomeNull(): void
    {
        $client = Client::fromArray(['id' => '1', 'facturx_identifier' => '', 'seller_identifier' => '']);
        $account = Account::fromArray(['id' => '1', 'tax_authority_number' => '', 'facturx_fields_enabled' => '']);
        $invoice = Invoice::fromArray(['id' => '1', 'facturx_reference' => '']);
        $doc = InboxDocument::fromArray(['id' => '1', 'xrechnung_extractions_id' => '']);

        self::assertNull($client->facturxIdentifier);
        self::assertNull($client->sellerIdentifier);
        self::assertNull($account->taxAuthorityNumber);
        self::assertNull($account->facturxFieldsEnabled);
        self::assertNull($invoice->facturxReference);
        self::assertNull($doc->xrechnungExtractionsId);
    }
}
