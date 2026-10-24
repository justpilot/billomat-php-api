<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Angebote)“ (/offer-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Offer',
    label: 'einem Angebot',
    parentField: 'offer_id',
    specResource: 'angebote/schlagworte',
    docUrl: 'https://www.billomat.com/api/angebote/schlagworte/',
);
