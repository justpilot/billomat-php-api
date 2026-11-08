<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\IncomingStatus;

/**
 * Aggregat-Eintrag aus GET /incomings?group_by=...
 *
 * Welche der optionalen Identifier-Felder gesetzt sind, hängt vom group_by-Parameter ab:
 *  - group_by=supplier → supplierId
 *  - group_by=status → status
 *  - group_by=day/week/month/year → eines der Zeit-Felder
 *
 * Ein leeres Zeitfeld kommt als null an.
 *
 * `incomingParams` enthält das Filterset, das Billomat zurückgibt, damit man den Aggregat-
 * Eintrag in einen Folge-Request an `list()` weiterreichen kann (Drill-down).
 *
 * Doku: https://www.billomat.com/api/eingangsrechnungen/ (Abschnitt "Eingangsrechnungen aggregiert auflisten")
 */
final readonly class IncomingGroup
{
    private const array FIELDS = ['total_gross', 'total_net', 'supplier_id', 'status', 'day', 'week', 'month', 'year', 'incoming-params'];

    /**
     * @param array<string, scalar> $incomingParams Filter zum Drill-down (z. B. ['supplier_id' => 476]).
     * @param array<string, mixed>  $extra          Felder, die dieses Model nicht kennt
     */
    public function __construct(
        public ?Money $totalGross,
        public ?Money $totalNet,
        public ?int $supplierId = null,
        public ?IncomingStatus $status = null,
        public ?string $day = null,
        public ?string $week = null,
        public ?string $month = null,
        public ?string $year = null,
        public array $incomingParams = [],
        public array $extra = [],
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $params = [];
        if (isset($data['incoming-params']) && \is_array($data['incoming-params'])) {
            foreach ($data['incoming-params'] as $key => $value) {
                if (\is_scalar($value)) {
                    $params[(string) $key] = $value;
                }
            }
        }

        return new self(
            totalGross: ScalarCaster::toMoneyOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toMoneyOrNull($data['total_net'] ?? null),
            supplierId: ScalarCaster::toIntOrNull($data['supplier_id'] ?? null),
            status: IncomingStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            day: ScalarCaster::toStringOrNull($data['day'] ?? null),
            week: ScalarCaster::toStringOrNull($data['week'] ?? null),
            month: ScalarCaster::toStringOrNull($data['month'] ?? null),
            year: ScalarCaster::toStringOrNull($data['year'] ?? null),
            incomingParams: $params,
            extra: array_diff_key($data, array_flip(self::FIELDS)),
        );
    }

    /**
     * Für Debug- und Log-Ausgaben, mit Billomats Feldnamen.
     *
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            ...$this->extra,
            'total_gross' => $this->totalGross?->toString(),
            'total_net' => $this->totalNet?->toString(),
            'supplier_id' => $this->supplierId,
            'status' => $this->status?->value,
            'day' => $this->day,
            'week' => $this->week,
            'month' => $this->month,
            'year' => $this->year,
            'incoming-params' => $this->incomingParams,
        ];
    }
}
