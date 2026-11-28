<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Lieferadressen“ (/client-delivery-addresses).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'lieferadressen',
    'docUrl' => 'https://www.billomat.com/api/lieferadressen/',

    'model' => [
        'class' => 'ClientDeliveryAddress',
        'doc' => [
            'Lieferadresse eines Kunden.',
            '',
            'Rechnungen und Abo-Rechnungen verweisen über client_delivery_address_id darauf.',
        ],
    ],

    'createOptions' => [
        'class' => 'ClientDeliveryAddressCreateOptions',
        'endpoint' => 'POST /api/client-delivery-addresses',
        'doc' => [
            'Typisierter Payload für POST /client-delivery-addresses.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'ClientDeliveryAddressUpdateOptions',
        'endpoint' => 'PUT /api/client-delivery-addresses/{id}',
        'doc' => [
            'Typisierter Payload für PUT /client-delivery-addresses/{id}.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Der Kunde einer Lieferadresse lässt sich nicht ändern.',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Lieferadresse.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Lieferadresse.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        // Die Kunden-ID übergibt `ClientDeliveryAddressesApi::create()` als eigenes Argument.
        'client_id' => ['readOnly' => true, 'model' => ['required' => true], 'doc' => 'ID des Kunden, zu dem die Lieferadresse gehört.'],
        'label' => [
            'type' => 'string',
            'writable' => true,
            'doc' => 'Bezeichnung der Lieferadresse.',
            'details' => ['Von Billomat geliefert und angenommen, aber nicht dokumentiert.'],
        ],
        'name' => ['doc' => 'Firmenname.'],
        'salutation' => ['doc' => 'Anrede.'],
        'first_name' => ['doc' => 'Vorname.'],
        'last_name' => ['doc' => 'Nachname.'],
        'street' => ['doc' => 'Straße.'],
        'address_line_2' => [
            'type' => 'string',
            'writable' => true,
            'doc' => 'Zweite Adresszeile.',
            'details' => ['Von Billomat geliefert und angenommen, aber nicht dokumentiert.'],
        ],
        'address_line_3' => [
            'type' => 'string',
            'writable' => true,
            'doc' => 'Dritte Adresszeile.',
            'details' => ['Von Billomat geliefert und angenommen, aber nicht dokumentiert.'],
        ],
        'zip' => ['doc' => 'Postleitzahl.'],
        'city' => ['doc' => 'Ort.'],
        'state' => ['doc' => 'Bundesland / Bezirk / Region.'],
        'country_code' => ['doc' => 'Ländercode (ISO 3166-1 Alpha-2).', 'details' => ['Beispiel: "DE", "AT", "CH"']],
        'is_default' => [
            'type' => 'bool',
            'writable' => true,
            'doc' => 'Ist das die Standard-Lieferadresse des Kunden?',
            'details' => [
                'Es gibt höchstens eine pro Kunde: Wird eine Adresse zur Standardadresse, verliert die bisherige das Flag.',
                'Von Billomat geliefert und angenommen, aber nicht dokumentiert.',
            ],
        ],
    ],
];
