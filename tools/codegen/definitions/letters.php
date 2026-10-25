<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Briefe“ (/letters).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */


return [
    'specResource' => 'briefe',
    'docUrl' => 'https://www.billomat.com/en/api/letters/',

    'model' => [
        'class' => 'Letter',
        'doc' => ['Repräsentiert einen Brief (Letter) aus der Billomat-API.'],
    ],

    'createOptions' => [
        'class' => 'LetterCreateOptions',
        'endpoint' => 'POST /api/letters',
        'dateObjects' => true,
        'toArray' => ['client_id', 'contact_id', 'supplier_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'subject', 'label', 'intro', 'note', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für POST /letters.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'LetterUpdateOptions',
        'endpoint' => 'PUT /api/letters/{id}',
        'dateObjects' => true,
        'toArray' => ['client_id', 'contact_id', 'supplier_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'subject', 'label', 'intro', 'note', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für PUT /letters/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Ein Brief kann nur im Entwurfs-Status (DRAFT) bearbeitet werden.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'filter' => [
        'class' => 'LetterFilter',
        'endpoint' => 'GET /api/letters',
        'fields' => [
            'status' => ['enum' => 'LetterStatus'],
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Briefs.'],
        'client_id' => ['model' => ['required' => true, 'nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Briefs.'],
        'number' => ['type' => 'int', 'writable' => true, 'doc' => 'Laufende Nummer (ohne Präfix).', 'optionsOnly' => true],
        'number_pre' => ['type' => 'string', 'writable' => true, 'doc' => 'Präfix der Briefnummer.', 'optionsOnly' => true],
        'number_length' => ['type' => 'int', 'writable' => true, 'doc' => 'Mindestlänge der Briefnummer.', 'optionsOnly' => true],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'LetterStatus', 'doc' => 'Status des Briefs.'],
        'date' => ['doc' => 'Briefdatum.'],
        'address' => ['doc' => 'Komplette Briefadresse.', 'details' => ['Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.']],
        'subject' => ['doc' => 'Dokumentenüberschrift.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['doc' => 'Brieftext.'],
        'note' => ['type' => 'string', 'writable' => true, 'doc' => 'Anmerkung.', 'optionsOnly' => true],
        'template_id' => ['doc' => 'ID der Vorlage, mit der der Brief abgeschlossen wird.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'supplier_id' => ['doc' => 'ID des Lieferanten.', 'details' => ['Wird statt `client_id` verwendet, wenn der Brief an einen Lieferanten geht.']],
        'free_text_id' => ['optionsOnly' => true, 'doc' => 'ID des Freitextes zur Belegung von subject, label und intro.'],
    ],
];
