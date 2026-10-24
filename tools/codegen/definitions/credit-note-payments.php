<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Zahlungen (Gutschriften)“ (/credit-note-payments).
 *
 * Felder und Aufbau: siehe shared/payments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/payments.php')(
    prefix: 'CreditNote',
    label: 'einer Gutschrift',
    parentField: 'credit_note_id',
    specResource: 'gutschriften/zahlungen',
    docUrl: 'https://www.billomat.com/api/gutschriften/zahlungen/',
);
