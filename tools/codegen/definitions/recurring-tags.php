<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Abo-Rechnungen)“ (/recurring-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Recurring',
    label: 'einer Abo-Rechnung',
    parentField: 'recurring_id',
    specResource: 'abo-rechnungen/schlagworte',
    docUrl: 'https://www.billomat.com/api/abo-rechnungen/schlagworte/',
);
