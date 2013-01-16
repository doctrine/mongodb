<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\LoggableCollection;
use Doctrine\MongoDB\Database;
use Doctrine\Common\EventManager;
use MongoCollection;
use PHPUnit_Framework_TestCase;

class CollectionTest extends PHPUnit_Framework_TestCase
{
    public function testLog()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('collection'));

        $mockDatabase = $this->getMockDatabase();
        $mockDatabase->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('db'));

        $called = false;
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase, function($msg) use (&$called) {
            $called = $msg;
        });
        $coll->log(array('test' => 'test'));
        $this->assertEquals(array('collection' => 'collection', 'db' => 'db', 'test' => 'test'), $called);
    }

    public function testBatchInsert()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mockDatabase = $this->getMockDatabase();

        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $doc = array();
        $result = $coll->batchInsert($doc, array());
        $this->assertEquals(array(), $result);
    }

    public function testUpdate()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('update')
            ->with(array(), array(), array())
            ->will($this->returnValue(array()));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->update(array(), array(), array());
        $this->assertEquals(array(), $result);
    }

    public function testFind()
    {
        $mockConnection = $this->getMockConnection();
        $mockMongoCursor = $this->getMockMongoCursor();

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('find')
            ->with(array(), array())
            ->will($this->returnValue($mockMongoCursor));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->find(array(), array());
        $this->assertEquals($mockMongoCursor, $result->getMongoCursor());
    }

    public function testFindOne()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('findOne')
            ->with(array(), array())
            ->will($this->returnValue(array()));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->findOne(array(), array());
        $this->assertEquals(array(), $result);
    }

    public function testFindAndRemove()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('coll_name'));

        $query = array('name' => 'jon');
        $options = array('safe' => true);
        $command = array(
            'findandmodify' => 'coll_name',
            'safe' => true,
            'query' => $query,
            'remove' => true,
        );

        $document = array('_id' => new \MongoId(), 'test' => 'cool');
        $mockDatabase = $this->getMockDatabase();
        $mockDatabase->expects($this->once())
            ->method('command')
            ->with($command)
            ->will($this->returnValue(array('value' => $document)));

        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->findAndRemove($query, $options);
        $this->assertEquals($document, $result);
    }

    public function testFindAndModify()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->any())
            ->method('getName')
            ->will($this->returnValue('coll_name'));

        $query = array('name' => 'jon');
        $newObj = array('name' => 'ok');
        $options = array('safe' => true);
        $command = array(
            'findandmodify' => 'coll_name',
            'query' => $query,
            'safe' => true,
            'update' => array(
                'name' => 'ok'
            ),
        );

        $document = array('_id' => new \MongoId(), 'test' => 'cool');
        $mockDatabase = $this->getMockDatabase();
        $mockDatabase->expects($this->once())
            ->method('command')
            ->with($command)
            ->will($this->returnValue(array('value' => $document)));

        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->findAndUpdate($query, $newObj, $options);
        $this->assertEquals($document, $result);
    }

    public function testCount()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('count')
            ->with(array(), 0, 0)
            ->will($this->returnValue(1));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->count(array(), 0, 0);
        $this->assertEquals(1, $result);
    }

    public function testCreateDBRef()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('createDBRef')
            ->with(array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->createDBRef(array());
        $this->assertEquals(true, $result);
    }

    public function testDeleteIndex()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('deleteIndex')
            ->with(array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->deleteIndex(array());
        $this->assertEquals(true, $result);
    }

    public function testDeleteIndexes()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('deleteIndexes')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->deleteIndexes();
        $this->assertEquals(true, $result);
    }

    public function testDrop()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('drop')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->drop();
        $this->assertEquals(true, $result);
    }

    public function testEnsureIndex()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('ensureIndex')
            ->with(array(), array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->ensureIndex(array(), array());
        $this->assertEquals(true, $result);
    }

    public function testGetDBRef()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('getDBRef')
            ->with(array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->getDBRef(array());
        $this->assertEquals(true, $result);
    }

    public function testGetIndexInfo()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('getIndexInfo')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->getIndexInfo();
        $this->assertEquals(true, $result);
    }

    public function testIsFieldIndexedTrue()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('getIndexInfo')
            ->will($this->returnValue(array(array('key' => array('test' => 1)))));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->isFieldIndexed('test');
        $this->assertEquals(true, $result);
    }

    public function testIsFieldIndexedFalse()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('getIndexInfo')
            ->will($this->returnValue(array(array('key' => array('test' => 1)))));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->isFieldIndexed('doesnt-exist');
        $this->assertEquals(false, $result);
    }

    public function testGetName()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->getName();
        $this->assertEquals(true, $result);
    }

    public function testGroupWithNonEmptyOptionsArray()
    {
        $expectedOptions = array(
            'condition' => array(),
            'finalize' => new \MongoCode(''),
        );

        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('group')
            ->with(array(), array(), $this->isInstanceOf('MongoCode'), $this->equalTo($expectedOptions))
            ->will($this->returnValue(array()));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->group(array(), array(), '', array('condition' => array(), 'finalize' => ''));
        $this->assertEquals(new \Doctrine\MongoDB\ArrayIterator(array()), $result);
    }

    public function testGroupWithEmptyOptionsArray()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('group')
            ->with(array(), array(), $this->isInstanceOf('MongoCode'))
            ->will($this->returnValue(array()));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->group(array(), array(), '');
        $this->assertEquals(new \Doctrine\MongoDB\ArrayIterator(array()), $result);
    }

    public function testInsert()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(array(), array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $document = array();
        $result = $coll->insert($document, array());
        $this->assertEquals(true, $result);
    }

    public function testRemove()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('remove')
            ->with(array(), array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->remove(array(), array());
        $this->assertEquals(true, $result);
    }

    public function testSave()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('save')
            ->with(array(), array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $document = array();
        $result = $coll->save($document, array());
        $this->assertArrayHasKeyValue(array('ok' => 1.0), $result);
    }

    public function testGetSetSlaveOkay()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '>=')) {
            $this->markTestSkipped('This test is not applicable to driver versions >= 1.3.0');
        }

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('getSlaveOkay')
            ->will($this->returnValue(false));

        $mongoCollection->expects($this->once())
            ->method('setSlaveOkay')
            ->with(true)
            ->will($this->returnValue(false));

        $collection = $this->getTestCollection($this->getMockConnection(), $mongoCollection, $this->getMockDatabase());

        $this->assertEquals(false, $collection->getSlaveOkay());
        $this->assertEquals(false, $collection->setSlaveOkay(true));
    }

    public function testGetSetSlaveOkayReadPreferences()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->never())->method('getSlaveOkay');
        $mongoCollection->expects($this->never())->method('setSlaveOkay');

        $mongoCollection->expects($this->exactly(2))
            ->method('getReadPreference')
            ->will($this->returnValue(array(
                'type' => 0,
                'type_string' => 'primary',
            )));

        $mongoCollection->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED);

        $collection = $this->getTestCollection($this->getMockConnection(), $mongoCollection, $this->getMockDatabase());

        $this->assertEquals(false, $collection->setSlaveOkay(true));
    }

    public function testSetSlaveOkayPreservesReadPreferenceTags()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->exactly(2))
            ->method('getReadPreference')
            ->will($this->returnValue(array(
                'type' => 1,
                'type_string' => 'primary preferred',
                'tagsets' => array(array('dc:east')),
            )));

        $mongoCollection->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east')))
            ->will($this->returnValue(false));

        $collection = $this->getTestCollection($this->getMockConnection(), $mongoCollection, $this->getMockDatabase());

        $this->assertEquals(true, $collection->setSlaveOkay(true));
    }

    public function testValidate()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $result = $coll->validate();
        $this->assertEquals(true, $result);
    }

    public function testToString()
    {
        $mockConnection = $this->getMockConnection();
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockConnection, $mongoCollection, $mockDatabase);
        $document = array();
        $result = $coll->__toString();
        $this->assertEquals(true, $result);
    }

    private function getMockMongoCursor()
    {
        return $this->getMock('MongoCursor', array(), array(), '', false, false);
    }

    private function getMockMongoCollection()
    {
        return $this->getMock('MongoCollection', array(), array(), '', false, false);
    }

    private function getMockMongoDB()
    {
        return $this->getMock('MongoDB', array(), array(), '', false, false);
    }

    private function getMockDatabase()
    {
        return $this->getMock('Doctrine\MongoDB\Database', array(), array(), '', false, false);
    }

    private function getMockConnection()
    {
        return $this->getMock('Doctrine\MongoDB\Connection', array(), array(), '', false, false);
    }

    private function getTestCollection(Connection $connection, MongoCollection $mongoCollection, Database $db, $loggerCallable = null)
    {
        if (null === $loggerCallable) {
            $collection = new TestCollectionStub($connection, $mongoCollection->getName(), $db, new EventManager(), '$');
            $collection->setMongoCollection($mongoCollection);
            return $collection;
        }
        $collection = new TestLoggableCollectionStub($connection, $mongoCollection->getName(), $db, new EventManager(), '$', $loggerCallable);
        $collection->setMongoCollection($mongoCollection);
        return $collection;
    }

    private function assertArrayHasKeyValue($expected, $array, $message = '')
    {
        foreach ((array) $expected as $key => $value) {
            $this->assertArrayHasKey($key, $expected, $message);
            $this->assertEquals($value, $expected[$key], $message);
        }
    }
}

class TestLoggableCollectionStub extends LoggableCollection
{
    public function setMongoCollection($mongoCollection)
    {
        $this->mongoCollection = $mongoCollection;
    }

    public function getMongoCollection()
    {
        return $this->mongoCollection;
    }
}

class TestCollectionStub extends Collection
{
    public function setMongoCollection($mongoCollection)
    {
        $this->mongoCollection = $mongoCollection;
    }

    public function getMongoCollection()
    {
        return $this->mongoCollection;
    }
}
