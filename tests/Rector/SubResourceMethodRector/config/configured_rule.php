<?php

declare(strict_types=1);

use Justpilot\Billomat\Rector\SubResourceMethodRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([SubResourceMethodRector::class]);
