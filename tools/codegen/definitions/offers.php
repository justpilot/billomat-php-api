<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Angebote“ (/offers).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */


return [
    'specResource' => 'angebote',
    'docUrl' => 'https://www.billomat.com/en/api/estimates/',

    'model' => [
        'class' => 'Offer',
        'doc' => [
            'Repräsentiert ein Angebot (Offer / Estimate) aus der Billomat-API.',
            '',
            'Status ist bei Erstellung immer DRAFT. Die endgültige Angebotsnummer',
            '(offer_number) wird erst beim Abschluss vergeben.',
        ],
    ],

    'createOptions' => [
        'class' => 'OfferCreateOptions',
        'endpoint' => 'POST /api/offers',
        'dateObjects' => true,
        'items' => ['class' => 'OfferItemCreateOptions', 'wire' => 'offer-items', 'item' => 'offer-item'],
        'toArray' => ['client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'validity_days', 'validity_date', 'discount_rate', 'discount_days', 'discount_date', 'title', 'label', 'intro', 'note', 'reduction', 'currency_code', 'net_gross', 'quote', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für POST /offers.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
            'Positionen lassen sich beim Anlegen über addItem() direkt mitsenden.',
        ],
    ],

    'updateOptions' => [
        'class' => 'OfferUpdateOptions',
        'endpoint' => 'PUT /api/offers/{id}',
        'dateObjects' => true,
        'toArray' => ['client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'validity_days', 'validity_date', 'discount_rate', 'discount_days', 'discount_date', 'title', 'label', 'intro', 'note', 'reduction', 'net_gross', 'currency_code', 'quote', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für PUT /offers/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Nur Angebote im Status DRAFT sind voll editierbar; Positionen und',
            'Kommentare werden über die Sub-Ressourcen verwaltet.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Angebots.'],
        'client_id' => ['model' => ['required' => true, 'nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Angebots.'],
        'offer_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Angebotsnummer (wird beim Abschließen vergeben).'],
        'number' => ['doc' => 'Laufende Nummer (ohne Präfix).'],
        'number_pre' => ['doc' => 'Präfix der Angebotsnummer.'],
        'number_length' => ['doc' => 'Mindestlänge der Angebotsnummer (wird mit führenden Nullen aufgefüllt).'],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'OfferStatus', 'doc' => 'Status des Angebots.'],
        'date' => ['doc' => 'Angebotsdatum.'],
        'validity_days' => ['doc' => 'Gültigkeit in Tagen (Angebotsgültigkeit).'],
        'address' => ['doc' => 'Vollständige Angebotsadresse (formatiert).'],
        'discount_rate' => ['type' => 'int', 'writable' => true, 'model' => ['type' => 'float'], 'update' => ['type' => 'float'], 'doc' => 'Skonto in Prozent.', 'optionsOnly' => true],
        'discount_date' => ['type' => 'date', 'writable' => true, 'doc' => 'Skontodatum.', 'optionsOnly' => true],
        'discount_days' => ['type' => 'int', 'writable' => true, 'doc' => 'Skontozeitraum in Tagen.', 'optionsOnly' => true],
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
        'currency_code' => ['doc' => 'Währung (ISO-Währungscode).'],
        'quote' => ['doc' => 'Währungskurs (für Umrechnung in die Standard-Währung).'],
        'free_text_id' => ['doc' => 'ID des Freitextes zur Belegung von title, label, intro und note.'],
        'template_id' => ['doc' => 'ID der Vorlage, mit der das Angebot abgeschlossen wird.'],
        'taxes' => ['type' => 'taxes', 'readOnly' => true, 'doc' => 'Steuerzeilen des Angebots.'],
        'offer-items' => [
            'type' => 'list',
            'readOnly' => true,
            'list' => ['class' => 'OfferItem', 'item' => 'offer-item'],
            'model' => ['property' => 'items'],
            'doc' => 'Positionen des Angebots (nur bei GET /offers/{id}).',
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'validity_date' => [
            'doc' => 'Konkretes Ablaufdatum des Angebots.',
            'details' => ['Wird übersteuert durch `validityDays`, wenn beide gesetzt sind.'],
        ],
        'total_reduction' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Summe der Rabatte.'],
    ],
];
