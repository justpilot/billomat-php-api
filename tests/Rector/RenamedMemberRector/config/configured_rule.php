<?php

declare(strict_types=1);

use Justpilot\Billomat\Rector\RenamedMemberRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([RenamedMemberRector::class]);
