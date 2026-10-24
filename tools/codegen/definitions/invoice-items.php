<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Positionen (Rechnungen)“ (/invoice-items).
 *
 * Felder und Aufbau: siehe shared/items.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/items.php')(
    prefix: 'Invoice',
    label: 'Rechnungsposition',
    parentField: 'invoice_id',
    specResource: 'rechnungen/positionen',
    docUrl: 'https://www.billomat.com/api/rechnungen/positionen/',
);
