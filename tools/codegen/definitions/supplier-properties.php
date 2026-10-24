<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Lieferanten-Attribute“ (/supplier-properties).
 *
 * Felder und Aufbau: siehe shared/properties.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/properties.php')(
    class: 'SupplierProperty',
    label: 'Lieferanten-Eigenschaft',
    specResource: 'einstellungen/lieferanten-attribute',
    path: 'supplier-properties',
    docUrl: 'https://www.billomat.com/en/api/settings/supplier-properties/',
);
