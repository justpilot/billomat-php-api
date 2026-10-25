<?php

declare(strict_types=1);

/*
 * Gemeinsame Codegen-Definition für die Positionen (Items) der Billomat-Dokumente
 * (/{resource}-items).
 *
 * Alle Positions-Ressourcen haben im Kern dieselben Felder; Mahnungen und Abo-Rechnungen
 * kennen einige davon nicht ($omit), Angebote, Gutschriften und Mahnungen liefern zusätzliche
 * ($extra, landen hinter `customfield`). Pro Ressource entstehen das Read-Model und – sofern
 * Billomat das Anlegen erlaubt – die Create-Options. Liegt in einem Unterordner, damit der
 * Generator die Datei nicht selbst als Definition lädt.
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * @param string                              $prefix       Klassen-Präfix, z. B. 'Invoice'
 * @param string                              $label        Bezeichnung der Position, z. B. 'Rechnungsposition'
 * @param string                              $parentField  Billomat-Feld der Eltern-ID, z. B. 'invoice_id'
 * @param string|null                         $specResource Spec-Slug; null, wenn Billomat die Positionen nicht dokumentiert
 * @param bool                                $withOptions  false, wenn es keine Create-Options gibt (Mahnungen)
 * @param list<string>                        $omit         Felder, die die Ressource nicht kennt
 * @param list<string>                        $optionsOnly  Felder, die Billomat nicht (mehr) ausliefert (nur noch in den Options)
 * @param array<string, array<string, mixed>> $extra        zusätzliche Felder hinter `customfield`
 */
return static function (
    string $prefix,
    string $label,
    string $parentField,
    ?string $specResource,
    string $docUrl,
    bool $withOptions = true,
    array $omit = [],
    array $optionsOnly = ['created'],
    array $extra = [],
): array {
    $path = str_replace('_', '-', substr($parentField, 0, -3)) . '-items';

    $fields = [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Position.'],
        // Die Eltern-ID ergänzt die Api-Klasse beim Anlegen, deshalb nicht in den Create-Options.
        $parentField => ['type' => 'int', 'readOnly' => true, 'doc' => 'ID des zugehörigen Dokuments.'],
        'article_id' => ['type' => 'int', 'writable' => true, 'doc' => 'ID des Artikels (falls verknüpft).'],
        'position' => [
            'type' => 'int',
            'writable' => true,
            'doc' => 'Laufende Positionsnummer innerhalb des Dokuments.',
            'details' => ['Wird beim Anlegen normalerweise automatisch vergeben (anhängen ans Ende).'],
        ],
        'unit' => ['type' => 'string', 'writable' => true, 'doc' => 'Einheit (z. B. "Stück", "Stunde").'],
        'quantity' => ['type' => 'float', 'writable' => true, 'doc' => 'Menge.', 'model' => ['nonNull' => true], 'create' => ['constructor' => true]],
        'unit_price' => ['type' => 'float', 'writable' => true, 'doc' => 'Einzelpreis.', 'model' => ['required' => true], 'create' => ['constructor' => true]],
        'tax_name' => ['type' => 'string', 'writable' => true, 'doc' => 'Steuerbezeichnung (z. B. "MwSt").'],
        'tax_rate' => ['type' => 'float', 'writable' => true, 'doc' => 'Steuerrate in Prozent.'],
        'tax_changed_manually' => [
            'type' => 'bool',
            'writable' => true,
            'doc' => 'Flag, ob die Steuerrate manuell geändert wurde.',
            'details' => ['Laut Doku Pflicht, *wenn* nicht der Standard-Steuersatz angewendet werden soll.'],
        ],
        'title' => ['type' => 'string', 'writable' => true, 'doc' => 'Titel der Position.'],
        'description' => ['type' => 'string', 'writable' => true, 'doc' => 'Beschreibung der Position.'],
        'reduction' => ['type' => 'string', 'writable' => true, 'doc' => 'Positionsrabatt (absolut oder als Prozentwert, z. B. "10" oder "10%").'],
        'type' => ['type' => 'string', 'writable' => true, 'enum' => 'ItemType', 'doc' => 'Typ der Position (Produkt/Dienstleistung).'],
        'total_gross' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag brutto.'],
        'total_net' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag netto.'],
        'total_gross_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Unreduzierter Gesamtbetrag brutto.'],
        'total_net_unreduced' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Unreduzierter Gesamtbetrag netto.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Position.'],
    ];
    $fields = array_diff_key($fields, array_flip($omit));

    foreach ($optionsOnly as $name) {
        if (!empty($fields[$name]['readOnly'])) {
            unset($fields[$name]);
        } else {
            $fields[$name]['optionsOnly'] = true;
        }
    }

    $fields['customfield'] = ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'];
    $fields += $extra;

    $definition = [
        'specResource' => $specResource,
        'docUrl' => $docUrl,

        'model' => [
            'class' => $prefix . 'Item',
            'doc' => [
                "Repräsentiert eine {$label} aus der Billomat-API.",
                '',
                "Dieses Model bildet die Struktur von GET /{$path}?{$parentField}={id} bzw. GET /{$path}/{id} ab.",
            ],
        ],

        'fields' => $fields,
    ];

    if ($withOptions) {
        // BC: toArray() war schon immer anders sortiert als die Properties.
        $toArray = [
            'type', 'article_id', 'title', 'description', 'quantity', 'unit_price', 'unit', 'tax_name', 'tax_rate',
            'tax_changed_manually', 'reduction', 'position',
            ...array_keys(array_filter($extra, static fn (array $f): bool => empty($f['readOnly']))),
        ];
        $definition['createOptions'] = [
            'class' => $prefix . 'ItemCreateOptions',
            'endpoint' => "POST /api/{$path}",
            'toArray' => $toArray,
            'doc' => [
                "Typisierter Payload für POST /{$path} gemäß Billomat-API",
                'sowie für eingebettete Positionen beim Anlegen des Dokuments.',
                '',
                'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
            ],
        ];
        $definition['updateOptions'] = [
            'class' => $prefix . 'ItemUpdateOptions',
            'endpoint' => "PUT /api/{$path}/{id}",
            'toArray' => $toArray,
            'doc' => [
                "Typisierter Payload für PUT /{$path}/{id} (Position bearbeiten).",
                '',
                'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update);',
                'Billomat lässt nicht gesendete Felder unverändert.',
            ],
        ];
    } else {
        $definition['specEndpoint'] = "POST /api/{$path}";
    }

    return $definition;
};
