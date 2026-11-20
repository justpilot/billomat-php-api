<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Auftragsbestätigungen)“ (/confirmation-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Confirmation',
    label: 'einer Auftragsbestätigung',
    parentField: 'confirmation_id',
    specResource: null,
    docUrl: 'https://www.billomat.com/api/auftragsbestaetigungen/',
);
