<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kunden-Attribute“ (/client-properties).
 *
 * Felder und Aufbau: siehe shared/properties.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/properties.php')(
    class: 'ClientProperty',
    label: 'Kunden-Eigenschaft',
    specResource: 'einstellungen/kunden-attribute',
    path: 'client-properties',
    docUrl: 'https://www.billomat.com/en/api/settings/client-properties/',
    withOptions: true,
);
