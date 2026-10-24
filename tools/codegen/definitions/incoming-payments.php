<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Zahlungen (Eingangsrechnungen)“ (/incoming-payments).
 *
 * Felder und Aufbau: siehe shared/payments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/payments.php')(
    prefix: 'Incoming',
    label: 'einer Eingangsrechnung',
    parentField: 'incoming_id',
    specResource: 'eingangsrechnungen/zahlungen',
    docUrl: 'https://www.billomat.com/api/eingangsrechnungen/zahlungen/',
);
