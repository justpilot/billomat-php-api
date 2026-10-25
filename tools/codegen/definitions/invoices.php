<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Rechnungen“ (/invoices).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */


$optionsOrder = static fn (string ...$swap): array => [
    'client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'supply_date', 'supply_date_type',
    'due_days', 'due_date', 'discount_rate', 'discount_days', 'discount_date', 'title', 'label', 'intro', 'note', 'reduction',
    ...$swap,
    'quote', 'payment_types', 'invoice_id', 'offer_id', 'confirmation_id', 'recurring_id', 'free_text_id', 'template_id',
];

return [
    'specResource' => 'rechnungen',
    'docUrl' => 'https://www.billomat.com/en/api/invoices/',

    'model' => [
        'class' => 'Invoice',
        'doc' => [
            'Repräsentiert eine Rechnung aus der Billomat-API.',
            '',
            'Status ist bei Erstellung immer DRAFT. Die endgültige Rechnungsnummer',
            '(invoice_number) wird erst beim Abschluss der Rechnung gesetzt.',
            '',
            'Die meisten Felder sind optional und hängen davon ab,',
            'welcher Endpoint genutzt wurde und welche Einstellungen im Account aktiv sind.',
        ],
    ],

    'createOptions' => [
        'class' => 'InvoiceCreateOptions',
        'endpoint' => 'POST /api/invoices',
        'dateObjects' => true,
        'items' => ['class' => 'InvoiceItemCreateOptions', 'wire' => 'invoice-items', 'item' => 'invoice-item'],
        'toArray' => $optionsOrder('currency_code', 'net_gross'),
        'doc' => [
            'Typisierter Payload für POST /invoices gemäß Billomat-API.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert,',
            'sodass Billomat seine Defaults verwenden kann.',
            'Positionen lassen sich beim Anlegen über `items:` bzw. withItem() direkt mitsenden.',
        ],
    ],

    'updateOptions' => [
        'class' => 'InvoiceUpdateOptions',
        'endpoint' => 'PUT /api/invoices/{id}',
        'dateObjects' => true,
        'toArray' => $optionsOrder('net_gross', 'currency_code'),
        'doc' => [
            'Typisierter Payload für PUT /invoices/{id} (Rechnung bearbeiten).',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Laut Billomat nur im Status DRAFT bearbeitbar; Positionen und Kommentare',
            'werden über die Sub-Ressourcen (/invoice-items, /invoice-comments) verwaltet.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'filter' => [
        'class' => 'InvoiceFilter',
        'endpoint' => 'GET /api/invoices',
        'fields' => [
            'status' => ['enum' => 'InvoiceStatus'],
            'payment_type' => ['enum' => 'InvoicePaymentType'],
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Rechnung.'],
        'client_id' => ['model' => ['required' => true, 'nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Rechnung.'],
        'invoice_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Rechnungsnummer (kann bei DRAFT leer sein).'],
        'number' => ['doc' => 'Laufende Nummer (ohne Präfix).'],
        'number_pre' => ['doc' => 'Präfix der Rechnungsnummer (z. B. "RE").'],
        'number_length' => ['doc' => 'Mindestlänge der Rechnungsnummer (wird mit führenden Nullen aufgefüllt).'],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'InvoiceStatus', 'doc' => 'Status der Rechnung, z. B. DRAFT, OPEN, PAID.'],
        'date' => ['doc' => 'Rechnungsdatum.'],
        // BC: Die Spec erlaubt auch Freitext (MIXED), das SDK führt das Feld als Datum.
        'supply_date' => ['type' => 'date', 'doc' => 'Liefer-/Leistungsdatum.'],
        'supply_date_type' => ['enum' => 'SupplyDateType', 'doc' => 'Typ des Liefer-/Leistungsdatums.'],
        'due_date' => ['doc' => 'Fälligkeitsdatum.'],
        'due_days' => ['doc' => 'Tage bis zur Fälligkeit.'],
        'address' => ['doc' => 'Vollständige Rechnungsadresse (formatiert).'],
        'discount_rate' => ['model' => ['type' => 'float'], 'update' => ['type' => 'float'], 'doc' => 'Skonto in Prozent.'],
        'discount_date' => ['doc' => 'Skontodatum.'],
        'discount_days' => ['doc' => 'Skontofrist in Tagen.'],
        'discount_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Skontobetrag.'],
        'title' => ['doc' => 'Dokumentenüberschrift.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['doc' => 'Einleitungstext.'],
        'note' => ['doc' => 'Anmerkungstext.'],
        'total_gross' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto.'],
        'total_net' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto.'],
        'net_gross' => ['enum' => 'NetGross', 'doc' => 'Preisbasis (NET, GROSS oder SETTINGS).'],
        'reduction' => ['doc' => 'Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").'],
        'total_gross_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto vor Rabatt.'],
        'total_net_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto vor Rabatt.'],
        'paid_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Bereits bezahlter Betrag.'],
        'open_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Offener Betrag.'],
        'currency_code' => ['doc' => 'Währung (ISO-Währungscode, z. B. "EUR").'],
        'quote' => ['doc' => 'Währungskurs (für Umrechnung in die Standard-Währung).'],
        'invoice_id' => ['doc' => 'ID der korrigierten Rechnung (bei Korrekturrechnung).'],
        'offer_id' => ['doc' => 'ID des Angebots, aus dem die Rechnung entstanden ist.'],
        'confirmation_id' => ['doc' => 'ID der Auftragsbestätigung, aus der die Rechnung entstanden ist.'],
        'recurring_id' => ['doc' => 'ID der Abo-Rechnung, aus der die Rechnung entstanden ist.'],
        'taxes' => ['type' => 'taxes', 'readOnly' => true, 'doc' => 'Steuerzeilen der Rechnung (Zusammenfassung pro Steuersatz).'],
        'payment_types' => [
            'doc' => 'Akzeptierte Zahlungsarten.',
            'details' => ['Billomat überträgt sie kommasepariert, z. B. "BANK_TRANSFER,CASH".'],
            'enum' => 'InvoicePaymentType',
            'enumList' => true,
        ],
        'template_id' => ['doc' => 'ID der Vorlage, mit der die Rechnung abgeschlossen wird.'],
        'invoice-items' => [
            'type' => 'list',
            'readOnly' => true,
            'list' => ['class' => 'InvoiceItem', 'item' => 'invoice-item'],
            'model' => ['property' => 'items'],
            'doc' => 'Positionen der Rechnung (nur bei GET /invoices/{id}).',
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'facturx_reference' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Referenz der Factur-X-/ZUGFeRD-Rechnung.'],
        'total_reduction' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Summe der Rabatte.'],
        'dig_proceeded' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Wurde die Rechnung digital weiterverarbeitet?'],
        'free_text_id' => ['optionsOnly' => true, 'doc' => 'ID des Freitextes zur Belegung von title, label, intro und note.'],
    ],
];
