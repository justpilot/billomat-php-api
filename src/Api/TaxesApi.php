<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Generator;
use Justpilot\Billomat\Exception\NotFoundException;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use Justpilot\Billomat\Model\TaxRate;
use Justpilot\Billomat\Pagination\Page;
use NoDiscard;

/**
 * API-Wrapper für Steuersätze.
 *
 * Endpoints:
 *  - GET    /taxes
 *  - GET    /taxes/{id}
 *  - POST   /taxes
 *  - PUT    /taxes/{id}
 *  - DELETE /taxes/{id}
 *
 * Dokumentation:
 * https://www.billomat.com/api/einstellungen/steuersaetze/
 */
final class TaxesApi extends AbstractApi
{
    /**
     * Listet alle Steuersätze auf.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return list<TaxRate>
     */
    #[NoDiscard]
    public function list(array $filters = []): array
    {
        return $this->listResource('/taxes', 'taxes', 'tax', TaxRate::fromArray(...), $filters);
    }

    /**
     * Wie {@see list()}, liefert aber zusätzlich die Pagination-Metadaten.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Page<TaxRate>
     */
    #[NoDiscard]
    public function listPage(array $filters = []): Page
    {
        return $this->listResourcePage('/taxes', 'taxes', 'tax', TaxRate::fromArray(...), $filters);
    }

    /**
     * Iteriert lazy über alle Seiten.
     *
     * @param array<string, scalar|array<scalar|null>|null> $filters
     *
     * @return Generator<int, TaxRate>
     */
    #[NoDiscard]
    public function iterateAll(array $filters = [], int $pageSize = 100): Generator
    {
        yield from $this->iterateResource('/taxes', 'taxes', 'tax', TaxRate::fromArray(...), $filters, $pageSize);
    }

    /**
     * Holt einen einzelnen Steuersatz; null, wenn es ihn nicht gibt (404).
     */
    #[NoDiscard]
    public function get(int $id): ?TaxRate
    {
        return $this->getResource("/taxes/{$id}", 'tax', TaxRate::fromArray(...), 'fetching tax');
    }

    /**
     * Wie {@see get()}, wirft aber eine {@see NotFoundException} statt null zurückzugeben.
     *
     * @throws NotFoundException wenn es den Datensatz nicht gibt (404)
     */
    #[NoDiscard]
    public function getOrFail(int $id): TaxRate
    {
        return $this->get($id) ?? throw $this->notFound("/taxes/{$id}");
    }

    /**
     * Legt einen neuen Steuersatz an.
     */
    public function create(TaxRateCreateOptions $options): TaxRate
    {
        $payload = [
            'tax' => $options->toArray(),
        ];

        $data = $this->postJson('/taxes', $payload);

        $taxData = $data['tax'] ?? null;

        if (!self::isRecord($taxData)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat when creating tax rate.');
        }

        return TaxRate::fromArray($taxData);
    }

    /**
     * Aktualisiert einen bestehenden Steuersatz.
     *
     * Laut Doku: PUT /taxes/{id} mit gleichen Feldern wie beim Anlegen.
     */
    public function update(int $id, TaxRateCreateOptions $options): TaxRate
    {
        $payload = [
            'tax' => $options->toArray(),
        ];

        $data = $this->putJson("/taxes/{$id}", $payload);

        $taxData = $data['tax'] ?? null;

        if (!self::isRecord($taxData)) {
            throw new UnexpectedResponseException('Unexpected response from Billomat when updating tax rate.');
        }

        return TaxRate::fromArray($taxData);
    }

    /**
     * Löscht einen Steuersatz.
     *
     * Entspricht DELETE /taxes/{id}
     */
    public function delete(int $id): void
    {
        $this->deleteVoid("/taxes/{$id}");

        // Wenn kein HttpException geflogen ist, gehen wir von Erfolg aus.
    }
}
