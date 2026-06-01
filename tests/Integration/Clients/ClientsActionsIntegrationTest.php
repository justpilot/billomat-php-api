<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Integration\Clients;

use Justpilot\Billomat\Api\ClientCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\AuthenticationException;
use Justpilot\Billomat\Exception\HttpException;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\ValidationException;
use Justpilot\Billomat\Model\Client;
use Justpilot\Billomat\Tests\Integration\AbstractBillomatIntegrationTestCase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\CoversNothing;
use PHPUnit\Framework\Attributes\Test;

/**
 * Integrationstests für Aktionen, die in den Konformitäts-Fixes ergänzt wurden:
 *  - DELETE /clients/{id}
 *  - GET    /clients/{id}/avatar
 *
 * Beide Aktionen verursachen keine Kosten und keine externen Nebenwirkungen,
 * deshalb laufen sie ohne zusätzliches Opt-in (sofern Sandbox-Credentials gesetzt sind).
 */
#[CoversNothing]
final class ClientsActionsIntegrationTest extends AbstractBillomatIntegrationTestCase
{
    #[Group('integration')]
    #[Test]
    public function can_delete_freshly_created_client_in_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();
        $faker = $this->faker();

        // 1) Wegwerf-Kunden anlegen
        $options = new ClientCreateOptions();
        $options->name = 'IntegrationDelete ' . $faker->unique()->bothify('##??##');
        $options->email = $faker->unique()->safeEmail();
        $options->countryCode = 'DE';

        $created = $billomat->clients->create($options);

        self::assertInstanceOf(Client::class, $created);
        self::assertNotNull($created->id);

        $clientId = $created->id;

        // 2) Löschen
        try {
            $result = $billomat->clients->delete($clientId);
        } catch (ValidationException $e) {
            // Sollte bei frisch angelegtem Kunden ohne Dokumente eigentlich nicht passieren —
            // wenn doch (z. B. Account-Quirks), als Skip behandeln statt failen.
            $this->markTestSkipped(
                'Billomat hat den Kunden nicht gelöscht (' . $e->getMessage() . '). '
                . 'Möglicherweise ist Hard-Delete für diesen Account-Typ deaktiviert.'
            );
        }

        self::assertTrue($result);

        // 3) GET sollte null liefern (404 wird als null gemapped)
        $deleted = $billomat->clients->get($clientId);
        self::assertNull($deleted, 'Client should be gone after delete().');
    }

    #[Group('integration')]
    #[Test]
    public function can_fetch_client_avatar_from_sandbox(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $clientId = $this->resolveAvatarableClientIdOrSkip($billomat);

        try {
            $binary = $billomat->clients->avatar($clientId);
        } catch (NotFoundException) {
            $this->markTestSkipped("Client {$clientId} hat keinen Avatar (404).");
        } catch (AuthenticationException $e) {
            // Manche Accounts/Pläne geben 401/403 statt 404 zurück, wenn der
            // Avatar-Endpoint für diesen Account-Typ deaktiviert ist.
            $this->markTestSkipped(
                'Avatar-Endpoint vom Account abgelehnt (401/403). '
                . 'Vermutlich für diesen Account-Typ nicht verfügbar: ' . $e->getMessage()
            );
        }

        self::assertIsString($binary);
        self::assertNotSame('', $binary, 'Avatar binary should not be empty.');

        // Billomat liefert i. d. R. einen Default-Avatar als PNG/JPG —
        // wir prüfen die Magic-Bytes locker statt strikt.
        $isPng = str_starts_with($binary, "\x89PNG\r\n\x1a\n");
        $isJpg = str_starts_with($binary, "\xFF\xD8\xFF");
        $isGif = str_starts_with($binary, 'GIF8');

        self::assertTrue(
            $isPng || $isJpg || $isGif,
            'Expected PNG/JPG/GIF magic bytes in avatar response, got: '
            . bin2hex(substr($binary, 0, 8))
        );
    }

    #[Group('integration')]
    #[Test]
    public function can_fetch_client_avatar_with_size_query(): void
    {
        $billomat = $this->createBillomatClientOrSkip();

        $clientId = $this->resolveAvatarableClientIdOrSkip($billomat);

        try {
            $binary = $billomat->clients->avatar($clientId, 128);
        } catch (NotFoundException) {
            $this->markTestSkipped("Client {$clientId} hat keinen Avatar (404).");
        } catch (AuthenticationException $e) {
            $this->markTestSkipped(
                'Avatar-Endpoint vom Account abgelehnt (401/403): ' . $e->getMessage()
            );
        }

        self::assertIsString($binary);
        self::assertNotSame('', $binary);
    }

    /**
     * Wählt einen Kunden, an dem der Avatar-Endpoint sinnvoll getestet werden kann.
     *
     * Strategie:
     *  1. Listet bis zu 5 echte Kunden und nimmt den ersten.
     *  2. Fällt zurück auf `clients/myself`, wenn keine Listeneinträge da sind.
     *  3. Skippt den Test, wenn keine ID ermittelbar ist.
     */
    private function resolveAvatarableClientIdOrSkip(BillomatClient $billomat): int
    {
        $candidates = [];

        try {
            $list = $billomat->clients->list(['per_page' => 5]);
        } catch (HttpException) {
            $list = [];
        }

        foreach ($list as $candidate) {
            if ($candidate->id !== null) {
                $candidates[] = $candidate->id;
            }
        }

        if ($candidates === []) {
            try {
                $me = $billomat->clients->getMyself();
                if ($me->id !== null) {
                    $candidates[] = $me->id;
                }
            } catch (HttpException) {
                // ignorieren
            }
        }

        if ($candidates === []) {
            $this->markTestSkipped('Konnte keine Client-ID für den Avatar-Test ermitteln.');
        }

        return $candidates[0];
    }
}
