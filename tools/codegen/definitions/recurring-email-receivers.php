<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „E-Mail-Empfänger“ von Abo-Rechnungen (/recurring-email-receivers).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'abo-rechnungen/empfaenger',
    'docUrl' => 'https://www.billomat.com/api/abo-rechnungen/empfaenger/',

    'model' => [
        'class' => 'RecurringEmailReceiver',
        'doc' => ['E-Mail-Empfänger für eine Abo-Rechnung (TO/CC/BCC).'],
    ],

    'createOptions' => [
        'class' => 'RecurringEmailReceiverCreateOptions',
        'endpoint' => 'POST /api/recurring-email-receivers',
        'doc' => [
            'Typisierter Payload für POST /recurring-email-receivers.',
            '',
            'Der Empfänger wird zu den bereits vorhandenen Empfängern der Abo-Rechnung hinzugefügt.',
        ],
    ],

    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID des Empfängers.'],
        'recurring_id' => ['model' => ['nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'ID der Abo-Rechnung.'],
        // Billomat liefert „To“/„Cc“/„Bcc“, die Doku nennt Kleinbuchstaben; fromApi() toleriert beides.
        'type' => ['enum' => 'RecurringEmailReceiverType', 'model' => ['fromApi' => true], 'create' => ['constructor' => true], 'doc' => 'Empfänger-Typ (To, Cc oder Bcc).'],
        'address' => ['model' => ['nonNull' => true], 'create' => ['constructor' => true], 'doc' => 'E-Mail-Adresse.'],
    ],
];
