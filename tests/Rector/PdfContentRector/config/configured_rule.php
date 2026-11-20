<?php

declare(strict_types=1);

use Justpilot\Billomat\Rector\PdfContentRector;
use Rector\Config\RectorConfig;

return RectorConfig::configure()->withRules([PdfContentRector::class]);
