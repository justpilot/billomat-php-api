<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Vorlagen“ (/templates).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/vorlagen',
    'docUrl' => 'https://www.billomat.com/api/einstellungen/vorlagen/',

    'model' => [
        'class' => 'Template',
        // BC: toArray() gab Bool-Felder schon immer als 1/0 aus und ließ Null-Felder weg.
        'boolAsInt' => true,
        'filterNulls' => true,
        'doc' => [
            'Repräsentiert eine Vorlage (Template) aus der Billomat-API.',
            '',
            'Dieses Model bildet die Struktur von GET /templates bzw. GET /templates/{id} ab.',
            '',
            'Hinweis laut Doku: `format` und `base64file` kommen nur bei GET /templates/{id}',
            'und nur, wenn `template_type` = UPLOADED ist.',
        ],
    ],

    'createOptions' => [
        'class' => 'TemplateCreateOptions',
        'endpoint' => 'POST /api/templates',
        // Billomat erwartet 1/0 statt true/false.
        'boolAsInt' => true,
        'doc' => [
            'Typisierter Payload für POST /templates gemäß Billomat-API.',
            '',
            'Werden `format` und `base64file` gesetzt, vergibt Billomat den template_type',
            'UPLOADED, sonst DEFINED. Null-Werte werden beim Serialisieren ignoriert.',
        ],
    ],

    'updateOptions' => [
        'class' => 'TemplateUpdateOptions',
        'endpoint' => 'PUT /api/templates/{id}',
        // Billomat erwartet 1/0 statt true/false.
        'boolAsInt' => true,
        'doc' => [
            'Typisierter Payload für PUT /templates/{id} gemäß Billomat-API.',
            '',
            'Nur gesetzte (nicht-null) Felder werden serialisiert (Partial Update).',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'filter' => [
        'class' => 'TemplateFilter',
        'endpoint' => 'GET /api/templates',
        'fields' => [
            'type' => ['enum' => 'TemplateDocumentType'],
            'template_type' => ['enum' => 'TemplateType'],
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Vorlage.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Vorlage.'],
        'type' => [
            'doc' => 'Dokumenttyp, für den die Vorlage gilt.',
            'enum' => 'TemplateDocumentType',
            'create' => ['constructor' => true],
            'update' => ['skip' => true],
        ],
        'template_type' => [
            'type' => 'string',
            'readOnly' => true,
            'enum' => 'TemplateType',
            'doc' => 'Art der Vorlage.',
            'details' => ['Billomat vergibt UPLOADED, wenn eine Datei hochgeladen wurde, sonst DEFINED.'],
        ],
        'name' => ['doc' => 'Bezeichnung der Vorlage.'],
        'format' => [
            'doc' => 'Dateiformat der hochgeladenen Vorlage.',
            'details' => ['Im Read-Model nur bei UPLOADED und GET /templates/{id} verfügbar.'],
            'enum' => 'TemplateFormat',
            'update' => ['skip' => true],
        ],
        'base64file' => [
            'type' => 'string',
            'doc' => 'Base64-kodierte Vorlagendatei.',
            'details' => ['Im Read-Model nur bei UPLOADED und GET /templates/{id} verfügbar.'],
            'update' => ['skip' => true],
        ],
        'is_default' => ['doc' => 'Standard-Vorlage für den Dokumenttyp?', 'model' => ['nonNull' => true]],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'is_background_available' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Ist ein Hintergrundbild für die Vorlage hinterlegt?'],
    ],
];
