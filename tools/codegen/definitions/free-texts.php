<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Freitexte“ (/free-texts).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. FreeTextCreateOptions für POST,
 * FreeTextUpdateOptions für PUT.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/freitexte',
    'docUrl' => 'https://www.billomat.com/en/api/settings/free-texts/',

    'model' => [
        'class' => 'FreeText',
        'doc' => [
            'Freitext-Baustein (verwendbar als title/label/intro/note auf Dokumenten).',
            '',
            'Dieses Model bildet die Struktur von GET /free-texts bzw. GET /free-texts/{id} ab.',
        ],
    ],

    'createOptions' => [
        'class' => 'FreeTextCreateOptions',
        // Billomat erwartet 1/0 statt true/false.
        'boolAsInt' => true,
        'endpoint' => 'POST /api/free-texts',
        'doc' => [
            'Typisierter Payload für POST /free-texts gemäß Billomat-API.',
        ],
    ],

    'updateOptions' => [
        'class' => 'FreeTextUpdateOptions',
        // Billomat erwartet 1/0 statt true/false.
        'boolAsInt' => true,
        'endpoint' => 'PUT /api/free-texts/{id}',
        'doc' => [
            'Typisierter Payload für PUT /free-texts/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Freitexts.'],
        'title' => ['doc' => 'Überschrift bzw. Betreff.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['doc' => 'Einleitungstext bzw. Brieftext.'],
        'note' => ['doc' => 'Anmerkungen.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Freitexts.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'type' => [
            'doc' => 'Dokumenttyp, für den der Freitext gilt.',
            'details' => ['Mögliche Werte: INVOICE, CORRECTION, OFFER, CONFIRMATION, CREDIT_NOTE, DELIVERY_NOTE, LETTER'],
            'enum' => 'FreeTextType',
            'create' => ['constructor' => true],
        ],
        'name' => ['doc' => 'Name der Textvorlage.', 'create' => ['constructor' => true]],
        'is_default' => ['doc' => 'Standard-Freitext für den Dokumenttyp?'],
    ],
];
