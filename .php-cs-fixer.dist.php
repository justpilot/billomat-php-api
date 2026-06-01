<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in([__DIR__ . '/src', __DIR__ . '/tests'])
    ->name('*.php')
    ->ignoreDotFiles(true)
    ->ignoreVCS(true);

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PER-CS' => true,
        '@PER-CS:risky' => true,
        '@Symfony' => true,
        '@Symfony:risky' => true,
        '@PHP84Migration' => true,
        '@PHPUnit100Migration:risky' => true,
        'declare_strict_types' => true,
        'ordered_imports' => ['sort_algorithm' => 'alpha', 'imports_order' => ['class', 'function', 'const']],
        'native_function_invocation' => ['include' => ['@compiler_optimized'], 'scope' => 'namespaced', 'strict' => true],
        'global_namespace_import' => ['import_classes' => true, 'import_constants' => true, 'import_functions' => false],
        'phpdoc_to_comment' => false,
        'no_superfluous_phpdoc_tags' => ['allow_mixed' => true, 'remove_inheritdoc' => true],
        'php_unit_test_class_requires_covers' => false,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__ . '/build/.php-cs-fixer.cache');
