<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;

/**
 * Stammdaten und Kontingent des eigenen Billomat-Accounts.
 *
 * Bildet die Antwort von GET /clients/myself ab. Der Account wird in der
 * Billomat-API technisch als Client geführt; zusätzlich liefert dieser
 * spezielle Aufruf den aktuellen Tarif (`plan`) und das Kontingent
 * (`quotas`), die für gewöhnliche Clients nicht gesetzt sind.
 *
 * Dokumentation: https://www.billomat.com/api/account/
 */
final readonly class Account
{
    /**
     * @param list<AccountQuota> $quotas
     */
    public function __construct(
        public Client $client,
        public ?string $plan,
        public array $quotas,
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            client: Client::fromArray($data),
            plan: ScalarCaster::toStringOrNull($data['plan'] ?? null),
            quotas: self::extractQuotas($data['quotas'] ?? null),
        );
    }

    public function quota(string $entity): ?AccountQuota
    {
        foreach ($this->quotas as $quota) {
            if ($quota->entity === $entity) {
                return $quota;
            }
        }

        return null;
    }

    /**
     * @return array<string,mixed>
     */
    public function toArray(): array
    {
        return [
            'plan' => $this->plan,
            'quotas' => array_map(static fn (AccountQuota $q): array => $q->toArray(), $this->quotas),
            'client' => $this->client->toArray(),
        ];
    }

    /**
     * @return list<AccountQuota>
     */
    private static function extractQuotas(mixed $raw): array
    {
        if (!\is_array($raw)) {
            return [];
        }

        // Billomat verschachtelt: { "quotas": { "quota": [...] } } oder einzeln.
        $items = $raw['quota'] ?? $raw;

        if (!\is_array($items)) {
            return [];
        }

        // Einzel-Objekt → Liste machen
        if (!array_is_list($items)) {
            $items = [$items];
        }

        $quotas = [];

        foreach ($items as $item) {
            if (!\is_array($item)) {
                continue;
            }
            /** @var array<string,mixed> $item */
            $quotas[] = AccountQuota::fromArray($item);
        }

        return $quotas;
    }
}
