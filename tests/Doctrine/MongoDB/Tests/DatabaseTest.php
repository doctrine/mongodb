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

    public function testCreateCollectionWithMultipleArguments()
    {
        if (version_compare(phpversion('mongo'), '1.4.0', '>=')) {
            $this->mongodb->expects($this->once())
                ->method('createCollection')
                ->with('foo', array('capped' => true, 'size' => 10485760, 'max' => 0));
        } else {
            $this->mongodb->expects($this->once())
                ->method('createCollection')
                ->with('foo', true, 10485760, 0);
        }

        $database = new Database($this->connection, 'test', $this->getMockEventManager(), '$');
        $collection = $database->createCollection('foo', true, 10485760, 0);

        $this->assertInstanceOf('Doctrine\MongoDB\Collection', $collection);
        $this->assertEquals('foo', $collection->getName());
    }

    public function testCreateCollectionWithOptionsArgument()
    {
        if (version_compare(phpversion('mongo'), '1.4.0', '>=')) {
            $this->mongodb->expects($this->once())
                ->method('createCollection')
                ->with('foo', array('capped' => true, 'size' => 10485760, 'autoIndexId' => false, 'max' => 0));
        } else {
            $this->mongodb->expects($this->once())
                ->method('createCollection')
                ->with('foo', true, 10485760, 0);
        }

        $database = new Database($this->connection, 'test', $this->getMockEventManager(), '$');
        $collection = $database->createCollection('foo', array('capped' => true, 'size' => 10485760, 'autoIndexId' => false));

        $this->assertInstanceOf('Doctrine\MongoDB\Collection', $collection);
        $this->assertEquals('foo', $collection->getName());
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

        $this->mongodb->expects($this->never())->method('getSlaveOkay');
        $this->mongodb->expects($this->never())->method('setSlaveOkay');

        $this->mongodb->expects($this->exactly(2))
            ->method('getReadPreference')
            ->will($this->returnValue(array(
                'type' => 0,
                'type_string' => 'primary',
            )));

        $this->mongodb->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED)
            ->will($this->returnValue(false));

        $database = new Database($this->connection, 'test', $this->getMockEventManager(), '$');

        $this->assertEquals(false, $database->setSlaveOkay(true));
    }

    public function testSetSlaveOkayPreservesReadPreferenceTags()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $this->mongodb->expects($this->exactly(2))
            ->method('getReadPreference')
            ->will($this->returnValue(array(
                'type' => 1,
                'type_string' => 'primary preferred',
                'tagsets' => array(array('dc:east')),
            )));

        $this->mongodb->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east')))
            ->will($this->returnValue(false));

        $database = new Database($this->connection, 'test', $this->getMockEventManager(), '$');

        $this->assertEquals(true, $database->setSlaveOkay(true));
    }

    public function testSetReadPreference()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $this->mongodb->expects($this->at(0))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY)
            ->will($this->returnValue(true));

        $this->mongodb->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east')))
            ->will($this->returnValue(true));

        $database = new Database($this->connection, 'test', $this->getMockEventManager(), '$');

        $this->assertTrue($database->setReadPreference(\MongoClient::RP_PRIMARY));
        $this->assertTrue($database->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east'))));
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
