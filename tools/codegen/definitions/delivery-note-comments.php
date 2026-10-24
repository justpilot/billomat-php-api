<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kommentare (Lieferscheine)“ (/delivery-note-comments).
 *
 * Felder und Aufbau: siehe shared/comments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/comments.php')(
    prefix: 'DeliveryNote',
    label: 'einem Lieferschein',
    parentField: 'delivery_note_id',
    specResource: null,
    docUrl: 'https://www.billomat.com/en/api/delivery-notes/comments/',
);
