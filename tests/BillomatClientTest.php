<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests;

use Justpilot\Billomat\Api\InvoiceCommentsApi;
use Justpilot\Billomat\Api\InvoiceTagsApi;
use Justpilot\Billomat\Api\RecurringEmailReceiversApi;
use Justpilot\Billomat\Api\RecurringItemsApi;
use Justpilot\Billomat\Api\RecurringsApi;
use Justpilot\Billomat\Api\RecurringTagsApi;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Config\BillomatConfig;
use Justpilot\Billomat\Model\Client;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\MockHttpClient;
use Symfony\Component\HttpClient\Response\MockResponse;

use const JSON_THROW_ON_ERROR;

#[CoversClass(BillomatClient::class)]
final class BillomatClientTest extends TestCase
{
    #[Test]
    public function itWiresClientsApiAndUsesConfig(): void
    {
        $responses = [
            new MockResponse(json_encode([
                'clients' => [
                    'client' => [
                        ['id' => 1, 'name' => 'Client A'],
                    ],
                ],
            ], JSON_THROW_ON_ERROR)),
        ];

        $mockHttp = new MockHttpClient($responses);

        $config = new BillomatConfig(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
        );

        $client = new BillomatClient($config, $mockHttp);

        $result = $client->clients->list(['per_page' => 1]);

        self::assertIsArray($result);
        self::assertCount(1, $result);
        self::assertContainsOnlyInstancesOf(Client::class, $result);

        $first = $result[0];
        self::assertSame('Client A', $first->name);
        self::assertSame(1, $first->id);
    }

    #[Test]
    public function staticCreateHelperBuildsConfigAndWiresApis(): void
    {
        $mockHttp = new MockHttpClient([
            new MockResponse(json_encode([
                'clients' => [
                    'client' => [],
                ],
            ], JSON_THROW_ON_ERROR)),
        ]);

        $client = BillomatClient::create(
            billomatId: 'mycompany',
            apiKey: 'secret-key',
            httpClient: $mockHttp,
        );

        // Smoke-Test: Clients-API funktioniert und liefert ein Array von Client-Objekten
        $clients = $client->clients->list(['per_page' => 1]);

        self::assertIsArray($clients);
        self::assertContainsOnlyInstancesOf(Client::class, $clients);

        // zusätzliche Sicherstellung: weitere APIs sind verdrahtet
        self::assertInstanceOf(InvoiceCommentsApi::class, $client->invoiceComments);
        self::assertInstanceOf(InvoiceTagsApi::class, $client->invoiceTags);
        self::assertInstanceOf(RecurringsApi::class, $client->recurrings);
        self::assertInstanceOf(RecurringItemsApi::class, $client->recurringItems);
        self::assertInstanceOf(RecurringTagsApi::class, $client->recurringTags);
        self::assertInstanceOf(RecurringEmailReceiversApi::class, $client->recurringEmailReceivers);
    }
}
