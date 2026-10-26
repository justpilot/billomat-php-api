<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Positionen (Auftragsbestätigungen)“ (/confirmation-items).
 *
 * Felder und Aufbau: siehe shared/items.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/items.php')(
    prefix: 'Confirmation',
    label: 'Position einer Auftragsbestätigung',
    parentField: 'confirmation_id',
    specResource: null,
    docUrl: 'https://www.billomat.com/api/auftragsbestaetigungen/',
);
