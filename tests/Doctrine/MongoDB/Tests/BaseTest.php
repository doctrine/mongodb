<?php

namespace Doctrine\MongoDB\Tests;

use PHPUnit_Framework_TestCase;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected static $dbName = 'doctrine_mongodb';

    /**
     * @var Connection
     */
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

    /**
     * @param string $version
     * @param string $operator
     * @return bool
     */
    public static function checkMongoVersion($version, $operator)
    {
        return version_compare(phpversion('mongo'), $version, $operator);
    }

    /**
     * @param $version
     * @param string $operator
     * @throws \PHPUnit_Framework_SkippedTestError
     */
    public static function markTestSkippedByMongoVersion($version, $operator)
    {
        if (self::checkMongoVersion($version, $operator)) {
            self::markTestSkipped("This test is not applicable to driver versions {$operator} {$version}");
        }
    }
}