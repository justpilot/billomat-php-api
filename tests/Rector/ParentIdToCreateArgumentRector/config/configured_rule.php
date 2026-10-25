<?php

declare(strict_types=1);

use Justpilot\Billomat\Rector\ParentIdToCreateArgumentRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([ParentIdToCreateArgumentRector::class]);
