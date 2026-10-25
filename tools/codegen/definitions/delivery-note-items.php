<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Positionen (Lieferscheine)“ (/delivery-note-items).
 *
 * Felder und Aufbau: siehe shared/items.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/items.php')(
    prefix: 'DeliveryNote',
    label: 'Lieferscheinposition',
    parentField: 'delivery_note_id',
    specResource: null,
    docUrl: 'https://www.billomat.com/en/api/delivery-notes/items/',
    optionsOnly: ['unit_price', 'tax_name', 'tax_rate', 'reduction', 'total_gross', 'total_net', 'total_gross_unreduced', 'total_net_unreduced', 'created'],
);
