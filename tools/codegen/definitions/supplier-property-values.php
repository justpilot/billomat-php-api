<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Attribute (Lieferanten)“ (/supplier-property-values).
 *
 * Felder und Aufbau: siehe shared/property-values.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/property-values.php')(
    prefix: 'Supplier',
    label: 'einer Lieferanten-Eigenschaft',
    parentField: 'supplier_id',
    specResource: 'lieferanten/attribute',
    docUrl: 'https://www.billomat.com/api/lieferanten/attribute/',
);
