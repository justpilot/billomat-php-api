<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Briefe“ (/letters).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

$gone = static fn (string $field): string => "since 3.1 – Billomat liefert `{$field}` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.";

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
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Briefs.'],
        'client_id' => ['model' => ['required' => true, 'nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Briefs.'],
        'letter_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Briefnummer.', 'deprecated' => $gone('letter_number')],
        'number' => ['type' => 'int', 'writable' => true, 'doc' => 'Laufende Nummer (ohne Präfix).', 'deprecated' => $gone('number')],
        'number_pre' => ['type' => 'string', 'writable' => true, 'doc' => 'Präfix der Briefnummer.', 'deprecated' => $gone('number_pre')],
        'number_length' => ['type' => 'int', 'writable' => true, 'doc' => 'Mindestlänge der Briefnummer.', 'deprecated' => $gone('number_length')],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'LetterStatus', 'doc' => 'Status des Briefs.'],
        'date' => ['doc' => 'Briefdatum.'],
        'address' => ['doc' => 'Komplette Briefadresse.', 'details' => ['Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.']],
        'subject' => ['doc' => 'Dokumentenüberschrift.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['doc' => 'Brieftext.'],
        'note' => ['type' => 'string', 'writable' => true, 'doc' => 'Anmerkung.', 'deprecated' => $gone('note')],
        'template_id' => ['doc' => 'ID der Vorlage, mit der der Brief abgeschlossen wird.'],
        'customerportal_url' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Persönliche URL zum Kundenportal.', 'deprecated' => $gone('customerportal_url')],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'supplier_id' => ['doc' => 'ID des Lieferanten.', 'details' => ['Wird statt `client_id` verwendet, wenn der Brief an einen Lieferanten geht.']],
        'free_text_id' => ['optionsOnly' => true, 'doc' => 'ID des Freitextes zur Belegung von subject, label und intro.'],
    ],
];
