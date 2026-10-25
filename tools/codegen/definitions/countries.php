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
            'model' => ['nonNull' => true, 'sources' => ['code', 'country_code']],
            'doc' => 'ISO-Ländercode (z. B. "DE").',
        ],
        'name' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Name des Landes.'],
        'id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'Interne Billomat-ID des Landes.'],
    ],
];
