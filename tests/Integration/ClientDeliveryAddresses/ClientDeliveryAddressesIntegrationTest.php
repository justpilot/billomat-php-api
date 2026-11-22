<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\ClientDeliveryAddresses;

use Justpilot\Billomat\Api\ClientDeliveryAddressCreateOptions;
use Justpilot\Billomat\Api\ClientDeliveryAddressUpdateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\ClientDeliveryAddress;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

/**
 * Lieferadressen gegen die Sandbox. Alles hängt am Beispielkunden „SDK-Beispiel GmbH“
 * (siehe tools/record-fixtures.php): Die Sandbox enthält echte Kundendaten, an die nichts
 * angehängt werden soll. Jeder Test räumt seine Datensätze wieder ab.
 */
#[CoversNothing]
final class ClientDeliveryAddressesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    private const string SEED_CLIENT = 'SDK-Beispiel GmbH';

    #[Group('integration')]
    #[Test]
    public function canCreateUpdateDeleteDeliveryAddressInSandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->seedClientId($billomat);

        $address = $billomat->clients->deliveryAddresses->create($clientId, new ClientDeliveryAddressCreateOptions(
            label: 'IT-Lieferadresse',
            // Eindeutig, weil Billomat gleiche Adressen eines Kunden ablehnt (das Label zählt dabei nicht).
            street: 'Teststraße '.uniqid(),
            addressLine2: 'Hinterhof',
            zip: '10115',
            city: 'Berlin',
            countryCode: 'DE',
            isDefault: false,
        ));

        try {
            self::assertSame($clientId, $address->clientId);
            self::assertSame('Hinterhof', $address->addressLine2);
            self::assertFalse($address->isDefault);

            $updated = $billomat->clients->deliveryAddresses->update($address->id, new ClientDeliveryAddressUpdateOptions(city: 'Bremen'));
            self::assertSame('Bremen', $updated->city);
            // Partial Update: nicht gesendete Felder bleiben erhalten.
            self::assertSame($address->street, $updated->street);

            $ids = array_map(static fn (ClientDeliveryAddress $a): int => $a->id, $billomat->clients->deliveryAddresses->list($clientId));
            self::assertContains($address->id, $ids);
        } finally {
            $billomat->clients->deliveryAddresses->delete($address->id);
        }

        self::assertNull($billomat->clients->deliveryAddresses->get($address->id));
    }

    #[Group('integration')]
    #[Test]
    public function anInvalidCountryCodeIsAValidationError(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->seedClientId($billomat);

        $this->expectException(ValidationException::class);
        $this->expectExceptionMessageIsOrContains('country_code');

        $billomat->clients->deliveryAddresses->create($clientId, new ClientDeliveryAddressCreateOptions(countryCode: 'XX'));
    }

    #[Group('integration')]
    #[Test]
    public function anInvoiceCanReferenceADeliveryAddress(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $clientId = $this->seedClientId($billomat);

        $address = $billomat->clients->deliveryAddresses->create($clientId, new ClientDeliveryAddressCreateOptions(
            label: 'IT-Rechnung',
            street: 'Rechnungsweg '.uniqid(),
            city: 'Hamburg',
            countryCode: 'DE',
        ));

        try {
            $invoice = $billomat->invoices->create(new InvoiceCreateOptions(
                clientId: $clientId,
                label: 'Integrationstest Lieferadresse',
                clientDeliveryAddressId: $address->id,
            ));

            try {
                self::assertSame($address->id, $invoice->clientDeliveryAddressId);
                self::assertSame($address->id, $billomat->invoices->getOrFail($invoice->id)->clientDeliveryAddressId);
            } finally {
                $billomat->invoices->delete($invoice->id);
            }
        } finally {
            $billomat->clients->deliveryAddresses->delete($address->id);
        }
    }

    private function seedClientId(BillomatClient $billomat): int
    {
        foreach ($billomat->clients->list(['name' => self::SEED_CLIENT]) as $client) {
            if (self::SEED_CLIENT === $client->name) {
                return $client->id;
            }
        }

        self::markTestSkipped('Beispielkunde „'.self::SEED_CLIENT.'“ fehlt in der Sandbox.');
    }
}
