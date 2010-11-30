<?php

namespace Doctrine\MongoDB\Tests;

use PHPUnit_Framework_TestCase;

abstract class BaseTest extends PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $conn = new \Doctrine\MongoDB\Connection();
        $dbs = $conn->listDBs();
        foreach ($dbs['databases'] as $db) {
            $collections = $conn->selectDB($db['name'])->listCollections();
            foreach ($collections as $collection) {
                $collection->drop();
            }
        }
        $conn->close();
    }
}