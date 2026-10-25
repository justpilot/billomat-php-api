<?php

declare(strict_types=1);

use Justpilot\Billomat\Rector\GetItemsToPropertyRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([GetItemsToPropertyRector::class]);
