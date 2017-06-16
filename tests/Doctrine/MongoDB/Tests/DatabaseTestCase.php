<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;

abstract class DatabaseTestCase extends TestCase
{
    protected static $dbName = 'doctrine_mongodb';

    protected $conn;

    public function setUp()
    {
        $config = new Configuration();
        $config->setLoggerCallable(function($msg) {});
        $this->conn = new Connection(null, [], $config);
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

    protected function getServerVersion()
    {
        $result = $this->conn->selectDatabase(self::$dbName)->command(['buildInfo' => 1]);

        return $result['version'];
    }
}
