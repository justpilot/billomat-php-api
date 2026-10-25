<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Mahnstufen“ (/reminder-texts).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Das SDK liest Mahnstufen nur,
 * deshalb gibt es keine Options; `specEndpoint` dient nur dem Drift-Abgleich.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/mahnstufen',
    'specEndpoint' => 'POST /api/reminder-texts',
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

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Mahnstufe.'],
        'name' => ['doc' => 'Name der Mahnstufe.'],
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
