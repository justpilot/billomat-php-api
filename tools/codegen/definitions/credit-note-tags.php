<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Gutschriften)“ (/credit-note-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'CreditNote',
    label: 'einer Gutschrift',
    parentField: 'credit_note_id',
    specResource: 'gutschriften/schlagworte',
    docUrl: 'https://www.billomat.com/api/gutschriften/schlagworte/',
);
