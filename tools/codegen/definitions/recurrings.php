<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Abo-Rechnungen“ (/recurrings).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */


$optionsOrder = [
    'client_id', 'contact_id', 'address', 'number_pre', 'supply_date', 'supply_date_type', 'due_days', 'discount_rate',
    'discount_days', 'name', 'title', 'label', 'intro', 'note', 'reduction', 'currency_code', 'net_gross', 'quote',
    'payment_types', 'action', 'cycle_number', 'cycle', 'hour', 'start_date', 'end_date', 'iterations', 'email_sender',
    'email_subject', 'email_message', 'email_template_id', 'free_text_id', 'template_id', 'next_creation_date',
    'email_filename', 'email_bcc', 'letter_color', 'letter_duplex', 'letter_paper_weight', 'offer_id', 'confirmation_id',
];

return [
    'specResource' => 'abo-rechnungen',
    'docUrl' => 'https://www.billomat.com/api/abo-rechnungen/',

    'model' => [
        'class' => 'Recurring',
        'doc' => [
            'Repräsentiert eine Abo-Rechnung (Recurring) aus der Billomat-API.',
            '',
            'Eine Abo-Rechnung erzeugt periodisch (gemäß cycle/cycle_number) neue',
            '"echte" Rechnungen. Was bei jedem Lauf geschieht, steuert `action`.',
        ],
    ],

    'createOptions' => [
        'class' => 'RecurringCreateOptions',
        'endpoint' => 'POST /api/recurrings',
        'dateObjects' => true,
        'items' => ['class' => 'RecurringItemCreateOptions', 'wire' => 'recurring-items', 'item' => 'recurring-item'],
        'toArray' => $optionsOrder,
        'doc' => [
            'Typisierter Payload für POST /recurrings.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
            'Positionen lassen sich beim Anlegen über `items:` bzw. withItem() direkt mitsenden;',
            'spätere Änderungen an Positionen laufen über $billomat->recurrings->items.',
        ],
    ],

    'updateOptions' => [
        'class' => 'RecurringUpdateOptions',
        'endpoint' => 'PUT /api/recurrings/{id}',
        'dateObjects' => true,
        'toArray' => $optionsOrder,
        'doc' => [
            'Typisierter Payload für PUT /recurrings/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Positionen lassen sich hier nicht bearbeiten, dafür ist $billomat->recurrings->items zuständig.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Abo-Rechnung.'],
        'client_id' => ['model' => ['required' => true, 'nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID des Kunden.'],
        'contact_id' => ['doc' => 'ID des Kontakts.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Abo-Rechnung.'],
        'address' => ['doc' => 'Komplette Adresse.', 'details' => ['Wird ein leerer Wert übergeben, wird die aktuelle Kunden-Adresse verwendet.']],
        // BC: Die Spec erlaubt auch Freitext (MIXED), das SDK führt das Feld als Datum.
        'supply_date' => ['type' => 'date', 'doc' => 'Liefer-/Leistungsdatum.'],
        'supply_date_type' => ['enum' => 'SupplyDateType', 'doc' => 'Typ des Liefer-/Leistungsdatums.'],
        'due_days' => ['doc' => 'Tage bis zur Fälligkeit der erzeugten Rechnungen.'],
        'discount_rate' => ['create' => ['type' => 'int'], 'update' => ['type' => 'int'], 'doc' => 'Skonto in Prozent.'],
        'discount_days' => ['doc' => 'Skontofrist in Tagen.'],
        'name' => ['doc' => 'Name der Abo-Rechnung (nur intern sichtbar).'],
        'number_pre' => ['type' => 'string', 'writable' => true, 'doc' => 'Präfix der Rechnungsnummer.', 'optionsOnly' => true],
        'title' => ['doc' => 'Dokumentenüberschrift.'],
        'label' => ['doc' => 'Bezeichnung.'],
        'intro' => ['doc' => 'Einleitungstext.'],
        'note' => ['doc' => 'Anmerkungstext.'],
        'currency_code' => ['doc' => 'Währung (ISO-Währungscode).'],
        'net_gross' => ['enum' => 'NetGross', 'doc' => 'Preisbasis (Brutto- oder Netto-Preise).'],
        'quote' => ['doc' => 'Währungskurs (für Umrechnung in die Standard-Währung).'],
        'payment_types' => ['doc' => 'Akzeptierte Zahlungsarten (kommasepariert).'],
        'action' => ['enum' => 'RecurringAction', 'doc' => 'Aktion, die bei jedem Lauf ausgeführt wird.'],
        'cycle_number' => ['doc' => 'Anzahl der Intervall-Einheiten zwischen zwei Läufen.'],
        'cycle' => ['enum' => 'RecurringCycle', 'doc' => 'Intervall-Einheit (z. B. monatlich).'],
        'hour' => ['doc' => 'Stunde, zu der die Rechnung erzeugt wird.'],
        'start_date' => ['doc' => 'Startdatum.'],
        'end_date' => ['doc' => 'Enddatum.'],
        'last_creation_date' => ['type' => 'date', 'readOnly' => true, 'doc' => 'Datum der zuletzt erzeugten Rechnung.'],
        'next_creation_date' => ['doc' => 'Datum der nächsten Rechnung.'],
        'iterations' => ['type' => 'int', 'writable' => true, 'doc' => 'Anzahl der Läufe, nach denen die Abo-Rechnung endet.'],
        'counter' => ['type' => 'int', 'readOnly' => true, 'doc' => 'Anzahl der bisher erzeugten Rechnungen.'],
        'total_gross' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto.'],
        'total_net' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto.'],
        'reduction' => ['doc' => 'Rabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").'],
        'email_sender' => ['doc' => 'Absender-Adresse der E-Mail.'],
        'email_subject' => ['doc' => 'Betreff der E-Mail.'],
        'email_message' => ['doc' => 'Text der E-Mail.'],
        'email_template_id' => ['doc' => 'ID der E-Mail-Vorlage.'],
        'free_text_id' => ['doc' => 'ID des Freitextes zur Belegung von title, label, intro und note.'],
        'template_id' => ['doc' => 'ID der Vorlage für die erzeugten Rechnungen.'],
        'taxes' => ['type' => 'taxes', 'readOnly' => true, 'doc' => 'Steuerzeilen der Abo-Rechnung.'],
        'recurring-items' => [
            'type' => 'list',
            'readOnly' => true,
            'list' => ['class' => 'RecurringItem', 'item' => 'recurring-item'],
            'model' => ['property' => 'items'],
            'doc' => 'Positionen der Abo-Rechnung (nur bei GET /recurrings/{id}).',
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'total_reduction' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Summe der Rabatte.'],
        'total_net_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto vor Rabatt.'],
        'total_gross_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto vor Rabatt.'],
        'discount_amount' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Skontobetrag.'],
        'status' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Status der Abo-Rechnung.'],
        'in_progress' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Läuft gerade eine Rechnungserzeugung?'],
        'ultimo' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Rechnungen immer zum Monatsletzten erzeugen?'],
        'email_filename' => ['doc' => 'Dateiname des PDF-Anhangs.'],
        'email_bcc' => ['doc' => 'E-Mail zusätzlich als BCC an den Absender senden?'],
        'letter_color' => ['doc' => 'Brief farbig drucken?'],
        'letter_duplex' => ['doc' => 'Brief beidseitig drucken?'],
        'letter_paper_weight' => ['doc' => 'Papiergewicht des Briefs in g/m².'],
        'offer_id' => ['doc' => 'ID des Quell-Angebots.'],
        'confirmation_id' => ['doc' => 'ID der Quell-Auftragsbestätigung.'],
    ],
];
