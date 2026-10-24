<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Benutzer“ (/users).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Billomat bietet für Benutzer nur
 * Lesezugriffe und dokumentiert keine Felder, deshalb ohne Options und ohne
 * Drift-Abgleich.
 *
 * Nach Änderungen: composer generate
 */

$gone = static fn (string $field): string => "since 3.1 – Billomat liefert `{$field}` nicht (mehr) aus (Stand 2026-10), der Wert ist immer `null`. Wird in 4.0 entfernt.";

return [
    'specResource' => null,
    'docUrl' => 'https://www.billomat.com/en/api/users/',

    'model' => [
        'class' => 'User',
        'doc' => ['Benutzer/Mitarbeiter aus der Billomat-API.'],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Benutzers.'],
        'email' => ['type' => 'string', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'E-Mail-Adresse (Login).'],
        'first_name' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Vorname.'],
        'last_name' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Nachname.'],
        'salutation' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Anrede.'],
        'phone' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Telefonnummer.', 'deprecated' => $gone('phone')],
        'mobile' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Mobilnummer.', 'deprecated' => $gone('mobile')],
        'fax' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Faxnummer.', 'deprecated' => $gone('fax')],
        'role_id' => ['type' => 'int', 'readOnly' => true, 'doc' => 'ID der Rolle des Benutzers.'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt des Benutzers.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
        'customfield' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Freies Zusatzfeld (siehe Custom-Meta-Attribute).'],
        'locale' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Sprache der Oberfläche (z. B. "de_DE").'],
        'timezone' => ['type' => 'string', 'readOnly' => true, 'doc' => 'Zeitzone (z. B. "Europe/Berlin").'],
        'is_account_owner' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Ist der Benutzer Inhaber des Accounts?'],
    ],
];
