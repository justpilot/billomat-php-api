<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Briefe)“ (/letter-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Letter',
    label: 'einem Brief',
    parentField: 'letter_id',
    specResource: null,
    docUrl: 'https://www.billomat.com/api/briefe/',
);
