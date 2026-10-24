<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Benutzer-Attribute“ (/user-properties).
 *
 * Felder und Aufbau: siehe shared/properties.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/properties.php')(
    class: 'UserProperty',
    label: 'Benutzer-Eigenschaft',
    specResource: 'einstellungen/benutzer-attribute',
    path: 'user-properties',
    docUrl: 'https://www.billomat.com/api/einstellungen/benutzer-attribute/',
);
