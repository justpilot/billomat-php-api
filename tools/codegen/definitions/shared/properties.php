<?php

declare(strict_types=1);

/*
 * Gemeinsame Codegen-Definition für die fünf Attribut-Ressourcen
 * (article-, client-, supplier-, incoming- und user-properties).
 *
 * Billomat beschreibt alle fünf mit denselben Feldern. Jede Ressource bekommt ein
 * eigenes Read-Model; die geteilten PropertyCreateOptions (POST) und
 * PropertyUpdateOptions (PUT) entstehen nur einmal, über client-properties.php. Liegt in einem Unterordner, damit der
 * Generator die Datei nicht selbst als Definition lädt.
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 */

return static fn (string $class, string $label, string $specResource, string $path, string $docUrl, bool $withOptions = false): array => [
    'specResource' => $specResource,
    'specEndpoint' => 'POST /api/' . $path,
    'docUrl' => $docUrl,

    'model' => [
        'class' => $class,
        'doc' => [
            "Definition einer {$label}.",
            '',
            "Dieses Model bildet die Struktur von GET /{$path} bzw. GET /{$path}/{id} ab.",
            'Anlegen über PropertyCreateOptions, Ändern über PropertyUpdateOptions.',
        ],
    ],

    ...($withOptions ? [
        'createOptions' => [
            'class' => 'PropertyCreateOptions',
            'endpoint' => 'POST /api/' . $path,
            // Billomat erwartet 1/0 statt true/false.
            'boolAsInt' => true,
            'doc' => [
                'Geteilte Options-Klasse für POST der Attribut-Definitionen',
                '(article-properties, client-properties, supplier-properties,',
                'incoming-properties, user-properties).',
                '',
                'Null-Werte bedeuten "nicht gesetzt" und werden beim Serialisieren ignoriert.',
            ],
        ],
        'updateOptions' => [
            'class' => 'PropertyUpdateOptions',
            'endpoint' => 'PUT /api/' . $path . '/{id}',
            // Billomat erwartet 1/0 statt true/false.
            'boolAsInt' => true,
            'doc' => [
                'Geteilte Options-Klasse für PUT der Attribut-Definitionen',
                '(article-properties, client-properties, supplier-properties,',
                'incoming-properties, user-properties).',
                '',
                'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
            ],
        ],
    ] : []),

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Attributs.'],
        'name' => [
            'doc' => 'Name des Attributs.',
            'model' => ['nonNull' => true],
            'create' => ['constructor' => true],
        ],
        'type' => [
            'doc' => 'Typ des Attributs.',
            'details' => ['TEXTFIELD = einzeiliges Textfeld, TEXTAREA = mehrzeiliges Textfeld, CHECKBOX = Checkbox'],
            'enum' => 'PropertyType',
        ],
        'default_value' => ['doc' => 'Standardwert.'],
        // BC: PropertyCreateOptions sendet `position` weiterhin, falls gesetzt.
        'position' => [
            'type' => 'int',
            'writable' => true,
            'doc' => 'Sortierposition.',
            'optionsOnly' => true,
        ],
        'is_nvl' => ['doc' => 'Standardwert verwenden, wenn kein anderer Wert gesetzt ist?'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
    ],
];
