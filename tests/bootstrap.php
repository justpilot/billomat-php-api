<?php

declare(strict_types=1);

use Symfony\Component\Dotenv\Dotenv;

require __DIR__ . '/../vendor/autoload.php';

$dotenv = new Dotenv();
$projectDir = dirname(__DIR__);

// Basis-Datei (mit Platzhaltern)
if (is_file($projectDir . '/.env.test')) {
    $dotenv->usePutenv()->load($projectDir . '/.env.test');
}

// Lokale Overrides mit echten Secrets
if (is_file($projectDir . '/.env.test.local')) {
    $dotenv->usePutenv()->load($projectDir . '/.env.test.local');
}