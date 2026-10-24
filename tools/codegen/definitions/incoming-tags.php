<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Eingangsrechnungen)“ (/incoming-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Incoming',
    label: 'einer Eingangsrechnung',
    parentField: 'incoming_id',
    specResource: 'eingangsrechnungen/schlagworte',
    docUrl: 'https://www.billomat.com/api/eingangsrechnungen/schlagworte/',
);
