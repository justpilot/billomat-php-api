<?php

declare(strict_types=1);

use Justpilot\Billomat\Rector\NestedSubResourceRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([NestedSubResourceRector::class]);
