<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „E-Mail-Vorlagen“ (/email-templates).
 *
 * Aufbau und Feld-Schlüssel: siehe clients.php. Das SDK liest E-Mail-Vorlagen nur,
 * deshalb gibt es keine Options; `specEndpoint` dient nur dem Drift-Abgleich.
 *
 * Nach Änderungen: composer generate
 */

return [
    'specResource' => 'einstellungen/email-vorlagen',
    'specEndpoint' => 'POST /api/email-templates',
    'docUrl' => 'https://www.billomat.com/api/einstellungen/email-vorlagen/',

    'model' => [
        'class' => 'EmailTemplate',
        'doc' => [
            'E-Mail-Vorlage aus den Billomat-Einstellungen.',
            '',
            'Dieses Model bildet die Struktur von GET /email-templates bzw. GET /email-templates/{id} ab.',
            '',
            'Billomat liefert den Nachrichtentext im Feld `text` (früher `body`), das',
            'SDK stellt ihn weiterhin als `$body` bereit.',
        ],
    ],

    // Die Reihenfolge entspricht den Konstruktor-Parametern des Read-Models (BC für positionale Aufrufe).
    'fields' => [
        'id' => ['type' => 'int', 'readOnly' => true, 'model' => ['required' => true], 'doc' => 'Interne Billomat-ID der Vorlage.'],
        'name' => ['doc' => 'Name der Vorlage.'],
        'subject' => ['doc' => 'Betreff.'],
        // BC: Property und toArray()-Schlüssel heißen weiterhin `body`; `body` bleibt als Fallback.
        'text' => [
            'doc' => 'Nachrichtentext.',
            'model' => ['property' => 'body', 'key' => 'body', 'sources' => ['text', 'body']],
        ],
        // BC: Billomat liefert kein Absender-Feld mehr.
        'from' => [
            'type' => 'string',
            'readOnly' => true,
            'doc' => 'Absender-Adresse.',
            'details' => ['Billomat liefert dieses Feld nicht mehr, der Wert ist in der Regel `null`.'],
            'model' => ['property' => 'fromAddress'],
        ],
        'is_default' => ['doc' => 'Standard-Vorlage für den Dokumenttyp?'],
        'type' => [
            'doc' => 'Dokumenttyp, für den die Vorlage gilt.',
            'details' => ['Mögliche Werte: INVOICES, OFFERS, CONFIRMATIONS, CREDIT_NOTES, DELIVERY_NOTES, REMINDERS, PAYMENT_THANKS'],
            'enum' => 'EmailTemplateType',
        ],
        'bcc' => ['doc' => 'Erhält der Absender eine BCC-Kopie?'],
        'created' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Erstellungszeitpunkt der Vorlage.'],
        'updated' => ['type' => 'datetime', 'readOnly' => true, 'doc' => 'Zeitpunkt der letzten Änderung.'],
    ],
];
