<?php

/**
 * Beispiel 11: Lieferant mit Tag und Property-Wert anlegen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID und BILLOMAT_API_KEY gesetzt.
 *  - Optional BILLOMAT_SUPPLIER_PROPERTY_ID: ID einer bestehenden
 *    Lieferanten-Property. Wenn gesetzt, wird ein Wert angelegt.
 *
 * Aufruf:
 *  php examples/11-supplier.php
 *  BILLOMAT_SUPPLIER_PROPERTY_ID=42 php examples/11-supplier.php
 */

declare(strict_types=1);

require __DIR__.'/bootstrap.php';

use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\SupplierTagCreateOptions;

$billomat = billomat();

// 1) Lieferant anlegen
$supplier = $billomat->suppliers->create(new SupplierCreateOptions(
    name: sprintf('Beispiel-Lieferant %s', date('Ymd-His')),
    countryCode: 'DE',
    note: 'Angelegt durch examples/11-supplier.php',
    email: 'lieferant@example.com',
    bankIban: 'DE89370400440532013000',
));
$supplierId = $supplier->id;

printf("Lieferant #%d angelegt: %s\n", $supplierId, $supplier->name);

// 2) Schlagwort anhängen
$billomat->suppliers->tags->create(new SupplierTagCreateOptions(supplierId: $supplierId, name: 'IT-Dienstleister'));

// 3) Property-Wert anlegen, nur wenn eine Property vorgegeben wurde
if (null === optionalEnv('BILLOMAT_SUPPLIER_PROPERTY_ID')) {
    echo "Hinweis: BILLOMAT_SUPPLIER_PROPERTY_ID nicht gesetzt, Property-Wert übersprungen.\n";
    exit(0);
}

$propertyId = envId('BILLOMAT_SUPPLIER_PROPERTY_ID');
$value = $billomat->suppliers->propertyValues->create(new SupplierPropertyValueCreateOptions(
    supplierId: $supplierId,
    supplierPropertyId: $propertyId,
    value: 'Premium',
));

printf("Property-Wert #%d gesetzt (Property %d).\n", $value->id, $propertyId);
