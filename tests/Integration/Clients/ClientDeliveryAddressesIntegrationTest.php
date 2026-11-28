<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

use Justpilot\Billomat\Api\ClientDeliveryAddressCreateOptions;
use Justpilot\Billomat\Api\ClientDeliveryAddressUpdateOptions;
use Justpilot\Billomat\Api\InvoiceCreateOptions;
use Justpilot\Billomat\Model\ClientDeliveryAddress;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\Test;

#[CoversNothing]
final class ClientDeliveryAddressesIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function deliveryAddressesCanBeWrittenAndUsedOnInvoices(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $clients = $billomat->clients->list(['per_page' => 1]);
        if ([] === $clients) {
            self::markTestSkipped('Die Sandbox braucht mindestens einen Kunden.');
        }
        $clientId = $clients[0]->id;

        $address = $billomat->clients->deliveryAddresses->create($clientId, new ClientDeliveryAddressCreateOptions(
            label: 'Integrationstest',
            name: 'Integrationstest Lager',
            street: 'Hafenstraße 1',
            addressLine2: 'Tor 3',
            zip: '20457',
            city: 'Hamburg',
            countryCode: 'DE',
        ));

        try {
            self::assertSame($clientId, $address->clientId);
            self::assertSame('Integrationstest', $address->label);
            self::assertSame('Tor 3', $address->addressLine2);
            self::assertSame('DE', $address->countryCode);
            self::assertSame([], $address->extra);

            $listed = $billomat->clients->deliveryAddresses->list($clientId);
            self::assertContainsOnlyInstancesOf(ClientDeliveryAddress::class, $listed);
            self::assertContains($address->id, array_map(static fn (ClientDeliveryAddress $a): int => $a->id, $listed));

            $updated = $billomat->clients->deliveryAddresses->update($address->id, new ClientDeliveryAddressUpdateOptions(
                addressLine3: 'Halle 2',
                city: 'Bremen',
            ));
            self::assertSame('Bremen', $updated->city);
            self::assertSame('Halle 2', $updated->addressLine3);
            self::assertSame('Integrationstest Lager', $updated->name);

            $invoice = $billomat->invoices->create(new InvoiceCreateOptions(clientId: $clientId, clientDeliveryAddressId: $address->id));
            try {
                self::assertSame($address->id, $invoice->clientDeliveryAddressId);
            } finally {
                $billomat->invoices->delete($invoice->id);
            }
        } finally {
            $billomat->clients->deliveryAddresses->delete($address->id);
        }

        self::assertNull($billomat->clients->deliveryAddresses->get($address->id));
    }
}
