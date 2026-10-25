<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;

// Die Konfiguration, die UPGRADE-4.0.md empfiehlt.
return RectorConfig::configure()
    ->withSets([__DIR__.'/../../../../rector/upgrade-4.0.php'])
    ->withImportNames(importShortClasses: false, removeUnusedImports: true);
