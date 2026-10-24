<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Zahlungen (Rechnungen)“ (/invoice-payments).
 *
 * Felder und Aufbau: siehe shared/payments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/payments.php')(
    prefix: 'Invoice',
    label: 'einer Rechnung',
    parentField: 'invoice_id',
    specResource: 'rechnungen/zahlungen',
    docUrl: 'https://www.billomat.com/api/rechnungen/zahlungen/',
);
