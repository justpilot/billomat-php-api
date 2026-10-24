<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kommentare (Eingangsrechnungen)“ (/incoming-comments).
 *
 * Felder und Aufbau: siehe shared/comments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/comments.php')(
    prefix: 'Incoming',
    label: 'einer Eingangsrechnung',
    parentField: 'incoming_id',
    specResource: 'eingangsrechnungen/kommentare',
    docUrl: 'https://www.billomat.com/api/eingangsrechnungen/kommentare/',
    clientFields: false,
);
