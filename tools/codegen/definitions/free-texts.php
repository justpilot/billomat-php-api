<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Freitexte“ (/free-texts).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Das SDK liest Freitexte nur,
 * deshalb gibt es keine Options; `specEndpoint` dient nur dem Drift-Abgleich.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/freitexte',
    'specEndpoint' => 'POST /api/free-texts',
    'docUrl' => 'https://www.billomat.com/en/api/settings/free-texts/',

    'model' => [
        'class' => 'FreeText',
        'doc' => [
            'Freitext-Baustein (verwendbar als title/label/intro/note auf Dokumenten).',
            '',
            'Dieses Model bildet die Struktur von GET /free-texts bzw. GET /free-texts/{id} ab.',
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
        ],
        'name' => ['doc' => 'Name der Textvorlage.'],
        'is_default' => ['doc' => 'Standard-Freitext für den Dokumenttyp?'],
    ],
];
