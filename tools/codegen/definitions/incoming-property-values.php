<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Attribute (Eingangsrechnungen)“ (/incoming-property-values).
 *
 * Felder und Aufbau: siehe shared/property-values.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/property-values.php')(
    prefix: 'Incoming',
    label: 'einer Eigenschaft einer Eingangsrechnung',
    parentField: 'incoming_id',
    specResource: 'eingangsrechnungen/attribute',
    docUrl: 'https://www.billomat.com/api/eingangsrechnungen/attribute/',
);
