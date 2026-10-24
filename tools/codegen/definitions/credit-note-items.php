<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Positionen (Gutschriften)“ (/credit-note-items).
 *
 * Felder und Aufbau: siehe shared/items.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/items.php')(
    prefix: 'CreditNote',
    label: 'Gutschriftsposition',
    parentField: 'credit_note_id',
    specResource: 'gutschriften/positionen',
    docUrl: 'https://www.billomat.com/api/gutschriften/positionen/',
    extra: [
        'tax_rate_reverse_charge' => ['type' => 'float', 'readOnly' => true, 'doc' => 'Steuerrate bei Reverse-Charge in Prozent.'],
    ],
);
