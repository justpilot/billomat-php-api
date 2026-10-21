<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Einheiten“ (/units).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Das SDK liest Einheiten nur,
 * deshalb gibt es keine Options; `specEndpoint` dient nur dem Drift-Abgleich.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/einheiten',
    'specEndpoint' => 'POST /api/units',
    'docUrl' => 'https://www.billomat.com/en/api/settings/units/',

    'model' => [
        'class' => 'Unit',
        'doc' => [
            'Einheit (z. B. Stück, Stunde) aus den Billomat-Einstellungen.',
            '',
            'Dieses Model bildet die Struktur von GET /units bzw. GET /units/{id} ab.',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Einheit.'],
        'name' => ['model' => ['required' => true, 'nonNull' => true], 'doc' => 'Bezeichnung der Einheit.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Einheit.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
    ],
];
