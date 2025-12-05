<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Api;

use Justpilot\Billomat\Model\TaxRate;

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
     * @param array<string, scalar|array|null> $query (z.B. page, per_page)
     * @return list<TaxRate>
     */
    public function list(array $query = []): array
    {
        $data = $this->getJson('/taxes', $query);

        $taxesData = $data['taxes']['tax'] ?? [];

        if ($taxesData === [] || $taxesData === null) {
            return [];
        }

        // Billomat liefert bei einem Element teilweise ein einzelnes Array statt Liste
        if (!\is_array($taxesData) || isset($taxesData['id'])) {
            $taxesData = [$taxesData];
        }

        /** @var list<TaxRate> $taxes */
        $taxes = array_map(
            static fn(array $row): TaxRate => TaxRate::fromArray($row),
            $taxesData
        );

        return $taxes;
    }

    /**
     * Holt einen einzelnen Steuersatz.
     *
     * Wir nutzen hier getJsonOrNull() und geben null zurück,
     * wenn der Steuersatz nicht existiert (404).
     */
    public function get(int $id): ?TaxRate
    {
        $data = $this->getJsonOrNull("/taxes/{$id}");

        if ($data === null) {
            return null;
        }

        $taxData = $data['tax'] ?? null;

        if (!\is_array($taxData)) {
            throw new \RuntimeException('Unexpected response from Billomat when fetching tax rate.');
        }

        return TaxRate::fromArray($taxData);
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

        if (!\is_array($taxData)) {
            throw new \RuntimeException('Unexpected response from Billomat when creating tax rate.');
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

        if (!\is_array($taxData)) {
            throw new \RuntimeException('Unexpected response from Billomat when updating tax rate.');
        }

        return TaxRate::fromArray($taxData);
    }

    /**
     * Löscht einen Steuersatz.
     *
     * Entspricht DELETE /taxes/{id}
     *
     * @return bool true bei Erfolg (HTTP 200)
     */
    public function delete(int $id): bool
    {
        $this->deleteVoid("/taxes/{$id}");

        // Wenn kein HttpException geflogen ist, gehen wir von Erfolg aus.
        return true;
    }
}