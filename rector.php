<?php

declare(strict_types=1);

use Rector\Config\RectorConfig;
use Rector\PHPUnit\CodeQuality\Rector\Class_\PreferPHPUnitThisCallRector;
use Rector\PHPUnit\Set\PHPUnitSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ])
    ->withSkip([
        __DIR__ . '/vendor',
        __DIR__ . '/build',
        __DIR__ . '/graphify-out',
        // Bewusste Stilentscheidung: PHPUnit-Assertions werden statisch aufgerufen (self::assert*).
        PreferPHPUnitThisCallRector::class,
    ])
    ->withPhpSets(php84: true)
    ->withSets([
        LevelSetList::UP_TO_PHP_84,
        SetList::CODE_QUALITY,
        SetList::DEAD_CODE,
        SetList::TYPE_DECLARATION,
        PHPUnitSetList::PHPUNIT_100,
        PHPUnitSetList::ANNOTATIONS_TO_ATTRIBUTES,
        PHPUnitSetList::PHPUNIT_CODE_QUALITY,
    ])
    ->withImportNames(importDocBlockNames: true, importShortClasses: false)
    ->withCache(cacheDirectory: __DIR__ . '/build/rector');
