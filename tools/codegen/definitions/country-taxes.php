<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Steuerfreie Länder“ (/country-taxes).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. CountryTaxCreateOptions für POST,
 * CountryTaxUpdateOptions für PUT.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/steuerfreie-laender',
    'docUrl' => 'https://www.billomat.com/api/einstellungen/steuerfreie-laender/',

    'model' => [
        'class' => 'CountryTax',
        'doc' => [
            'Steuerfreies Land, für das Billomat keine Mehrwertsteuer berechnet.',
            '',
            'Dieses Model bildet die Struktur von GET /country-taxes bzw. GET /country-taxes/{id} ab.',
        ],
    ],

    'createOptions' => [
        'class' => 'CountryTaxCreateOptions',
        'endpoint' => 'POST /api/country-taxes',
        'doc' => [
            'Typisierter Payload für POST /country-taxes gemäß Billomat-API.',
        ],
    ],

    'updateOptions' => [
        'class' => 'CountryTaxUpdateOptions',
        'endpoint' => 'PUT /api/country-taxes/{id}',
        'doc' => [
            'Typisierter Payload für PUT /country-taxes/{id} gemäß Billomat-API.',
            '',
            'Billomat verlangt `country_code` auch beim Ändern (sonst HTTP 400).',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Eintrags.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der Erstellung.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'country_code' => ['doc' => 'Ländercode nach ISO 3166 Alpha-2.', 'details' => ['Beispiel: "CH"'], 'create' => ['constructor' => true]],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
    ],
];
