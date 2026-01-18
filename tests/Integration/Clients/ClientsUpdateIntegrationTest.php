<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

use Justpilot\Billomat\Api\ClientUpdateOptions;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;

final class ClientsUpdateIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    public function test_can_update_client_in_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        // 1) Einen Client holen (oder Test überspringen, wenn keiner existiert)
        $clients = $billomat->clients->list(['per_page' => 1]);

        if ($clients === []) {
            $this->markTestSkipped('No clients available in sandbox to test update().');
        }

        /** @var Client $client */
        $client = $clients[0];

        if ($client->id === null) {
            $this->markTestSkipped('Client id missing in list response.');
        }

        $id = $client->id;

        // 2) Originalwerte merken, damit wir sauber zurücksetzen können
        $originalName = $client->name;
        $originalNote = $client->note ?? null;

        // 3) Update durchführen (minimal-invasiv: note ändern)
        $newNote = sprintf('SDK integration test update %s', new \DateTimeImmutable()->format('c'));

        $opts = new ClientUpdateOptions();
        $opts->note = $newNote;

        $updated = $billomat->clients->update($id, $opts);

        self::assertInstanceOf(Client::class, $updated);
        self::assertSame($id, $updated->id);
        self::assertSame($originalName, $updated->name, 'Name should not change when only note is updated.');
        self::assertSame($newNote, $updated->note);

        // 4) Neu laden und prüfen (Backend-Wahrheit)
        $fetched = $billomat->clients->get($id);

        // get() kann je nach SDK-Design null zurückgeben (z.B. getOrNull) – hier erwarten wir einen Client
        self::assertInstanceOf(Client::class, $fetched);
        self::assertSame($id, $fetched->id);
        self::assertSame($newNote, $fetched->note);

        // 5) Cleanup: note zurücksetzen (damit wir die Sandbox nicht "zumüllen")
        $reset = new ClientUpdateOptions();
        $reset->note = $originalNote;

        // Wenn originalNote null war, setzen wir leer-string, um den Effekt zu demonstrieren.
        if ($originalNote === null) {
            $reset->note = '';
        }

        $afterReset = $billomat->clients->update($id, $reset);
        self::assertInstanceOf(Client::class, $afterReset);
        self::assertSame($id, $afterReset->id);

        $fetchedAfterReset = $billomat->clients->get($id);
        self::assertInstanceOf(Client::class, $fetchedAfterReset);
        self::assertSame($id, $fetchedAfterReset->id);

        if ($originalNote === null) {
            self::assertSame('', $fetchedAfterReset->note ?? '');
        } else {
            self::assertSame($originalNote, $fetchedAfterReset->note);
        }
    }
}