<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Posten der Eingangsrechnungen (/incoming-items).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Die Posten haben andere Felder als die
 * Positionen der Ausgangsbelege (Betrag statt Menge × Einzelpreis, Kostenstelle,
 * Aufwandskonto), deshalb nicht über shared/items.php. Die Eltern-ID ergänzt
 * ItemsApi beim Anlegen, sie steht nicht in den Options.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'eingangsrechnungen/posten',
    'docUrl' => 'https://www.billomat.com/api/eingangsrechnungen/posten/',

    'model' => [
        'class' => 'IncomingItem',
        'doc' => [
            'Repräsentiert einen Posten einer Eingangsrechnung aus der Billomat-API.',
            '',
            'Dieses Model bildet die Struktur von GET /incoming-items?incoming_id={id} bzw. GET /incoming-items/{id} ab.',
        ],
    ],

    'createOptions' => [
        'class' => 'IncomingItemCreateOptions',
        'endpoint' => 'POST /api/incoming-items',
        // Billomat erwartet 1/0 statt true/false.
        'boolAsInt' => true,
        'doc' => [
            'Typisierter Payload für POST /incoming-items gemäß Billomat-API.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'IncomingItemUpdateOptions',
        'endpoint' => 'PUT /api/incoming-items/{id}',
        // Billomat erwartet 1/0 statt true/false.
        'boolAsInt' => true,
        'doc' => [
            'Typisierter Payload für PUT /incoming-items/{id} (Posten bearbeiten).',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update);',
            'Billomat lässt nicht gesendete Felder unverändert.',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Postens.'],
        // Die Eltern-ID ergänzt die Api-Klasse beim Anlegen, deshalb nicht in den Create-Options.
        'incoming_id' => ['readOnly' => true, 'doc' => 'ID der zugehörigen Eingangsrechnung.'],
        // Spec: INT; als Betrag wie alle Beträge des SDK trotzdem Money.
        'total' => ['type' => 'money', 'doc' => 'Betrag des Postens.', 'create' => ['constructor' => true]],
        'description' => ['doc' => 'Beschreibung des Postens.'],
        'position' => [
            'doc' => 'Laufende Nummer des Postens innerhalb der Eingangsrechnung.',
            'details' => ['Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).'],
        ],
        'tax_name' => ['doc' => 'Steuerbezeichnung (z. B. "USt").'],
        'tax_rate' => ['doc' => 'Steuerrate in Prozent.'],
        'tax_rate_reverse_charge' => ['doc' => 'Steuerrate für das Reverse-Charge-Verfahren.'],
        'tax_included' => ['doc' => 'Ist die Steuer im Betrag enthalten (Brutto)?'],
        'category' => ['doc' => 'Titel der Kategorie.'],
        'type' => [
            'doc' => 'Produkt oder Dienstleistung.',
            'details' => ['Nur bei Rechnungen ins Ausland, abhängig vom Firmenstandort.'],
            'enum' => 'ItemType',
        ],
        'cost_center' => ['doc' => 'Kostenstelle.'],
        'expense_account_number' => ['doc' => 'Nummer des Aufwandskontos.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
    ],
];
