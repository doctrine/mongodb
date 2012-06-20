<?php

$file = __DIR__.'/../vendor/autoload.php';
if (!file_exists($file)) {
        $help = <<<'EOT'
You must set up the project dependencies, run the following commands:
wget http://getcomposer.org/composer.phar
php composer.phar install
EOT;

    throw new RuntimeException($help);
}

$autoload = require_once $file;
$autoload->register('Doctrine\MongoDB\Tests', __DIR__ . '/../tests');