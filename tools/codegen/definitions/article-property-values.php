<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Attribute (Artikel)“ (/article-property-values).
 *
 * Felder und Aufbau: siehe shared/property-values.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/property-values.php')(
    prefix: 'Article',
    label: 'einer Artikel-Eigenschaft',
    parentField: 'article_id',
    specResource: 'artikel/attribute',
    docUrl: 'https://www.billomat.com/api/artikel/attribute/',
);
