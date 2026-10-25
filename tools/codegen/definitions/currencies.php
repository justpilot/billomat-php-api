<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Währungen“ (/currencies).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Billomat bietet für Währungen nur
 * Lesezugriffe und dokumentiert keine Felder, deshalb ohne Options und ohne
 * Drift-Abgleich.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => null,
    'docUrl' => 'https://www.billomat.com/en/api/currencies/',

    'model' => [
        'class' => 'Currency',
        'doc' => ['Währung aus der Billomat-Währungsliste.'],
    ],

    'fields' => [
        'code' => [
            'type' => 'string',
            'readOnly' => true,
            'model' => ['nonNull' => true, 'sources' => ['code', 'currency_code']],
            'doc' => 'ISO-Währungscode (z. B. "EUR").',
        ],
        'name' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Name der Währung.'],
        'symbol' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Währungssymbol (z. B. "€").'],
        'id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'Interne Billomat-ID der Währung.'],
    ],
];
