<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Artikel“ (/articles).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'artikel',
    'docUrl' => 'https://www.billomat.com/en/api/articles/',

    'model' => [
        'class' => 'Article',
        'doc' => [
            'Artikel (Article) aus der Billomat-API.',
            '',
            'Dieses Model bildet die Struktur von GET /articles bzw. GET /articles/{id} ab.',
            'Es ist als Read-Model gedacht: Die Erstellung erfolgt über ArticleCreateOptions.',
        ],
    ],

    'createOptions' => [
        'class' => 'ArticleCreateOptions',
        'endpoint' => 'POST /api/articles',
        'doc' => [
            'Typisierter Payload für POST /articles gemäß Billomat-API.',
            '',
            'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'ArticleUpdateOptions',
        'endpoint' => 'PUT /api/articles/{id}',
        'doc' => [
            'Typisierter Payload für PUT /articles/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'filter' => [
        'class' => 'ArticleFilter',
        'endpoint' => 'GET /api/articles',
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Artikels.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Erstellungszeitpunkt des Artikels.'],
        'article_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Vollständige Artikelnummer (Präfix + laufende Nummer).'],
        'number' => ['doc' => 'Laufende Nummer (ohne Präfix).'],
        'number_pre' => ['doc' => 'Präfix der Artikelnummer.'],
        'number_length' => ['doc' => 'Mindestlänge der Artikelnummer (wird mit führenden Nullen aufgefüllt).'],
        'title' => [
            'doc' => 'Titel des Artikels.',
            'create' => ['constructor' => true],
        ],
        'description' => ['doc' => 'Beschreibung.'],
        'sales_price' => ['doc' => 'Verkaufspreis.'],
        'currency_code' => ['doc' => 'Währung (ISO-Währungscode).', 'details' => ['Beispiel: "EUR"']],
        'sales_price2' => ['doc' => 'Preis für Kunden der Preisgruppe 2.', 'details' => ['Ohne Angabe gilt der Normalpreis.']],
        'sales_price3' => ['doc' => 'Preis für Kunden der Preisgruppe 3.', 'details' => ['Ohne Angabe gilt der Normalpreis.']],
        'sales_price4' => ['doc' => 'Preis für Kunden der Preisgruppe 4.', 'details' => ['Ohne Angabe gilt der Normalpreis.']],
        'sales_price5' => ['doc' => 'Preis für Kunden der Preisgruppe 5.', 'details' => ['Ohne Angabe gilt der Normalpreis.']],
        // BC: `unit` steht nicht in der Spec, bleibt aber in den Options schreibbar.
        'unit' => [
            'type' => 'string',
            'writable' => true,
            'doc' => 'Bezeichnung der Einheit.',
            'details' => ['Besser `unitId` verwenden.'],
            'optionsOnly' => true,
        ],
        'unit_id' => ['doc' => 'ID der verknüpften Einheit.'],
        'purchase_price' => ['doc' => 'Einkaufspreis.'],
        // BC: steht nicht in der Spec, bleibt aber in den Options schreibbar.
        'purchase_price_currency_code' => [
            'type' => 'string',
            'writable' => true,
            'doc' => 'Währung des Einkaufspreises (ISO-Währungscode).',
            'optionsOnly' => true,
        ],
        'supplier_id' => ['doc' => 'ID des verknüpften Lieferanten.'],
        'tax_id' => ['doc' => 'ID des verknüpften Steuersatzes.'],
        // BC: steht nicht in der Spec, bleibt aber in den Options schreibbar.
        'category_id' => [
            'type' => 'int',
            'writable' => true,
            'doc' => 'ID der Artikelkategorie.',
            'optionsOnly' => true,
        ],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'archived' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Archivierungsstatus.', 'details' => ['true = archiviert, false = aktiv.']],
        'type' => ['enum' => 'ArticleType', 'doc' => 'Artikeltyp: Produkt oder Dienstleistung.'],
        'revenue_account_number' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Erlöskonto.'],
        'cost_center' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Kostenstelle.'],
        'purchase_price_net_gross' => ['enum' => 'NetGross', 'doc' => 'Preisbasis des Einkaufspreises (netto oder brutto).'],
    ],
];
