<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Lieferanten)“ (/supplier-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Supplier',
    label: 'einem Lieferanten',
    parentField: 'supplier_id',
    specResource: 'lieferanten/schlagworte',
    docUrl: 'https://www.billomat.com/api/lieferanten/schlagworte/',
);
