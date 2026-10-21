<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kontakte“ (/contacts) eines Kunden.
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'kunden/kontakte',
    'docUrl' => 'https://www.billomat.com/en/api/clients/contacts/',

    'model' => [
        'class' => 'Contact',
        'doc' => [
            'Ansprechpartner (Contact) eines Kunden.',
            '',
            'Dieses Model bildet die Struktur von GET /contacts bzw. GET /contacts/{id} ab.',
            'Es ist als Read-Model gedacht: Die Erstellung erfolgt über ContactCreateOptions.',
        ],
    ],

    'createOptions' => [
        'class' => 'ContactCreateOptions',
        'endpoint' => 'POST /api/contacts',
        'doc' => [
            'Typisierter Payload für POST /contacts gemäß Billomat-API.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'ContactUpdateOptions',
        'endpoint' => 'PUT /api/contacts/{id}',
        'doc' => [
            'Typisierter Payload für PUT /contacts/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Der Kunde eines Kontakts lässt sich nicht ändern.',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Kontakts.'],
        'client_id' => [
            'doc' => 'ID des Kunden, zu dem der Kontakt gehört.',
            'model' => ['required' => true, 'nonNull' => true],
            'create' => ['constructor' => true],
            'update' => ['skip' => true],
        ],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Kontakts.'],
        'label' => ['doc' => 'Bezeichnung des Kontakts.'],
        'salutation' => ['doc' => 'Anrede.'],
        'first_name' => ['doc' => 'Vorname.'],
        'last_name' => ['doc' => 'Nachname.'],
        'street' => ['doc' => 'Straße.'],
        'zip' => ['doc' => 'Postleitzahl.'],
        'city' => ['doc' => 'Ort.'],
        'state' => ['doc' => 'Bundesland / Bezirk / Region.'],
        'country_code' => ['doc' => 'Ländercode (ISO 3166-1 Alpha-2).', 'details' => ['Beispiel: "DE", "AT", "CH"']],
        'email' => ['doc' => 'E-Mail-Adresse.'],
        'phone' => ['doc' => 'Telefonnummer.'],
        'fax' => ['doc' => 'Faxnummer.'],
        'mobile' => ['doc' => 'Mobilnummer.'],
        // BC: `note` steht nicht in der Spec, bleibt aber in den Options schreibbar.
        'note' => [
            'type' => 'string',
            'writable' => true,
            'doc' => 'Notiz zum Kontakt.',
            'deprecated' => 'since 3.1 – Billomat liefert `note` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.',
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'name' => ['doc' => 'Firmenname.'],
        'address' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Von Billomat zusammengesetzte Adresse (mehrzeilig).'],
        'www' => ['doc' => 'Website (ohne Schema).', 'details' => ['Beispiel: "example.com"']],
        'locale' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Gebietsschema des Kontakts.', 'details' => ['Beispiel: "de_DE"']],
    ],
];
