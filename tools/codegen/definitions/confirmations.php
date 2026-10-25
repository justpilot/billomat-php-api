<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Auftragsbestätigungen“ (/confirmations).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */


return [
    'specResource' => 'auftragsbestaetigungen',
    'docUrl' => 'https://www.billomat.com/en/api/confirmations/',

    'model' => [
        'class' => 'Confirmation',
        'doc' => ['Repräsentiert eine Auftragsbestätigung (Confirmation) aus der Billomat-API.'],
    ],

    'createOptions' => [
        'class' => 'ConfirmationCreateOptions',
        'endpoint' => 'POST /api/confirmations',
        'dateObjects' => true,
        'items' => ['class' => 'ConfirmationItemCreateOptions', 'wire' => 'confirmation-items', 'item' => 'confirmation-item'],
        'toArray' => ['client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'discount_rate', 'discount_days', 'discount_date', 'title', 'label', 'intro', 'note', 'reduction', 'currency_code', 'net_gross', 'quote', 'offer_id', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für POST /confirmations.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
            'Positionen lassen sich beim Anlegen über `items:` bzw. withItem() direkt mitsenden.',
        ],
    ],

    'updateOptions' => [
        'class' => 'ConfirmationUpdateOptions',
        'endpoint' => 'PUT /api/confirmations/{id}',
        'dateObjects' => true,
        'toArray' => ['client_id', 'contact_id', 'address', 'number_pre', 'number', 'number_length', 'date', 'discount_rate', 'discount_days', 'discount_date', 'title', 'label', 'intro', 'note', 'reduction', 'net_gross', 'currency_code', 'quote', 'offer_id', 'free_text_id', 'template_id'],
        'doc' => [
            'Typisierter Payload für PUT /confirmations/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Nur Auftragsbestätigungen im Status DRAFT sind voll editierbar.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'filter' => [
        'class' => 'ConfirmationFilter',
        'endpoint' => 'GET /api/confirmations',
        'fields' => [
            'status' => ['enum' => 'ConfirmationStatus'],
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Auftragsbestätigung.'],
        'client_id' => ['model' => ['nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Auftragsbestätigung.'],
        'confirmation_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Auftragsbestätigungsnummer (wird beim Abschließen vergeben).'],
        'number' => ['doc' => 'Laufende Nummer (ohne Präfix).'],
        'number_pre' => ['doc' => 'Präfix der Auftragsbestätigungsnummer.'],
        'number_length' => ['doc' => 'Mindestlänge der Auftragsbestätigungsnummer (wird mit führenden Nullen aufgefüllt).'],
        'status' => ['type' => 'string', 'readOnly' => true, 'enum' => 'ConfirmationStatus', 'doc' => 'Status der Auftragsbestätigung.'],
        'date' => ['doc' => 'Auftragsbestätigungsdatum.'],
        'address' => ['doc' => 'Komplette Adresse.', 'details' => ['Laut Doku veraltet – Billomat verarbeitet das Feld nicht mehr, bitte contact_id oder client_id verwenden.']],
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
        'offer_id' => ['doc' => 'ID des Quell-Angebots.'],
        'free_text_id' => ['doc' => 'ID des Freitextes zur Belegung von title, label, intro und note.'],
        'template_id' => ['doc' => 'ID der Vorlage, mit der die Auftragsbestätigung abgeschlossen wird.'],
        'taxes' => ['type' => 'taxes', 'readOnly' => true, 'doc' => 'Steuerzeilen der Auftragsbestätigung.'],
        'confirmation-items' => [
            'type' => 'list',
            'readOnly' => true,
            'list' => ['class' => 'ConfirmationItem', 'item' => 'confirmation-item'],
            'model' => ['property' => 'items'],
            'doc' => 'Positionen der Auftragsbestätigung (nur bei GET /confirmations/{id}).',
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'total_reduction' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Summe der Rabatte.'],
    ],
];
