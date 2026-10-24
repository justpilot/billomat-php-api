<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Gutschriften“ (/credit-notes).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

$gone = static fn (string $field): string => "since 3.1 – Billomat liefert `{$field}` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.";

return [
    'specResource' => 'gutschriften',
    'docUrl' => 'https://www.billomat.com/en/api/credit-notes/',

    'model' => [
        'class' => 'CreditNote',
        'doc' => ['Repräsentiert eine Gutschrift (Credit Note) aus der Billomat-API.'],
    ],

    'createOptions' => [
        'class' => 'CreditNoteCreateOptions',
        'endpoint' => 'POST /api/credit-notes',
        'dateObjects' => true,
        'items' => ['class' => 'CreditNoteItemCreateOptions', 'wire' => 'credit-note-items', 'item' => 'credit-note-item'],
        'toArray' => ['client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'title', 'label', 'intro', 'note', 'reduction', 'currency_code', 'net_gross', 'quote', 'invoice_id', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für POST /credit-notes.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
            'Positionen lassen sich beim Anlegen über addItem() direkt mitsenden.',
        ],
    ],

    'updateOptions' => [
        'class' => 'CreditNoteUpdateOptions',
        'endpoint' => 'PUT /api/credit-notes/{id}',
        'dateObjects' => true,
        'toArray' => ['client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'title', 'label', 'intro', 'note', 'reduction', 'net_gross', 'currency_code', 'quote', 'invoice_id', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für PUT /credit-notes/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Nur Gutschriften im Status DRAFT sind voll editierbar.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Gutschrift.'],
        'client_id' => ['model' => ['required' => true, 'nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Gutschrift.'],
        'credit_note_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Gutschriftsnummer (wird beim Abschließen vergeben).'],
        'number' => ['doc' => 'Laufende Nummer (ohne Präfix).'],
        'number_pre' => ['doc' => 'Präfix der Gutschriftsnummer.'],
        'number_length' => ['doc' => 'Mindestlänge der Gutschriftsnummer (wird mit führenden Nullen aufgefüllt).'],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'CreditNoteStatus', 'doc' => 'Status der Gutschrift.'],
        'date' => ['doc' => 'Gutschriftsdatum.'],
        'address' => ['doc' => 'Komplette Adresse.'],
        'title' => ['doc' => 'Dokumentenüberschrift.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['doc' => 'Einleitungstext.'],
        'note' => ['doc' => 'Anmerkungstext.'],
        'total_gross' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto.'],
        'total_net' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto.'],
        'net_gross' => ['enum' => 'NetGross', 'doc' => 'Preisbasis (Brutto- oder Netto-Preise).'],
        'reduction' => ['doc' => 'Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").'],
        'total_gross_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto vor Rabatt.'],
        'total_net_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto vor Rabatt.'],
        'paid_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Bereits ausgezahlter Betrag.'],
        'open_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Noch offener Betrag.'],
        'currency_code' => ['doc' => 'Währung (ISO-Währungscode).'],
        'quote' => ['doc' => 'Währungskurs (für Umrechnung in die Standard-Währung).'],
        'invoice_id' => ['doc' => 'ID der Quell-Rechnung (Korrekturgutschrift).'],
        'free_text_id' => ['doc' => 'ID des Freitextes zur Belegung von title, label, intro und note.'],
        'template_id' => ['doc' => 'ID der Vorlage, mit der die Gutschrift abgeschlossen wird.'],
        'taxes' => ['type' => 'taxes', 'readOnly' => true, 'doc' => 'Steuerzeilen der Gutschrift.'],
        'customerportal_url' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Persönliche URL zum Kundenportal.', 'deprecated' => $gone('customerportal_url')],
        'credit-note-items' => [
            'type' => 'list',
            'readOnly' => true,
            'list' => ['class' => 'CreditNoteItem', 'item' => 'credit-note-item'],
            'model' => ['property' => 'items'],
            'doc' => 'Positionen der Gutschrift (nur bei GET /credit-notes/{id}).',
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'total_reduction' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Summe der Rabatte.'],
    ],
];
