<?php

declare(strict_types=1);

/*
 * Gemeinsame Codegen-Definition für die Schlagworte (Tags) der Billomat-Ressourcen
 * (/{resource}-tags).
 *
 * Billomat beschreibt alle Tag-Ressourcen mit denselben Feldern. Pro Ressource entstehen
 * drei Klassen: das Read-Model eines Tags, das Read-Model eines Tag-Cloud-Eintrags
 * (GET /{resource}-tags ohne Filter) und die Create-Options. Liegt in einem Unterordner,
 * damit der Generator die Datei nicht selbst als Definition lädt.
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * @param string      $prefix       Klassen-Präfix, z. B. 'Invoice'
 * @param string      $label        Bezeichnung mit Artikel im Dativ, z. B. 'einer Rechnung'
 * @param string      $parentField  Billomat-Feld der Eltern-ID, z. B. 'invoice_id'
 * @param string|null $specResource Spec-Slug; null, wenn Billomat die Tags nicht dokumentiert
 */
return static function (string $prefix, string $label, string $parentField, ?string $specResource, string $docUrl): array {
    $path = str_replace('_', '-', substr($parentField, 0, -3)) . '-tags';

    // Ohne Spec muss jedes Feld angeben, ob es beschreibbar ist.
    $writable = null === $specResource ? ['type' => 'string', 'writable' => true] : [];

    $tag = [
        'specResource' => $specResource,
        'docUrl' => $docUrl,

        'model' => [
            'class' => $prefix . 'Tag',
            'doc' => [
                "Schlagwort (Tag) an {$label}.",
                '',
                "Dieses Model bildet die Struktur von GET /{$path}?{$parentField}={id} bzw. GET /{$path}/{id} ab.",
            ],
        ],

        'createOptions' => [
            'class' => $prefix . 'TagCreateOptions',
            'endpoint' => "POST /api/{$path}",
            'doc' => ["Typisierter Payload für POST /{$path} gemäß Billomat-API."],
        ],

        // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
        'fields' => [
            'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Schlagworts.'],
            $parentField => [
                ...$writable,
                'type' => 'int',
                'doc' => 'ID des Datensatzes, an dem das Schlagwort hängt.',
                'model' => ['nonNull' => true],
                'create' => ['constructor' => true],
            ],
            'name' => [
                ...$writable,
                'doc' => 'Name des Schlagworts.',
                'model' => ['nonNull' => true],
                'create' => ['constructor' => true],
            ],
            'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        ],
    ];

    // Die Tag-Cloud steht nicht als eigener Endpunkt in der Spec, deshalb ohne Drift-Abgleich.
    $cloud = [
        'docUrl' => $docUrl,

        'model' => [
            'class' => $prefix . 'TagCloudEntry',
            'doc' => [
                "Eintrag aus der aggregierten Tag-Cloud GET /{$path} (ohne {$parentField}).",
                '',
                'Jedes Schlagwort wird einmal mit seiner Häufigkeit geliefert.',
            ],
        ],

        'fields' => [
            'id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'Interne Billomat-ID des Schlagworts.'],
            'name' => ['type' => 'string', 'readOnly' => true, 'model' => ['nonNull' => true], 'doc' => 'Name des Schlagworts.'],
            'count' => ['type' => 'int', 'readOnly' => true, 'model' => ['nonNull' => true], 'doc' => 'Häufigkeit des Schlagworts.'],
        ],
    ];

    return [$tag, $cloud];
};
