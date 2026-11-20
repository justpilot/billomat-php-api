<?php

declare(strict_types=1);

namespace Justpilot\Billomat\Tests\Support;

use Closure;
use Justpilot\Billomat\Exception\UnexpectedResponseException;
use LogicException;
use ReflectionFunction;

/**
 * Minimal-Payloads mit den Pflichtfeldern der Read-Models für Tests.
 *
 * Pflichtfelder (`model.required` bzw. `ScalarCaster::required()`) werfen in `fromArray()`, wenn sie
 * fehlen. Tests, die nur ein bestimmtes Feld prüfen, brauchen trotzdem einen gültigen Datensatz;
 * {@see of()} ermittelt die Pflichtfelder eines Models, indem es `fromArray()` so lange mit einem
 * wachsenden Payload aufruft, bis keine Exception mehr kommt. So muss kein Test eine eigene Liste pflegen.
 */
final class RequiredFields
{
    /**
     * Alle Pflichtfelder aller Models mit Beispielwerten, für Tests, die nur einen Envelope-Key kennen
     * (z. B. `invoice-comment`), nicht die Model-Klasse. Felder, die ein Model nicht kennt, landen in `$extra`.
     */
    public const array ANY = [
        'id' => '7',
        'unit_price' => '1',
        'amount' => '1',
        'quantity' => '1',
        'name' => 'Name',
        'count' => '1',
        'code' => 'DE',
        'country_code' => 'DE',
        'currency_code' => 'EUR',
        'rate' => '19',
        'is_default' => '0',
        'address' => 'receiver@example.com',
        'filename' => 'document.pdf',
        'filesize' => '1',
        'resource' => 'invoices',
        'entity' => 'clients',
        'available' => '1',
        'used' => '0',
        'client_id' => '1',
        'supplier_id' => '1',
        'article_id' => '1',
        'invoice_id' => '1',
        'offer_id' => '1',
        'confirmation_id' => '1',
        'credit_note_id' => '1',
        'delivery_note_id' => '1',
        'letter_id' => '1',
        'reminder_id' => '1',
        'recurring_id' => '1',
        'incoming_id' => '1',
        'client_property_id' => '1',
        'supplier_property_id' => '1',
        'article_property_id' => '1',
        'incoming_property_id' => '1',
    ];

    /** @var array<class-string, array<string, string>> */
    private static array $cache = [];

    /**
     * Wie {@see of()}, für einen Hydrator der Form `Invoice::fromArray(...)`.
     *
     * @param Closure(array<string,mixed>): object $hydrate
     *
     * @return array<string, string>
     */
    public static function forHydrator(Closure $hydrate): array
    {
        $class = new ReflectionFunction($hydrate)->getClosureScopeClass() ?? throw new LogicException('Expected a hydrator like Model::fromArray(...).');

        return self::of($class->getName());
    }

    /**
     * Pflichtfelder eines Models mit Beispielwerten, z. B. `['id' => '7', 'client_id' => '1']` für `Invoice`.
     *
     * @param class-string $model Read-Model mit statischem `fromArray(array)`
     *
     * @return array<string, string>
     */
    public static function of(string $model): array
    {
        if (isset(self::$cache[$model])) {
            return self::$cache[$model];
        }

        $payload = [];
        // Jede Runde ergänzt genau ein fehlendes Feld; mehr Pflichtfelder hat kein Model.
        for ($round = 0; $round < 20; ++$round) {
            try {
                $model::fromArray($payload);

                return self::$cache[$model] = $payload;
            } catch (UnexpectedResponseException $e) {
                if (1 !== preg_match('/without a valid "([a-z_]+)" field/', $e->getMessage(), $match)) {
                    throw $e;
                }
                $field = $match[1];
                if (\array_key_exists($field, $payload)) {
                    throw new LogicException(\sprintf('%s rejects the sample value of "%s"; add a better one to %s::ANY.', $model, $field, self::class), 0, $e);
                }
                $payload[$field] = self::ANY[$field] ?? throw new LogicException(\sprintf('No sample value for the required field "%s" of %s; add it to %s::ANY.', $field, $model, self::class), 0, $e);
            }
        }

        throw new LogicException(\sprintf('%s still throws after 20 required fields.', $model));
    }
}
