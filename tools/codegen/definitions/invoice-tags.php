<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Rechnungen)“ (/invoice-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Invoice',
    label: 'einer Rechnung',
    parentField: 'invoice_id',
    specResource: 'rechnungen/schlagworte',
    docUrl: 'https://www.billomat.com/api/rechnungen/schlagworte/',
);
