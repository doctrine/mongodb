<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Connection;
use Mongo;

class ConnectionTest extends TestCase
{
    public function testInitializeMongoClient()
    {
        $conn = new Connection();
        $this->assertInstanceOf('MongoClient', $conn->getMongoClient());
    }

    public function testLog()
    {
        $conn = new Connection();
        $called = false;
        $conn->getConfiguration()->setLoggerCallable(function($msg) use (&$called) {
            $called = $msg;
        });
        $conn->log(['test']);
        $this->assertEquals(['test'], $called);
    }

    public function testLogShouldDoNothingWithoutLoggerCallable()
    {
        $conn = new Connection();
        $conn->log(['test']);

        $this->assertNull($conn->getConfiguration()->getLoggerCallable());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testSetMongoShouldThrowExceptionForInvalidArgument()
    {
        $mongoDB = $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();

        $conn = new Connection();
        $conn->setMongo($mongoDB);
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

    public function testSetReadPreference()
    {
        $mongoClient = $this->getMockMongoClient();

        $mongoClient->expects($this->at(0))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY)
            ->will($this->returnValue(true));

        $mongoClient->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, [['dc' => 'east']])
            ->will($this->returnValue(true));

        $conn = $this->getTestConnection($mongoClient);

        $this->assertTrue($conn->setReadPreference(\MongoClient::RP_PRIMARY));
        $this->assertTrue($conn->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, [['dc' => 'east']]));
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

    public function testConnectTimeoutOptionIsConverted()
    {
        /* Since initialize() creates MongoClient directly, we cannot examine
         * the options passed to its constructor.
         *
         * Note: we do not test "wTimeout" conversion, since the driver does not
         * raise a deprecation notice for its usage (see: PHP-1079).
         */
        $conn = new Connection(null, ['timeout' => 10000]);
        $conn->initialize();
    }

    private function getTestConnection($mongo)
    {
        return new Connection($mongo);
    }

    private function getMockMongo()
    {
        return $this->createMock('Mongo', [], [], '', false, false);
    }

    private function getMockMongoClient()
    {
        return $this->getMockBuilder('MongoClient')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockMongoDB()
    {
        return $this->createMock('MongoDB', [], [], '', false, false);
    }

    private function getMockMongoCollection()
    {
        return $this->createMock('MongoCollection', [], [], '', false, false);
    }
}
