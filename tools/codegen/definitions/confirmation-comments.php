<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kommentare (Auftragsbestätigungen)“ (/confirmation-comments).
 *
 * Felder und Aufbau: siehe shared/comments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/comments.php')(
    prefix: 'Confirmation',
    label: 'einer Auftragsbestätigung',
    parentField: 'confirmation_id',
    specResource: null,
    docUrl: 'https://www.billomat.com/api/auftragsbestaetigungen/',
);
