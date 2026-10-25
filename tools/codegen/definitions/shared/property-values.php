<?php

declare(strict_types=1);

/*
 * Gemeinsame Codegen-Definition für die Eigenschaftswerte (Attribute) der Billomat-Ressourcen
 * (/{resource}-property-values).
 *
 * Alle Eigenschaftswert-Ressourcen haben dieselben Felder. Pro Ressource entstehen das
 * Read-Model und die Create-Options. Liegt in einem Unterordner, damit der Generator die
 * Datei nicht selbst als Definition lädt.
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * @param string $prefix       Klassen-Präfix, z. B. 'Client'
 * @param string $label        Bezeichnung der Eigenschaft im Genitiv, z. B. 'einer Kunden-Eigenschaft'
 * @param string $parentField  Billomat-Feld der Eltern-ID, z. B. 'client_id'
 * @param string $specResource Spec-Slug, z. B. 'kunden/attribute'
 */
return static function (string $prefix, string $label, string $parentField, string $specResource, string $docUrl): array {
    $resource = substr($parentField, 0, -3);
    $path = str_replace('_', '-', $resource) . '-property-values';

    return [
        'specResource' => $specResource,
        'docUrl' => $docUrl,

        'model' => [
            'class' => $prefix . 'PropertyValue',
            'doc' => [
                "Wert {$label}.",
                '',
                "Dieses Model bildet die Struktur von GET /{$path}?{$parentField}={id} bzw. GET /{$path}/{id} ab.",
            ],
        ],

        'createOptions' => [
            'class' => $prefix . 'PropertyValueCreateOptions',
            'endpoint' => "POST /api/{$path}",
            'doc' => ["Typisierter Payload für POST /{$path} gemäß Billomat-API."],
        ],

        // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
        'fields' => [
            'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Eigenschaftswerts.'],
            $parentField => [
                'doc' => 'ID des Datensatzes, zu dem der Wert gehört.',
                'model' => ['nonNull' => true],
                'create' => ['constructor' => true],
            ],
            "{$resource}_property_id" => [
                'doc' => 'ID der Eigenschaft.',
                'model' => ['nonNull' => true],
                'create' => ['constructor' => true],
            ],
            'type' => ['type' => 'string', 'enum' => 'PropertyType', 'readOnly' => true, 'doc' => 'Typ der Eigenschaft (TEXTFIELD, TEXTAREA oder CHECKBOX).'],
            'name' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Name der Eigenschaft.'],
            // Der Typ des Werts hängt von der Eigenschaft ab (Text, Checkbox, …), deshalb `mixed`.
            'value' => [
                'type' => 'mixed',
                'doc' => 'Wert der Eigenschaft.',
                'create' => ['constructor' => true],
            ],
            'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt.'],
            'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
            'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        ],
    ];
};
