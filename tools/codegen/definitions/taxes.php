<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Steuersätze“ (/taxes).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. TaxRateCreateOptions dient
 * sowohl für POST als auch für PUT; alle Felder sind Konstruktor-Parameter.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/steuersaetze',
    'docUrl' => 'https://www.billomat.com/api/einstellungen/steuersaetze/',

    'model' => [
        'class' => 'TaxRate',
        // BC: toArray() gab Bool-Felder schon immer als 1/0 aus.
        'boolAsInt' => true,
        'doc' => [
            'Repräsentiert einen Steuersatz aus der Billomat-API.',
            '',
            'Dieses Model bildet die Struktur von GET /taxes bzw. GET /taxes/{id} ab.',
            'Es ist als Read-Model gedacht: Anlegen und Ändern erfolgen über TaxRateCreateOptions.',
        ],
    ],

    'createOptions' => [
        'class' => 'TaxRateCreateOptions',
        'endpoint' => 'POST /api/taxes',
        // Billomat erwartet 1/0 statt true/false.
        'boolAsInt' => true,
        'doc' => [
            'Typisierter Payload für POST /taxes und PUT /taxes/{id} gemäß Billomat-API.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Steuersatzes.'],
        'name' => [
            'doc' => 'Name des Steuersatzes.',
            'details' => ['Beispiel: "MwSt"'],
            'model' => ['nonNull' => true],
            'create' => ['constructor' => true],
        ],
        'rate' => [
            'doc' => 'Höhe des Steuersatzes in Prozent.',
            'details' => ['Beispiel: 19.0'],
            'model' => ['nonNull' => true],
            'create' => ['constructor' => true],
        ],
        'is_default' => [
            'doc' => 'Standard-Steuersatz?',
            'model' => ['nonNull' => true],
            'create' => ['constructor' => true, 'default' => false],
        ],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Steuersatzes.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'can_delete' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Kann der Steuersatz gelöscht werden?', 'details' => ['false, solange er auf Belegen oder Artikeln verwendet wird.']],
    ],
];
