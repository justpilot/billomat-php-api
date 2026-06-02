<?php

/**
 * Beispiel 11: Lieferant mit Tag und Property-Wert anlegen.
 *
 * Voraussetzungen:
 *  - BILLOMAT_ID, BILLOMAT_API_KEY gesetzt.
 *  - Optional: BILLOMAT_SUPPLIER_PROPERTY_ID — ID einer existierenden
 *    Supplier-Property-Definition. Wenn gesetzt, wird ein Wert angelegt.
 *
 * Aufruf:
 *  php examples/11-supplier.php
 *  BILLOMAT_SUPPLIER_PROPERTY_ID=42 php examples/11-supplier.php
 */

declare(strict_types=1);

require __DIR__ . '/../vendor/autoload.php';

use Justpilot\Billomat\Api\SupplierCreateOptions;
use Justpilot\Billomat\Api\SupplierPropertyValueCreateOptions;
use Justpilot\Billomat\Api\SupplierTagCreateOptions;
use Justpilot\Billomat\BillomatClient;
use Justpilot\Billomat\Exception\BillomatException;

$billomatId = getenv('BILLOMAT_ID') ?: null;
$apiKey = getenv('BILLOMAT_API_KEY') ?: null;
$propertyIdRaw = getenv('BILLOMAT_SUPPLIER_PROPERTY_ID') ?: null;

if (!$billomatId || !$apiKey) {
    fwrite(STDERR, "BILLOMAT_ID und BILLOMAT_API_KEY müssen gesetzt sein.\n");
    exit(1);
}

$billomat = BillomatClient::create(billomatId: $billomatId, apiKey: $apiKey);

try {
    // 1) Lieferant anlegen
    $opts = new SupplierCreateOptions(name: sprintf('Beispiel-Lieferant %s', date('Ymd-His')));
    $opts->countryCode = 'DE';
    $opts->email = 'lieferant@example.com';
    $opts->note = 'Angelegt durch examples/11-supplier.php';
    $opts->bankIban = 'DE89370400440532013000';

    $supplier = $billomat->suppliers->create($opts);
    printf("Lieferant #%d angelegt: %s\n", $supplier->id, $supplier->name ?? '?');

    // 2) Schlagwort anhängen
    $billomat->supplierTags->create(
        new SupplierTagCreateOptions(supplierId: $supplier->id, name: 'IT-Dienstleister'),
    );

    // 3) Property-Wert anlegen (nur wenn eine Property-Definition vorgegeben wurde)
    if ($propertyIdRaw !== null) {
        $value = $billomat->supplierPropertyValues->create(
            new SupplierPropertyValueCreateOptions(
                supplierId: $supplier->id,
                supplierPropertyId: (int)$propertyIdRaw,
                value: 'Premium',
            ),
        );
        printf("Property-Wert #%d gesetzt (Property %d).\n", $value->id, (int)$propertyIdRaw);
    } else {
        echo "Hinweis: BILLOMAT_SUPPLIER_PROPERTY_ID nicht gesetzt — Property-Wert übersprungen.\n";
    }
} catch (BillomatException $e) {
    fwrite(STDERR, sprintf("Fehler: %s\n", $e->getMessage()));
    exit(1);
}
