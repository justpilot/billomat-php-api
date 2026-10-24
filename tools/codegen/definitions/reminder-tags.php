<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Mahnungen)“ (/reminder-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Reminder',
    label: 'einer Mahnung',
    parentField: 'reminder_id',
    specResource: 'mahnungen/schlagworte',
    docUrl: 'https://www.billomat.com/api/mahnungen/schlagworte/',
);
