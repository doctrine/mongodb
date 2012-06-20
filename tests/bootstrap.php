<?php

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
    throw new RuntimeException('Install dependencies to run test suite.');
}

require_once $file;

use Doctrine\Common\ClassLoader;

$classLoader = new ClassLoader('Doctrine\MongoDB\Tests', __DIR__ . '/../tests');
$classLoader->register();
