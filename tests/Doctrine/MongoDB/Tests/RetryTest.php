<?php

namespace Doctrine\MongoDB\Tests;

use PHPUnit_Framework_TestCase;
use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\Cursor;

class RetryTest extends BaseTest
{
    public function setUp()
    {
        $config = new Configuration();
        $config->setRetryConnect(1);
        $config->setRetryQuery(1);
        $this->conn = new Connection(null, array(), $config);
    }

    public function testFunctional()
    {
        $test = $this->conn->selectDatabase('test')->selectCollection('test');
        $doc = array('test' => 'test');
        $test->insert($doc);
        $check = $test->findOne(array('test' => 'test'));
        $this->assertEquals('test', $check['test']);
        $check = $test->find(array('test' => 'test'));
        $this->assertInstanceOf('Doctrine\MongoDB\Cursor', $check);
        $array = $check->toArray();
        $this->assertTrue(is_array($array));
        $array = array_values($array);
        $this->assertEquals('test', $array[0]['test']);
    }

    public function testCollectionRetries()
    {
        $database = $this->conn->selectDatabase('test');
        $mongoCollection = $database->selectCollection('test')->getMongoCollection();
        $collection = new CollectionStub($database, $mongoCollection, $this->conn->getEventManager(), 1);
        $exception = new \MongoException('Test');
        try {
            $collection->testRetries($exception);
            $this->fail();
        } catch (\Exception $e) {
        }
        $this->assertSame($e, $exception);
        $this->assertEquals(2, $collection->numTimesTried);
    }

    public function testConnectionRetries()
    {
        $config = new Configuration();
        $config->setRetryConnect(1);
        $config->setRetryQuery(1);
        $conn = new ConnectionStub(null, array(), $config);
        $exception = new \MongoException('Test');
        try {
            $conn->testRetries($exception);
            $this->fail();
        } catch (\Exception $e) {
        }
        $this->assertSame($e, $exception);
        $this->assertEquals(2, $conn->numTimesTried);
    }

    public function testCursorCursorExceptionRetries()
    {
        $collection = $this->conn->selectDatabase('test')->selectCollection('test');
        $mongoCursor = $collection->find()->getMongoCursor();
        $cursor = new CursorStub($collection, $mongoCursor, array(), array(), 1);
        $exception = new \MongoCursorException('Test');
        try {
            $cursor->testCursorExceptionRetries($exception);
            $this->fail();
        } catch (\Exception $e) {
        }
        $this->assertSame($e, $exception);
        $this->assertEquals(2, $cursor->numTimesTried);
    }

    public function testCursorConnectionExceptionRetries()
    {
        $collection = $this->conn->selectDatabase('test')->selectCollection('test');
        $mongoCursor = $collection->find()->getMongoCursor();
        $cursor = new CursorStub($collection, $mongoCursor, array(), array(), 1);
        $exception = new \MongoConnectionException('Test');
        try {
            $cursor->testConnectionExceptionRetries($exception);
            $this->fail();
        } catch (\Exception $e) {
        }
        $this->assertSame($e, $exception);
        $this->assertEquals(2, $cursor->numTimesTried);
    }
}

class CollectionStub extends Collection
{
    public $numTimesTried = 0;

    public function testRetries($exception)
    {
        $numTimesTried = 0;
        try {
            $this->retry(function() use(&$numTimesTried, $exception) {
                $numTimesTried++;
                throw $exception;
            });
        } catch (\MongoException $e) {}
        $this->numTimesTried = $numTimesTried;
        throw $e;
    }
}

class ConnectionStub extends Connection
{
    public $numTimesTried = 0;

    public function testRetries($exception)
    {
        $numTimesTried = 0;
        try {
            $this->retry(function() use(&$numTimesTried, $exception) {
                $numTimesTried++;
                throw $exception;
            });
        } catch (\MongoException $e) {}
        $this->numTimesTried = $numTimesTried;
        throw $e;
    }
}

class CursorStub extends Cursor
{
    public $numTimesTried = 0;

    public function testCursorExceptionRetries($exception)
    {
        $this->numTimesTried = 0;
        $numTimesTried = 0;
        try {
            $this->retry(function() use(&$numTimesTried, $exception) {
                $numTimesTried++;
                throw $exception;
            }, true);
        } catch (\MongoCursorException $e) {}
        $this->numTimesTried = $numTimesTried;
        throw $e;
    }

    public function testConnectionExceptionRetries($exception)
    {
        $this->numTimesTried = 0;
        $numTimesTried = 0;
        try {
            $this->retry(function() use(&$numTimesTried, $exception) {
                $numTimesTried++;
                throw $exception;
            }, true);
        } catch (\MongoConnectionException $e) {}
        $this->numTimesTried = $numTimesTried;
        throw $e;
    }
}