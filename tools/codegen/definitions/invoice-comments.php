<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kommentare (Rechnungen)“ (/invoice-comments).
 *
 * Felder und Aufbau: siehe shared/comments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/comments.php')(
    prefix: 'Invoice',
    label: 'einer Rechnung',
    parentField: 'invoice_id',
    specResource: 'rechnungen/kommentare',
    docUrl: 'https://www.billomat.com/api/rechnungen/kommentare/',
);
