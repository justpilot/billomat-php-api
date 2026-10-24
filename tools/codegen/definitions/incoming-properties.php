<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Eingangsrechnungs-Attribute“ (/incoming-properties).
 *
 * Felder und Aufbau: siehe shared/properties.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/properties.php')(
    class: 'IncomingProperty',
    label: 'Eigenschaft für Eingangsrechnungen',
    specResource: 'einstellungen/eingangsrechnung-attribute',
    path: 'incoming-properties',
    docUrl: 'https://www.billomat.com/en/api/settings/incoming-properties/',
);
