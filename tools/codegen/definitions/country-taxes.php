<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Steuerfreie Länder“ (/country-taxes).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Das SDK liest steuerfreie Länder nur,
 * deshalb gibt es keine Options; `specEndpoint` dient nur dem Drift-Abgleich.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/steuerfreie-laender',
    'specEndpoint' => 'POST /api/country-taxes',
    'docUrl' => 'https://www.billomat.com/api/einstellungen/steuerfreie-laender/',

    'model' => [
        'class' => 'CountryTax',
        'doc' => [
            'Steuerfreies Land, für das Billomat keine Mehrwertsteuer berechnet.',
            '',
            'Dieses Model bildet die Struktur von GET /country-taxes bzw. GET /country-taxes/{id} ab.',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Eintrags.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der Erstellung.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'country_code' => ['doc' => 'Ländercode nach ISO 3166 Alpha-2.', 'details' => ['Beispiel: "CH"']],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
    ],
];
