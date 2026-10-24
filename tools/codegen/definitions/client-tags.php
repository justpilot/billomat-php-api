<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Kunden)“ (/client-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Client',
    label: 'einem Kunden',
    parentField: 'client_id',
    specResource: 'kunden/schlagworte',
    docUrl: 'https://www.billomat.com/api/kunden/schlagworte/',
);
