<?php

namespace Doctrine\MongoDB\Tests;

use PHPUnit_Framework_TestCase;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    protected $conn;

    public function setUp()
    {
        $config = new Configuration();
        $config->setLoggerCallable(function($msg) {
            //print_r($msg);
        });
        $this->conn = new Connection(null, array(), $config);
    }

    public function tearDown()
    {
        $dbs = $this->conn->listDatabases();
        foreach ($dbs['databases'] as $db) {
            $collections = $this->conn->selectDatabase($db['name'])->listCollections();
            foreach ($collections as $collection) {
                $collection->drop();
            }
        }
        $this->conn->close();
    }
}