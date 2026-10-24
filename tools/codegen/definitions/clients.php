<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kunden“ (/clients).
 *
 * Die Spec (docs/spec/billomat.json, Slug `kunden`) liefert für jedes schreibbare
 * Feld Typ, Default und Enum-Werte. Hier steht nur, was die Spec nicht weiß:
 * Reihenfolge, deutsche Beschreibungen, Felder aus den API-Antworten (read-only)
 * und Abweichungen, die aus BC-Gründen erhalten bleiben.
 *
 * Feld-Schlüssel (alle optional):
 *   doc         Ein-Satz-Beschreibung (deutsch). Fehlt sie, wird description_de aus der Spec genommen.
 *   details     Weitere Doku-Zeilen (z. B. „Beispiel: …“).
 *   type        string|int|float|bool|date|datetime – Pflicht für Felder, die nicht in der Spec stehen.
 *   readOnly    true = nur im Read-Model, nicht in den Options.
 *   optionsOnly true = nur in den Options, nicht im Read-Model.
 *   writable    true = in den Options, obwohl die Spec das Feld nicht beim Anlegen nennt.
 *   deprecated  Text für @deprecated am Read-Model.
 *   enum        Name eines Enums aus src/Model/Enum/ (z. B. 'NetGross') als Typ in Model und Options.
 *   model       ['required' => true]  → Konstruktor-Parameter ohne Default
 *               ['nonNull' => true]   → nicht-nullbarer Typ, fehlender Wert wird beim Hydrieren zu ''/0/0.0/false
 *               ['type' => 'int']     → abweichender Typ im Read-Model
 *               ['sources' => [...]]  → Billomat-Felder, die beim Hydrieren der Reihe nach probiert werden
 *               ['property' => 'x']   → abweichender Property-Name im Read-Model
 *               ['key' => 'x']        → abweichender Schlüssel in toArray() des Read-Models
 *   create      ['nonNull' => true, 'default' => <wert>] → nicht-nullbare Property mit Default
 *               ['constructor' => true] → Pflicht-Parameter im Konstruktor der Create-Options
 *               ['constructor' => true, 'default' => <wert>] → Konstruktor-Parameter mit Default
 *   update      ['skip' => true]      → fehlt in den Update-Options
 *
 * Ressourcen-Schlüssel: 'createOptions' und 'updateOptions' sind optional. Fehlen sie,
 * entsteht nur das Read-Model; 'specEndpoint' nennt dann den Endpunkt für den Drift-Abgleich.
 * 'boolAsInt' => true an 'model' oder an den Options gibt Bool-Felder in toArray() als 1/0 aus.
 * 'filterNulls' => true an 'model' lässt Null-Felder in toArray() des Read-Models weg.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'kunden',
    'docUrl' => 'https://www.billomat.com/en/api/clients/',

    'model' => [
        'class' => 'Client',
        'doc' => [
            'Repräsentiert einen Kunden (Client) aus der Billomat-API.',
            '',
            'Dieses Model bildet die Struktur von GET /clients bzw. GET /clients/{id} ab.',
            'Es ist als Read-Model gedacht: Die Erstellung erfolgt über ClientCreateOptions.',
        ],
    ],

    'createOptions' => [
        'class' => 'ClientCreateOptions',
        'endpoint' => 'POST /api/clients',
        'doc' => [
            'Typisierter Payload für POST /clients gemäß Billomat-API.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'ClientUpdateOptions',
        'endpoint' => 'PUT /api/clients/{id}',
        // Billomat erwartet beim Update 1/0 statt true/false.
        'boolAsInt' => true,
        'doc' => [
            'Typisierter Payload für PUT /clients/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            '',
            'Hinweis: Ein Kunde kann laut Billomat nur bearbeitet werden, wenn er nicht',
            'archiviert ist. Über das Feld `archived` selbst lässt sich der Archivstatus',
            'aber umschalten.',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Kunden.'],
        'name' => ['model' => ['required' => true, 'nonNull' => true], 'doc' => 'Firmenname des Kunden.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Kunden.'],
        'client_number' => ['type' => 'string', 'writable' => true, 'doc' => 'Kundennummer (frei wählbar).'],
        'number' => ['doc' => 'Laufende Nummer (ohne Präfix).'],
        'number_pre' => ['doc' => 'Präfix der Kundennummer.'],
        'number_length' => ['doc' => 'Mindestlänge der Kundennummer.'],
        'street' => ['doc' => 'Straße.'],
        'zip' => ['doc' => 'Postleitzahl.'],
        'city' => ['doc' => 'Ort.'],
        'state' => ['doc' => 'Bundesland / Bezirk / Region.'],
        'country_code' => ['doc' => 'Ländercode des Kunden (ISO 3166-1 Alpha-2).', 'details' => ['Beispiel: "DE", "AT", "CH"']],
        'address' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Von Billomat zusammengesetzte Adresse (mehrzeilig).'],
        'first_name' => ['doc' => 'Vorname.'],
        'last_name' => ['doc' => 'Nachname.'],
        'salutation' => ['doc' => 'Anrede.'],
        'email' => ['doc' => 'E-Mail-Adresse.'],
        'phone' => ['doc' => 'Telefonnummer.'],
        'fax' => ['doc' => 'Faxnummer.'],
        'mobile' => ['doc' => 'Mobilnummer.'],
        'www' => ['doc' => 'Website (ohne Schema).', 'details' => ['Beispiel: "example.com"']],
        'note' => ['doc' => 'Notiz zum Kunden.'],
        'locale' => ['doc' => 'Gebietsschema des Kunden.', 'details' => ['Beispiel: "de_DE"']],
        'tax_number' => ['doc' => 'Steuernummer.'],
        'vat_number' => ['doc' => 'Umsatzsteuer-ID (USt-IdNr.).'],
        'tax_rule' => ['doc' => 'Steuerregel.'],
        'net_gross' => ['doc' => 'Preisbasis.'],
        'currency_code' => ['doc' => 'Währung des Kunden (ISO-Währungscode).', 'details' => ['Beispiel: "EUR"']],
        'debitor_account_number' => ['doc' => 'Debitorennummer.', 'details' => ['Wird häufig als Referenz zur FIBU verwendet.']],
        'price_group' => [
            'doc' => 'Preisgruppe.',
            'details' => ['Artikel können mehrere Preise haben – die Preisgruppe legt fest,', 'welcher Preis für den Kunden verwendet wird.'],
        ],
        'archived' => ['doc' => 'Archivierungsstatus.', 'details' => ['true = archiviert, false = aktiv.']],
        // BC: ClientCreateOptions sendet dunning_run seit jeher immer mit (Default false).
        'dunning_run' => [
            'doc' => 'Automatischer Mahnlauf.',
            'details' => ['true = Kunde wird in automatischem Mahnlauf berücksichtigt.'],
            'create' => ['nonNull' => true, 'default' => false],
        ],
        'reduction' => ['doc' => 'Rabatt in Prozent.'],
        'discount_rate_type' => ['doc' => 'Typ des Skonto-Satzes.'],
        'discount_rate' => ['doc' => 'Skonto-Satz.'],
        'discount_days_type' => ['doc' => 'Typ des Skonto-Zeitraums.'],
        'discount_days' => ['doc' => 'Skonto-Tage.'],
        'due_days_type' => ['doc' => 'Typ der Fälligkeit.'],
        'due_days' => ['doc' => 'Fälligkeit in Tagen nach Rechnungsdatum.'],
        'reminder_due_days_type' => ['doc' => 'Typ der Mahnfälligkeit.'],
        'reminder_due_days' => ['doc' => 'Mahnfälligkeit in Tagen.'],
        'offer_validity_days_type' => ['doc' => 'Typ der Angebotsgültigkeit.'],
        'offer_validity_days' => ['doc' => 'Angebotsgültigkeit in Tagen.'],
        'bank_account_owner' => ['doc' => 'Kontoinhaber.'],
        'bank_number' => ['doc' => 'Bankleitzahl.'],
        'bank_name' => ['doc' => 'Bankname.'],
        'bank_account_number' => ['doc' => 'Kontonummer.'],
        'bank_swift' => ['doc' => 'SWIFT / BIC.'],
        'bank_iban' => ['doc' => 'IBAN.'],
        'sepa_mandate' => ['doc' => 'SEPA-Mandatsreferenz.'],
        'sepa_mandate_date' => ['doc' => 'Ausstelldatum des SEPA-Mandats.'],
        'default_payment_types' => ['doc' => 'Standard-Zahlarten (CSV).', 'details' => ['Beispiel: "CASH,BANK_TRANSFER"']],
        'enable_customerportal' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Kundenportal aktiviert?'],
        'customerportal_url' => [
            'type' => 'string',
            'readOnly' => true,
            'doc' => 'Persönliche URL zum Kundenportal.',
            'deprecated' => 'since 3.1 – Billomat liefert `customerportal_url` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.',
        ],
        'revenue_gross' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Brutto-Umsatz (read-only Auswertungsfeld).'],
        'revenue_net' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Netto-Umsatz (read-only Auswertungsfeld).'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'facturx_identifier' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Kennung für E-Rechnungen (ZUGFeRD/Factur-X).', 'details' => ['Von Billomat geliefert, aber nicht dokumentiert.']],
        'seller_identifier' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Verkäufer-Kennung für E-Rechnungen (ZUGFeRD/Factur-X).', 'details' => ['Von Billomat geliefert, aber nicht dokumentiert.']],
        'dig_exclude' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Von Billomat geliefert, aber nicht dokumentiert.'],
    ],
];
