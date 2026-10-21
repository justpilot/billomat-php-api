<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Lieferanten“ (/suppliers).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'lieferanten',
    'docUrl' => 'https://www.billomat.com/en/api/suppliers/',

    'model' => [
        'class' => 'Supplier',
        'doc' => [
            'Lieferant (Supplier) aus der Billomat-API.',
            '',
            'Dieses Model bildet die Struktur von GET /suppliers bzw. GET /suppliers/{id} ab.',
            'Es ist als Read-Model gedacht: Die Erstellung erfolgt über SupplierCreateOptions.',
        ],
    ],

    'createOptions' => [
        'class' => 'SupplierCreateOptions',
        'endpoint' => 'POST /api/suppliers',
        'doc' => [
            'Typisierter Payload für POST /suppliers gemäß Billomat-API.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'SupplierUpdateOptions',
        'endpoint' => 'PUT /api/suppliers/{id}',
        'doc' => [
            'Typisierter Payload für PUT /suppliers/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Lieferanten.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Erstellungszeitpunkt des Lieferanten.'],
        'name' => [
            'doc' => 'Firmenname des Lieferanten.',
            'model' => ['required' => true, 'nonNull' => true],
            'create' => ['constructor' => true],
        ],
        // BC: Das Read-Model führt die Kundennummer seit jeher als int, die Spec als ALNUM.
        'client_number' => ['doc' => 'Eigene Kundennummer beim Lieferanten.', 'model' => ['type' => 'int']],
        'salutation' => ['doc' => 'Anrede.'],
        'first_name' => ['doc' => 'Vorname.'],
        'last_name' => ['doc' => 'Nachname.'],
        'street' => ['doc' => 'Straße.'],
        'zip' => ['doc' => 'Postleitzahl.'],
        'city' => ['doc' => 'Ort.'],
        'state' => ['doc' => 'Bundesland / Bezirk / Region.'],
        'country_code' => ['doc' => 'Ländercode (ISO 3166-1 Alpha-2).', 'details' => ['Beispiel: "DE", "AT", "CH"']],
        'address' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Von Billomat zusammengesetzte Adresse (mehrzeilig).'],
        'note' => ['doc' => 'Notiz zum Lieferanten.'],
        'email' => ['doc' => 'E-Mail-Adresse.'],
        'phone' => ['doc' => 'Telefonnummer.'],
        'fax' => ['doc' => 'Faxnummer.'],
        'mobile' => ['doc' => 'Mobilnummer.'],
        'www' => ['doc' => 'Website (ohne Schema).', 'details' => ['Beispiel: "example.com"']],
        'tax_number' => ['doc' => 'Steuernummer.'],
        'vat_number' => ['doc' => 'Umsatzsteuer-ID (USt-IdNr.).'],
        'bank_account_number' => ['doc' => 'Kontonummer.'],
        'bank_account_owner' => ['doc' => 'Kontoinhaber.'],
        'bank_number' => ['doc' => 'Bankleitzahl.'],
        'bank_name' => ['doc' => 'Bankname.'],
        'bank_iban' => ['doc' => 'IBAN.'],
        // BC: Billomat liefert den BIC als `bank_swift`; das Read-Model führt ihn als `bankBic`.
        'bank_bic' => [
            'type' => 'string',
            'writable' => true,
            'doc' => 'SWIFT / BIC.',
            'details' => ['Billomat liefert den BIC als `bank_swift`; `bank_bic` dient nur als Fallback.'],
            'model' => ['sources' => ['bank_swift', 'bank_bic']],
        ],
        'bank_swift' => [
            'optionsOnly' => true,
            'doc' => 'SWIFT / BIC laut Billomat-Doku.',
            'details' => ['Alias zu `bankBic` – beide werden serialisiert.'],
        ],
        'currency_code' => ['doc' => 'Währung des Lieferanten (ISO-Währungscode).', 'details' => ['Leer = Account-Währung.']],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'is_eu_country' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Sitzt der Lieferant in einem EU-Land?'],
        'locale' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Gebietsschema des Lieferanten.', 'details' => ['Beispiel: "de_DE"']],
        'creditor_account_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Kreditorennummer.'],
        'creditor_identifier' => ['doc' => 'SEPA-Gläubiger-ID.'],
        'costs_gross' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Brutto-Kosten (read-only Auswertungsfeld).'],
        'costs_net' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Netto-Kosten (read-only Auswertungsfeld).'],
    ],
];
