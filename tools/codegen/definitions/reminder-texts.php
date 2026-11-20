<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Mahnstufen“ (/reminder-texts).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. ReminderTextCreateOptions für POST,
 * ReminderTextUpdateOptions für PUT.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/mahnstufen',
    'docUrl' => 'https://www.billomat.com/api/einstellungen/mahnstufen/',

    'model' => [
        'class' => 'ReminderText',
        'doc' => [
            'Mahn-Textbaustein (Mahnstufe).',
            '',
            'Dieses Model bildet die Struktur von GET /reminder-texts bzw. GET /reminder-texts/{id} ab.',
            '',
            'Billomat liefert die Reihenfolge als `sorting` (früher `sort`), das SDK',
            'stellt sie weiterhin als `$sort` bereit.',
        ],
    ],

    'createOptions' => [
        'class' => 'ReminderTextCreateOptions',
        'endpoint' => 'POST /api/reminder-texts',
        'doc' => [
            'Typisierter Payload für POST /reminder-texts gemäß Billomat-API.',
        ],
    ],

    'updateOptions' => [
        'class' => 'ReminderTextUpdateOptions',
        'endpoint' => 'PUT /api/reminder-texts/{id}',
        'doc' => [
            'Typisierter Payload für PUT /reminder-texts/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Mahnstufe.'],
        'name' => ['doc' => 'Name der Mahnstufe.', 'create' => ['constructor' => true]],
        'subject' => ['doc' => 'Betreff.'],
        'header' => ['doc' => 'Einleitung.'],
        'footer' => ['doc' => 'Anmerkung.'],
        // BC: Property und toArray()-Schlüssel heißen weiterhin `sort`; `sort` bleibt als Fallback.
        'sorting' => [
            'doc' => 'Reihenfolge der Mahnstufe.',
            'model' => ['property' => 'sort', 'key' => 'sort', 'sources' => ['sorting', 'sort']],
        ],
        'charge_name' => ['doc' => 'Name der Mahngebühr (falls vorhanden).'],
        'charge_description' => ['doc' => 'Beschreibung der Mahngebühr (falls vorhanden).'],
        'charge_amount' => ['doc' => 'Höhe der Mahngebühr (falls vorhanden).'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
    ],
];
