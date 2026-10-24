<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Lieferscheine“ (/delivery-notes).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

$gone = static fn (string $field): string => "since 3.1 – Billomat liefert `{$field}` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.";

return [
    'specResource' => 'lieferscheine',
    'docUrl' => 'https://www.billomat.com/en/api/delivery-notes/',

    'model' => [
        'class' => 'DeliveryNote',
        'doc' => ['Repräsentiert einen Lieferschein (Delivery Note) aus der Billomat-API.'],
    ],

    'createOptions' => [
        'class' => 'DeliveryNoteCreateOptions',
        'endpoint' => 'POST /api/delivery-notes',
        'dateObjects' => true,
        'items' => ['class' => 'DeliveryNoteItemCreateOptions', 'wire' => 'delivery-note-items', 'item' => 'delivery-note-item'],
        'toArray' => ['client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'title', 'label', 'intro', 'note', 'reduction', 'currency_code', 'net_gross', 'quote', 'invoice_id', 'offer_id', 'confirmation_id', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für POST /delivery-notes.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
            'Positionen lassen sich beim Anlegen über addItem() direkt mitsenden.',
        ],
    ],

    'updateOptions' => [
        'class' => 'DeliveryNoteUpdateOptions',
        'endpoint' => 'PUT /api/delivery-notes/{id}',
        'dateObjects' => true,
        'toArray' => ['client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'title', 'label', 'intro', 'note', 'reduction', 'net_gross', 'currency_code', 'quote', 'invoice_id', 'offer_id', 'confirmation_id', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für PUT /delivery-notes/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Nur Lieferscheine im Status DRAFT sind voll editierbar.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Lieferscheins.'],
        'client_id' => ['model' => ['required' => true, 'nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Lieferscheins.'],
        'delivery_note_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Lieferscheinnummer (wird beim Abschließen vergeben).'],
        'number' => ['doc' => 'Laufende Nummer (ohne Präfix).'],
        'number_pre' => ['doc' => 'Präfix der Lieferscheinnummer.'],
        'number_length' => ['doc' => 'Mindestlänge der Lieferscheinnummer (wird mit führenden Nullen aufgefüllt).'],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'DeliveryNoteStatus', 'doc' => 'Status des Lieferscheins.'],
        'date' => ['doc' => 'Lieferscheindatum.'],
        'address' => ['doc' => 'Komplette Lieferscheinadresse.', 'details' => ['Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.']],
        'title' => ['doc' => 'Dokumentenüberschrift.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['doc' => 'Einleitungstext.'],
        'note' => ['doc' => 'Anmerkungstext.'],
        'total_gross' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto.', 'deprecated' => $gone('total_gross')],
        'total_net' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto.', 'deprecated' => $gone('total_net')],
        'net_gross' => ['type' => 'string', 'writable' => true, 'enum' => 'NetGross', 'doc' => 'Preisbasis (Brutto- oder Netto-Preise).', 'deprecated' => $gone('net_gross')],
        'reduction' => ['type' => 'string', 'writable' => true, 'doc' => 'Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").', 'deprecated' => $gone('reduction')],
        'total_gross_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto vor Rabatt.', 'deprecated' => $gone('total_gross_unreduced')],
        'total_net_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto vor Rabatt.', 'deprecated' => $gone('total_net_unreduced')],
        'currency_code' => ['type' => 'string', 'writable' => true, 'doc' => 'Währung (ISO-Währungscode).', 'deprecated' => $gone('currency_code')],
        'quote' => ['type' => 'float', 'writable' => true, 'doc' => 'Währungskurs (für Umrechnung in die Standard-Währung).', 'deprecated' => $gone('quote')],
        'invoice_id' => ['doc' => 'ID der Quell-Rechnung.'],
        'confirmation_id' => ['doc' => 'ID der Quell-Auftragsbestätigung.'],
        'free_text_id' => ['doc' => 'ID des Freitextes zur Belegung von title, label, intro und note.'],
        'template_id' => ['doc' => 'ID der Vorlage, mit der der Lieferschein abgeschlossen wird.'],
        'taxes' => ['type' => 'taxes', 'readOnly' => true, 'doc' => 'Steuerzeilen des Lieferscheins.'],
        'customerportal_url' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Persönliche URL zum Kundenportal.', 'deprecated' => $gone('customerportal_url')],
        'delivery-note-items' => [
            'type' => 'list',
            'readOnly' => true,
            'list' => ['class' => 'DeliveryNoteItem', 'item' => 'delivery-note-item'],
            'model' => ['property' => 'items'],
            'doc' => 'Positionen des Lieferscheins (nur bei GET /delivery-notes/{id}).',
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'offer_id' => ['doc' => 'ID des Quell-Angebots.'],
    ],
];
