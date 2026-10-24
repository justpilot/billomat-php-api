<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Attribute (Kunden)“ (/client-property-values).
 *
 * Felder und Aufbau: siehe shared/property-values.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/property-values.php')(
    prefix: 'Client',
    label: 'einer Kunden-Eigenschaft',
    parentField: 'client_id',
    specResource: 'kunden/attribute',
    docUrl: 'https://www.billomat.com/api/kunden/attribute/',
);
