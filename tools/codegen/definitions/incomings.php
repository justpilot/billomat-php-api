<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Eingangsrechnungen“ (/incomings).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */


$optionsOrder = ['supplier_id', 'number', 'incoming_number', 'date', 'supply_date', 'due_date', 'due_days', 'address', 'label', 'intro', 'note', 'total_gross', 'total_net', 'currency_code', 'quote', 'base64file', 'category', 'client_number', 'expense_account_number'];

return [
    'specResource' => 'eingangsrechnungen',
    'docUrl' => 'https://www.billomat.com/en/api/incomings/',

    'model' => [
        'class' => 'Incoming',
        'doc' => ['Eingangsrechnung (Incoming) aus der Billomat-API.'],
    ],

    'createOptions' => [
        'class' => 'IncomingCreateOptions',
        'endpoint' => 'POST /api/incomings',
        'dateObjects' => true,
        'toArray' => $optionsOrder,
        'doc' => [
            'Typisierter Payload für POST /incomings.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'IncomingUpdateOptions',
        'endpoint' => 'PUT /api/incomings/{id}',
        'dateObjects' => true,
        'toArray' => $optionsOrder,
        'doc' => [
            'Typisierter Payload für PUT /incomings/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Eingangsrechnung.'],
        'supplier_id' => ['model' => ['required' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Lieferanten.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Eingangsrechnung.'],
        'date' => ['doc' => 'Rechnungsdatum.'],
        'supply_date' => ['type' => 'date', 'writable' => true, 'doc' => 'Liefer-/Leistungsdatum.', 'optionsOnly' => true],
        'due_date' => ['doc' => 'Fälligkeitsdatum.'],
        'due_days' => ['type' => 'int', 'writable' => true, 'doc' => 'Tage bis zur Fälligkeit.', 'optionsOnly' => true],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'IncomingStatus', 'doc' => 'Status der Eingangsrechnung.'],
        // Billomat liefert die Belegnummer als `number`; `incoming_number` nur als Fallback.
        'incoming_number' => [
            'type' => 'string',
            'writable' => true,
            'model' => ['sources' => ['number', 'incoming_number']],
            'doc' => 'Rechnungsnummer des Lieferanten.',
        ],
        'number' => ['optionsOnly' => true, 'doc' => 'Rechnungsnummer des Lieferanten.', 'details' => ['So liefert Billomat die Nummer auch in den Antworten aus (im Read-Model: `incomingNumber`).']],
        'address' => ['doc' => 'Komplette Absendeadresse.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['type' => 'string', 'writable' => true, 'doc' => 'Einleitungstext.', 'optionsOnly' => true],
        'note' => ['doc' => 'Anmerkungstext.'],
        'total_gross' => ['doc' => 'Gesamtbetrag brutto.'],
        'total_net' => ['doc' => 'Gesamtbetrag netto.'],
        'paid_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Bereits bezahlter Betrag.'],
        'open_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Offener Betrag.'],
        'currency_code' => ['doc' => 'Währung (ISO-Währungscode).'],
        'quote' => ['doc' => 'Währungskurs (für Umrechnung in die Standard-Währung).'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'client_number' => ['doc' => 'Kundennummer beim Lieferanten (z. B. eigene Vertragsnummer).'],
        'expense_account_number' => ['model' => ['type' => 'string'], 'doc' => 'Aufwandskonto laut SKR/Konten-Mapping.'],
        'category' => ['doc' => 'Kategorie.'],
        'page_count' => ['type' => 'int', 'readOnly' => true, 'doc' => 'Seitenzahl des Belegdokuments.'],
        'has_attachment' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Ist ein Belegdokument angehängt?'],
        'base64file' => ['optionsOnly' => true, 'doc' => 'Base64-codiertes Belegdokument (PDF oder Bild).'],
    ],
];
