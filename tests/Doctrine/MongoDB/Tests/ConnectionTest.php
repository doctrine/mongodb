<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Connection;
use PHPUnit_Framework_TestCase;
use Mongo;

class ConnectionTest extends PHPUnit_Framework_TestCase
{
    public function testInitialize()
    {
        $conn = new Connection();
        $this->assertNull($conn->getMongo());
        $conn->initialize();
        $this->assertInstanceOf('MongoClient', $conn->getMongo());
    }

    public function testLog()
    {
        $conn = new Connection();
        $called = false;
        $conn->getConfiguration()->setLoggerCallable(function($msg) use (&$called) {
            $called = $msg;
        });
        $conn->log(array('test'));
        $this->assertEquals(array('test'), $called);
    }

    public function testClose()
    {
        $mockMongo = $this->getMockMongo();
        $mockMongo->expects($this->once())
            ->method('close')
            ->will($this->returnValue(true));
        $conn = $this->getTestConnection($mockMongo);
        $result = $conn->close();
        $this->assertTrue($result);
    }

    public function testConnect()
    {
        $mockMongo = $this->getMockMongo();
        $mockMongo->expects($this->once())
            ->method('connect')
            ->will($this->returnValue(true));
        $conn = $this->getTestConnection($mockMongo);
        $result = $conn->connect();
        $this->assertTrue($result);
    }

    public function testDropDatabase()
    {
        $mockMongo = $this->getMockMongo();
        $mockMongo->expects($this->once())
            ->method('dropDB')
            ->with('test')
            ->will($this->returnValue(true));
        $conn = $this->getTestConnection($mockMongo);
        $result = $conn->dropDatabase('test');
        $this->assertTrue($result);
    }

    public function testListDatabases()
    {
        $mockMongo = $this->getMockMongo();
        $mockMongo->expects($this->once())
            ->method('listDBs')
            ->will($this->returnValue(true));
        $conn = $this->getTestConnection($mockMongo);
        $result = $conn->listDatabases();
        $this->assertTrue($result);
    }

    public function testSelectCollection()
    {
        $mockMongoCollection = $this->getMockMongoCollection();

        $mockMongoDB = $this->getMockMongoDB();
        $mockMongoDB->expects($this->once())
            ->method('selectCollection')
            ->with('coll')
            ->will($this->returnValue($mockMongoCollection));

        $mockMongo = $this->getMockMongo();
        $mockMongo->expects($this->once())
            ->method('selectDB')
            ->with('db')
            ->will($this->returnValue($mockMongoDB));

        $conn = $this->getTestConnection($mockMongo);
        $result = $conn->selectCollection('db', 'coll');
        $this->assertSame($mockMongoCollection, $result->getMongoCollection());
    }

    public function testSelectDatabase()
    {
        $mockMongoDB = $this->getMockMongoDB();

        $mockMongo = $this->getMockMongo();
        $mockMongo->expects($this->once())
            ->method('selectDB')
            ->with('db')
            ->will($this->returnValue($mockMongoDB));

        $conn = $this->getTestConnection($mockMongo);
        $result = $conn->selectDatabase('db');
        $this->assertSame($mockMongoDB, $result->getMongoDB());
    }

    public function testToString()
    {
        $mockMongo = $this->getMockMongo();
        $mockMongo->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue('Test'));

        $conn = $this->getTestConnection($mockMongo);
        $this->assertEquals('Test', (string) $conn);
    }

    private function getTestConnection(Mongo $mongo)
    {
        return new \Doctrine\MongoDB\Connection($mongo);
    }

    private function getMockMongo()
    {
        return $this->getMock('Mongo', array(), array(), '', false, false);
    }

    private function getMockMongoDB()
    {
        return $this->getMock('MongoDB', array(), array(), '', false, false);
    }

    private function getMockMongoCollection()
    {
        return $this->getMock('MongoCollection', array(), array(), '', false, false);
    }
}
