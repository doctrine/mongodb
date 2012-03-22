<?php

namespace Doctrine\MongoDB\Tests;

use PHPUnit_Framework_TestCase;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    protected static $dbName = 'doctrine_mongodb';

    protected $conn;

    public function setUp()
    {
        $config = new Configuration();
        $config->setLoggerCallable(function($msg) {});
        $this->conn = new Connection(null, array(), $config);
    }

    public function tearDown()
    {
        $collections = $this->conn->selectDatabase(self::$dbName)->listCollections();
        foreach ($collections as $collection) {
            $collection->drop();
        }

        $this->conn->close();
        unset($this->conn);
    }
}