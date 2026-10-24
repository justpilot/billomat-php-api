<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kategorien“ von Eingangsrechnungen (/incoming-categories).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Billomat bietet nur Lesezugriff,
 * die Spec nennt keine Felder; `specEndpoint` prüft nur, dass der Endpunkt existiert.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'eingangsrechnungen/kategorien',
    'specEndpoint' => 'GET /api/incoming-categories',
    'docUrl' => 'https://www.billomat.com/api/eingangsrechnungen/kategorien/',

    'model' => [
        'class' => 'IncomingCategory',
        'doc' => ['Kategorie einer Eingangsrechnung (z. B. „goods“, „services“).'],
    ],

    'fields' => [
        // BC: Die ID ist seit jeher ein String.
        'id' => ['type' => 'string', 'readOnly' => true, 'model' => ['required' => true, 'nonNull' => true], 'doc' => 'Kennung der Kategorie.'],
        'title' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Bezeichnung der Kategorie.'],
        'description' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Beschreibung der Kategorie.'],
    ],
];
