<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Kommentare (Briefe)“ (/letter-comments).
 *
 * Felder und Aufbau: siehe shared/comments.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/comments.php')(
    prefix: 'Letter',
    label: 'einem Brief',
    parentField: 'letter_id',
    specResource: null,
    docUrl: 'https://www.billomat.com/api/briefe/',
);
