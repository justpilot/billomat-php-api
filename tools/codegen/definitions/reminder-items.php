<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Positionen (Mahnungen)“ (/reminder-items).
 *
 * Felder und Aufbau: siehe shared/items.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/items.php')(
    prefix: 'Reminder',
    label: 'Mahnungsposition',
    parentField: 'reminder_id',
    specResource: 'mahnungen/positionen',
    docUrl: 'https://www.billomat.com/api/mahnungen/positionen/',
    withOptions: false,
    omit: ['tax_name', 'tax_rate', 'tax_changed_manually', 'reduction', 'type', 'total_gross_unreduced', 'total_net_unreduced'],
    optionsOnly: ['total_gross', 'total_net', 'created'],
    extra: [
        'total' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Gesamtbetrag der Position.'],
        'is_charge' => ['type' => 'bool', 'readOnly' => true, 'doc' => 'Mahngebühr statt Artikelposition?'],
    ],
);
