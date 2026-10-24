<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Positionen (Angebote)“ (/offer-items).
 *
 * Felder und Aufbau: siehe shared/items.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/items.php')(
    prefix: 'Offer',
    label: 'Angebotsposition',
    parentField: 'offer_id',
    specResource: 'angebote/positionen',
    docUrl: 'https://www.billomat.com/api/angebote/positionen/',
    extra: [
        // BC: in den Create-Options als int (wie in der Spec), im Read-Model als bool.
        'optional' => ['doc' => 'Optionale Position (wird nicht in die Summe eingerechnet).', 'model' => ['type' => 'bool']],
    ],
);
