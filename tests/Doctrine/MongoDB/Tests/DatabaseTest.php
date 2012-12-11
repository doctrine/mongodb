<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\LoggableCollection;
use Doctrine\MongoDB\Database;
use Doctrine\Common\EventManager;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    private $connection;
    private $mongo;
    private $mongodb;

    public function setUp()
    {
        $this->connection = $this->getMockConnection();
        $this->mongo = $this->getMockMongo();
        $this->mongodb = $this->getMockMongoDB();

        $this->connection->expects($this->any())
            ->method('getMongo')
            ->will($this->returnValue($this->mongo));

        $this->mongo->expects($this->any())
            ->method('selectDB')
            ->will($this->returnValue($this->mongodb));
    }

    public function testGetSetSlaveOkay()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '>=')) {
            $this->markTestSkipped('This test is not applicable to driver versions >= 1.3.0');
        }

        $this->mongodb->expects($this->once())
            ->method('getSlaveOkay')
            ->will($this->returnValue(false));

        $this->mongodb->expects($this->once())
            ->method('setSlaveOkay')
            ->with(true)
            ->will($this->returnValue(false));

        $database = new Database($this->connection, 'test', $this->getMockEventManager(), '$');

        $this->assertEquals(false, $database->getSlaveOkay());
        $this->assertEquals(false, $database->setSlaveOkay(true));
    }

    public function testGetSetSlaveOkayReadPreferences()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $this->mongodb->expects($this->once())
            ->method('getReadPreference')
            ->will($this->returnValue(\MongoClient::RP_PRIMARY));

        $this->mongodb->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED)
            ->will($this->returnValue(false));

        $database = new Database($this->connection, 'test', $this->getMockEventManager(), '$');

        $this->assertEquals(false, $database->setSlaveOkay(true));
    }

    private function getMockConnection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockEventManager()
    {
        return $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockMongo()
    {
        return $this->getMockBuilder('Mongo')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockMongoDB()
    {
        return $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
