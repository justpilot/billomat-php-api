<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Lieferscheine)“ (/delivery-note-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'DeliveryNote',
    label: 'einem Lieferschein',
    parentField: 'delivery_note_id',
    specResource: null,
    docUrl: 'https://www.billomat.com/api/lieferscheine/',
);
