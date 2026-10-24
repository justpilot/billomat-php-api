<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kommentare (Gutschriften)“ (/credit-note-comments).
 *
 * Felder und Aufbau: siehe shared/comments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/comments.php')(
    prefix: 'CreditNote',
    label: 'einer Gutschrift',
    parentField: 'credit_note_id',
    specResource: 'gutschriften/kommentare',
    docUrl: 'https://www.billomat.com/api/gutschriften/kommentare/',
);
