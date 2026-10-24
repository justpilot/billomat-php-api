<?php

declare(strict_types=1);

/*
 * Codegen-Definition für die Ressource „Artikel-Attribute“ (/article-properties).
 *
 * Felder und Aufbau: siehe shared/properties.php.
 *
 * Nach Änderungen: composer generate
 */

return (require __DIR__ . '/shared/properties.php')(
    class: 'ArticleProperty',
    label: 'Artikel-Eigenschaft',
    specResource: 'einstellungen/artikel-attribute',
    path: 'article-properties',
    docUrl: 'https://www.billomat.com/en/api/settings/article-properties/',
);
