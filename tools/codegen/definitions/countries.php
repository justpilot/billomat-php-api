<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Länder“ (/countries).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Billomat bietet für Länder nur
 * Lesezugriffe und dokumentiert keine Felder, deshalb ohne Options und ohne
 * Drift-Abgleich.
 *
 * Nach Änderungen: composer generate
 */

$gone = static fn (string $field): string => "since 3.1 – Billomat liefert `{$field}` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.";

return [
    'specResource' => null,
    'docUrl' => 'https://www.billomat.com/en/api/countries/',

    'model' => [
        'class' => 'Country',
        'doc' => ['Land aus der Billomat-Länderliste.'],
    ],

    'fields' => [
        'code' => [
            'type' => 'string',
            'readOnly' => true,
            'model' => ['required' => true, 'nonNull' => true, 'sources' => ['code', 'country_code']],
            'doc' => 'ISO-Ländercode (z. B. "DE").',
        ],
        'name' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Name des Landes.'],
        'name_de' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Deutscher Name des Landes.', 'deprecated' => $gone('name_de')],
        'eu' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Gehört das Land zur EU?', 'deprecated' => $gone('eu')],
        'id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'Interne Billomat-ID des Landes.'],
    ],
];
