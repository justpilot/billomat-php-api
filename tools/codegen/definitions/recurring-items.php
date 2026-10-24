<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Positionen (Abo-Rechnungen)“ (/recurring-items).
 *
 * Felder und Aufbau: siehe shared/items.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/items.php')(
    prefix: 'Recurring',
    label: 'Position einer Abo-Rechnung',
    parentField: 'recurring_id',
    specResource: 'abo-rechnungen/positionen',
    docUrl: 'https://www.billomat.com/api/abo-rechnungen/positionen/',
    omit: ['created'],
    optionsOnly: [],
);
