<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Schlagworte (Artikel)“ (/article-tags).
 *
 * Felder und Aufbau: siehe shared/tags.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/tags.php')(
    prefix: 'Article',
    label: 'einem Artikel',
    parentField: 'article_id',
    specResource: 'artikel/schlagworte',
    docUrl: 'https://www.billomat.com/api/artikel/schlagworte/',
);
