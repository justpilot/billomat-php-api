<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Lieferadressen“ (/client-delivery-addresses) eines Kunden.
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Billomat nennt beim Anlegen nur einen Teil der Felder; `label`, `address_line_2`,
 * `address_line_3` und `is_default` stehen nur in den Beispiel-Antworten, werden von der
 * Sandbox aber angenommen und zurückgeliefert (geprüft am 2026-11-22). Ein `customfield`
 * gibt es nicht (405 „does not support a custom field“).
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'lieferadressen',
    'docUrl' => 'https://www.billomat.com/api/lieferadressen/',
    'experimental' => 'Billomat stellt die Lieferadressen noch fertig; laut Doku gelten die Endpunkte erst ab dem 01.01.2027 verbindlich. Bis dahin kann sich die Klasse auch in einem Minor-Release ändern (siehe docs/concepts/backward-compatibility.md).',

    'model' => [
        'class' => 'ClientDeliveryAddress',
        'doc' => [
            'Lieferadresse (Client Delivery Address) eines Kunden.',
            '',
            'Dieses Model bildet die Struktur von GET /client-delivery-addresses bzw. GET /client-delivery-addresses/{id} ab.',
            'Es ist als Read-Model gedacht: Die Erstellung erfolgt über ClientDeliveryAddressCreateOptions.',
        ],
    ],

    'createOptions' => [
        'class' => 'ClientDeliveryAddressCreateOptions',
        'endpoint' => 'POST /api/client-delivery-addresses',
        'boolAsInt' => true,
        'doc' => [
            'Typisierter Payload für POST /client-delivery-addresses gemäß Billomat-API.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'ClientDeliveryAddressUpdateOptions',
        'endpoint' => 'PUT /api/client-delivery-addresses/{id}',
        'boolAsInt' => true,
        'doc' => [
            'Typisierter Payload für PUT /client-delivery-addresses/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            'Der Kunde einer Lieferadresse lässt sich nicht ändern.',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Lieferadresse.'],
        // Die Kunden-ID übergibt `ClientDeliveryAddressesApi::create()` als eigenes Argument; ändern lässt sie sich nicht.
        'client_id' => [
            'type' => 'int',
            'readOnly' => true,
            'doc' => 'ID des Kunden, zu dem die Lieferadresse gehört.',
            'model' => ['required' => true],
        ],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Lieferadresse.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'label' => ['type' => 'string', 'writable' => true, 'doc' => 'Bezeichnung der Lieferadresse.', 'details' => ['Beispiel: "Lager Berlin"']],
        'name' => ['doc' => 'Firmenname.'],
        'salutation' => ['doc' => 'Anrede.'],
        'first_name' => ['doc' => 'Vorname.'],
        'last_name' => ['doc' => 'Nachname.'],
        'street' => ['doc' => 'Straße.'],
        'address_line_2' => ['type' => 'string', 'writable' => true, 'doc' => 'Adresszusatz (Zeile 2).'],
        'address_line_3' => ['type' => 'string', 'writable' => true, 'doc' => 'Adresszusatz (Zeile 3).'],
        'zip' => ['doc' => 'Postleitzahl.'],
        'city' => ['doc' => 'Ort.'],
        'state' => ['doc' => 'Bundesland / Bezirk / Region.'],
        'country_code' => ['doc' => 'Ländercode (ISO 3166-1 Alpha-2).', 'details' => ['Beispiel: "DE", "AT", "CH"']],
        'is_default' => ['type' => 'bool', 'writable' => true, 'doc' => 'Standard-Lieferadresse des Kunden?'],
    ],
];
