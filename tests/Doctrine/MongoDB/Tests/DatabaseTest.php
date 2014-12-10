<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\LoggableCollection;
use Doctrine\MongoDB\Database;
use Doctrine\Common\EventManager;

class DatabaseTest extends \PHPUnit_Framework_TestCase
{
    public function testCreateCollectionWithMultipleArguments()
    {
        $mongoDB = $this->getMockMongoDB();

        if (version_compare(phpversion('mongo'), '1.4.0', '>=')) {
            $mongoDB->expects($this->once())
                ->method('createCollection')
                ->with('foo', array('capped' => true, 'size' => 10485760, 'max' => 0));
        } else {
            $mongoDB->expects($this->once())
                ->method('createCollection')
                ->with('foo', true, 10485760, 0);
        }

        $mongoDB->expects($this->once())
            ->method('selectCollection')
            ->with('foo')
            ->will($this->returnValue($this->getMockMongoCollection()));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());
        $collection = $database->createCollection('foo', true, 10485760, 0);

        $this->assertInstanceOf('Doctrine\MongoDB\Collection', $collection);
    }

    public function testCreateCollectionWithOptionsArgument()
    {
        $mongoDB = $this->getMockMongoDB();

        if (version_compare(phpversion('mongo'), '1.4.0', '>=')) {
            $mongoDB->expects($this->once())
                ->method('createCollection')
                ->with('foo', array('capped' => true, 'size' => 10485760, 'max' => 0, 'autoIndexId' => false,));
        } else {
            $mongoDB->expects($this->once())
                ->method('createCollection')
                ->with('foo', true, 10485760, 0);
        }

        $mongoDB->expects($this->once())
            ->method('selectCollection')
            ->with('foo')
            ->will($this->returnValue($this->getMockMongoCollection()));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());
        $collection = $database->createCollection('foo', array('capped' => true, 'size' => 10485760, 'autoIndexId' => false));

        $this->assertInstanceOf('Doctrine\MongoDB\Collection', $collection);
    }

    public function testCreateCollectionCappedOptionsAreCast()
    {
        $mongoDB = $this->getMockMongoDB();

        if (version_compare(phpversion('mongo'), '1.4.0', '>=')) {
            $mongoDB->expects($this->once())
                ->method('createCollection')
                ->with('foo', array('capped' => false, 'size' => 0, 'max' => 0));
        } else {
            $mongoDB->expects($this->once())
                ->method('createCollection')
                ->with('foo', false, 0, 0);
        }

        $mongoDB->expects($this->once())
            ->method('selectCollection')
            ->with('foo')
            ->will($this->returnValue($this->getMockMongoCollection()));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());
        $collection = $database->createCollection('foo', array('capped' => 0, 'size' => null, 'max' => null));

        $this->assertInstanceOf('Doctrine\MongoDB\Collection', $collection);
    }

    public function testGetSetSlaveOkay()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '>=')) {
            $this->markTestSkipped('This test is not applicable to driver versions >= 1.3.0');
        }

        $mongoDB = $this->getMockMongoDB();

        $mongoDB->expects($this->once())
            ->method('getSlaveOkay')
            ->will($this->returnValue(false));

        $mongoDB->expects($this->once())
            ->method('setSlaveOkay')
            ->with(true)
            ->will($this->returnValue(false));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());

        $this->assertEquals(false, $database->getSlaveOkay());
        $this->assertEquals(false, $database->setSlaveOkay(true));
    }

    public function testGetSetSlaveOkayReadPreferences()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $mongoDB = $this->getMockMongoDB();

        $mongoDB->expects($this->never())->method('getSlaveOkay');
        $mongoDB->expects($this->never())->method('setSlaveOkay');

        $mongoDB->expects($this->exactly(2))
            ->method('getReadPreference')
            ->will($this->returnValue(array(
                'type' => 0,
                'type_string' => 'primary',
            )));

        $mongoDB->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED)
            ->will($this->returnValue(false));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());

        $this->assertEquals(false, $database->setSlaveOkay(true));
    }

    public function testSetSlaveOkayPreservesReadPreferenceTags()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $mongoDB = $this->getMockMongoDB();

        $mongoDB->expects($this->exactly(2))
            ->method('getReadPreference')
            ->will($this->returnValue(array(
                'type' => 1,
                'type_string' => 'primary preferred',
                'tagsets' => array(array('dc:east')),
            )));

        $mongoDB->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east')))
            ->will($this->returnValue(false));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());

        $this->assertEquals(true, $database->setSlaveOkay(true));
    }

    public function testSetReadPreference()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $mongoDB = $this->getMockMongoDB();

        $mongoDB->expects($this->at(0))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY)
            ->will($this->returnValue(true));

        $mongoDB->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east')))
            ->will($this->returnValue(true));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());

        $this->assertTrue($database->setReadPreference(\MongoClient::RP_PRIMARY));
        $this->assertTrue($database->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east'))));
    }

    public function testSocketTimeoutOptionIsConverted()
    {
        if (version_compare(phpversion('mongo'), '1.5.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.5.0');
        }

        $mongoDB = $this->getMockMongoDB();
        $mongoDB->expects($this->any())
            ->method('command')
            ->with(array('count' => 'foo'), array('socketTimeoutMS' => 1000));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());

        $database->command(array('count' => 'foo'), array('timeout' => 1000));
    }

    public function testSocketTimeoutOptionIsNotConvertedForOlderDrivers()
    {
        if (version_compare(phpversion('mongo'), '1.5.0', '>=')) {
            $this->markTestSkipped('This test is not applicable to driver versions >= 1.5.0');
        }

        $mongoDB = $this->getMockMongoDB();
        $mongoDB->expects($this->any())
            ->method('command')
            ->with(array('count' => 'foo'), array('timeout' => 1000));

        $database = new Database($this->getMockConnection(), $mongoDB, $this->getMockEventManager());

        $database->command(array('count' => 'foo'), array('timeout' => 1000));
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

    private function getMockMongoCollection()
    {
        return $this->getMockBuilder('MongoCollection')
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
