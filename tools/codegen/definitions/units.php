<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Einheiten“ (/units).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. UnitCreateOptions für POST,
 * UnitUpdateOptions für PUT.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/einheiten',
    'docUrl' => 'https://www.billomat.com/en/api/settings/units/',

    'model' => [
        'class' => 'Unit',
        'doc' => [
            'Einheit (z. B. Stück, Stunde) aus den Billomat-Einstellungen.',
            '',
            'Dieses Model bildet die Struktur von GET /units bzw. GET /units/{id} ab.',
        ],
    ],

    'createOptions' => [
        'class' => 'UnitCreateOptions',
        'endpoint' => 'POST /api/units',
        'doc' => [
            'Typisierter Payload für POST /units gemäß Billomat-API.',
        ],
    ],

    'updateOptions' => [
        'class' => 'UnitUpdateOptions',
        'endpoint' => 'PUT /api/units/{id}',
        'doc' => [
            'Typisierter Payload für PUT /units/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Einheit.'],
        'name' => ['model' => ['nonNull' => true], 'doc' => 'Bezeichnung der Einheit.', 'create' => ['constructor' => true]],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Einheit.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
    ],
];
