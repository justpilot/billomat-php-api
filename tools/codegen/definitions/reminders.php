<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Mahnungen“ (/reminders).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */


$optionsOrder = ['invoice_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'due_days', 'due_date', 'subject', 'label', 'intro', 'note', 'reminder_text_id', 'template_id'];

return [
    'specResource' => 'mahnungen',
    'docUrl' => 'https://www.billomat.com/en/api/reminders/',

    'model' => [
        'class' => 'Reminder',
        'doc' => [
            'Repräsentiert eine Mahnung (Reminder) aus der Billomat-API.',
            '',
            'Eine Mahnung referenziert immer eine bestehende Rechnung.',
        ],
    ],

    'createOptions' => [
        'class' => 'ReminderCreateOptions',
        'endpoint' => 'POST /api/reminders',
        'dateObjects' => true,
        'toArray' => $optionsOrder,
        'doc' => [
            'Typisierter Payload für POST /reminders.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'ReminderUpdateOptions',
        'endpoint' => 'PUT /api/reminders/{id}',
        'dateObjects' => true,
        'toArray' => array_values(array_diff($optionsOrder, ['invoice_id'])),
        'doc' => [
            'Typisierter Payload für PUT /reminders/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'filter' => [
        'class' => 'ReminderFilter',
        'endpoint' => 'GET /api/reminders?invoice_number=RE123',
        'fields' => [
            'status' => ['enum' => 'ReminderStatus'],
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Mahnung.'],
        'client_id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true, 'nonNull' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        // Die Rechnung lässt sich nach dem Anlegen nicht mehr ändern.
        'invoice_id' => ['create' => ['constructor' => true], 'update' => ['skip' => true], 'doc' => 'ID der überfälligen Rechnung.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Mahnung.'],
        'number' => ['type' => 'int', 'writable' => true, 'doc' => 'Laufende Nummer (ohne Präfix).', 'optionsOnly' => true],
        'number_pre' => ['type' => 'string', 'writable' => true, 'doc' => 'Präfix der Mahnungsnummer.', 'optionsOnly' => true],
        'number_length' => ['type' => 'int', 'writable' => true, 'doc' => 'Mindestlänge der Mahnungsnummer.', 'optionsOnly' => true],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'ReminderStatus', 'doc' => 'Status der Mahnung.'],
        'date' => ['doc' => 'Mahnungsdatum.'],
        'due_days' => ['doc' => 'Tage bis zur Fälligkeit.'],
        'due_date' => ['doc' => 'Fälligkeitsdatum.'],
        'address' => ['doc' => 'Komplette Mahnungsadresse.', 'details' => ['Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.']],
        'subject' => ['doc' => 'Betreff.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['doc' => 'Einleitungstext.'],
        'note' => ['doc' => 'Anmerkungstext.'],
        'total_gross' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto.'],
        'currency_code' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Währung (ISO-Währungscode).'],
        'reminder_text_id' => ['type' => 'int', 'writable' => true, 'doc' => 'ID des Mahntextes (Mahnstufe).'],
        'template_id' => ['doc' => 'ID der Vorlage, mit der die Mahnung abgeschlossen wird.'],
        'reminder-items' => [
            'type' => 'list',
            'readOnly' => true,
            'list' => ['class' => 'ReminderItem', 'item' => 'reminder-item'],
            'model' => ['property' => 'items'],
            'doc' => 'Positionen der Mahnung (nur bei GET /reminders/{id}).',
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'paid_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Bereits bezahlter Betrag der Rechnung.'],
        'open_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Offener Betrag der Rechnung.'],
        'reminder_level' => ['type' => 'int', 'readOnly' => true, 'doc' => 'Mahnstufe.'],
        'reminder_level_name' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Bezeichnung der Mahnstufe.'],
        'is_old' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Von Billomat geliefert, aber nicht dokumentiert.'],
    ],
];
