<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kommentare (Angebote)“ (/offer-comments).
 *
 * Felder und Aufbau: siehe shared/comments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/comments.php')(
    prefix: 'Offer',
    label: 'einem Angebot',
    parentField: 'offer_id',
    specResource: 'angebote/kommentare',
    docUrl: 'https://www.billomat.com/api/angebote/kommentare/',
);
