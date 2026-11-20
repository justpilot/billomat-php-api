<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Model;

use Justpilot\Billomat\Internal\ScalarCaster;
use Justpilot\Billomat\Model\Enum\CreditNoteStatus;

/**
 * Aggregat-Eintrag aus GET /credit-notes?group_by=...
 *
 * Welche der optionalen Identifier-Felder gesetzt sind, hängt vom group_by-Parameter ab:
 *  - group_by=client → clientId
 *  - group_by=status → status
 *  - group_by=day/week/month/year → eines der Zeit-Felder
 *
 * Ein leeres Zeitfeld kommt als null an.
 *
 * `creditNoteParams` enthält das Filterset, das Billomat zurückgibt, damit man den Aggregat-
 * Eintrag in einen Folge-Request an `list()` weiterreichen kann (Drill-down).
 *
 * Doku: https://www.billomat.com/api/gutschriften/ (Abschnitt "Gutschriften aggregiert auflisten")
 */
final readonly class CreditNoteGroup
{
    private const array FIELDS = ['total_gross', 'total_net', 'client_id', 'status', 'day', 'week', 'month', 'year', 'credit-note-params'];

    /**
     * @param array<string, scalar> $creditNoteParams Filter zum Drill-down (z. B. ['client_id' => 476]).
     * @param array<string, mixed>  $extra            Felder, die dieses Model nicht kennt
     */
    public function __construct(
        public ?Money $totalGross,
        public ?Money $totalNet,
        public ?int $clientId = null,
        public ?CreditNoteStatus $status = null,
        public ?string $day = null,
        public ?string $week = null,
        public ?string $month = null,
        public ?string $year = null,
        public array $creditNoteParams = [],
        public array $extra = [],
    ) {
    }

    /**
     * @param array<string,mixed> $data
     */
    public static function fromArray(array $data): self
    {
        $params = [];
        if (isset($data['credit-note-params']) && \is_array($data['credit-note-params'])) {
            foreach ($data['credit-note-params'] as $key => $value) {
                if (\is_scalar($value)) {
                    $params[(string) $key] = $value;
                }
            }
        }

        return new self(
            totalGross: ScalarCaster::toMoneyOrNull($data['total_gross'] ?? null),
            totalNet: ScalarCaster::toMoneyOrNull($data['total_net'] ?? null),
            clientId: ScalarCaster::toIntOrNull($data['client_id'] ?? null),
            status: CreditNoteStatus::tryFrom(ScalarCaster::toStringOrNull($data['status'] ?? null) ?? ''),
            day: ScalarCaster::toStringOrNull($data['day'] ?? null),
            week: ScalarCaster::toStringOrNull($data['week'] ?? null),
            month: ScalarCaster::toStringOrNull($data['month'] ?? null),
            year: ScalarCaster::toStringOrNull($data['year'] ?? null),
            creditNoteParams: $params,
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
            'client_id' => $this->clientId,
            'status' => $this->status?->value,
            'day' => $this->day,
            'week' => $this->week,
            'month' => $this->month,
            'year' => $this->year,
            'credit-note-params' => $this->creditNoteParams,
        ];
    }
}
