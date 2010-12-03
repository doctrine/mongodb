<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\LoggableCollection;
use Doctrine\MongoDB\Database;
use Doctrine\Common\EventManager;
use MongoCollection;
use PHPUnit_Framework_TestCase;

class CollectionTest extends PHPUnit_Framework_TestCase
{
    public function testLog()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('collection'));

        $mockDatabase = $this->getMockDatabase();
        $mockDatabase->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('db'));

        $called = false;
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase, function($msg) use (&$called) {
            $called = $msg;
        });
        $coll->log(array('test' => 'test'));
        $this->assertEquals(array('collection' => 'collection', 'db' => 'db', 'test' => 'test'), $called);
    }

    public function testBatchInsert()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('batchInsert')
            ->with(array(), array())
            ->will($this->returnValue(array()));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $doc = array();
        $result = $coll->batchInsert($doc, array());
        $this->assertEquals(array(), $result);
    }

    public function testUpdate()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('update')
            ->with(array(), array(), array())
            ->will($this->returnValue(array()));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->update(array(), array(), array());
        $this->assertEquals(array(), $result);
    }

    public function testFind()
    {
        $mockMongoCursor = $this->getMockMongoCursor();

        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('find')
            ->with(array(), array())
            ->will($this->returnValue($mockMongoCursor));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->find(array(), array());
        $this->assertEquals($mockMongoCursor, $result->getMongoCursor());
    }

    public function testFindOne()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('findOne')
            ->with(array(), array())
            ->will($this->returnValue(array()));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->findOne(array(), array());
        $this->assertEquals(array(), $result);
    }

    public function testFindAndRemove()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
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

        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->findAndRemove($query, $options);
        $this->assertEquals($document, $result);
    }

    public function testFindAndModify()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
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

        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->findAndUpdate($query, $newObj, $options);
        $this->assertEquals($document, $result);
    }

    public function testCount()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('count')
            ->with(array(), 0, 0)
            ->will($this->returnValue(1));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->count(array(), 0, 0);
        $this->assertEquals(1, $result);
    }

    public function testCreateDBRef()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('createDBRef')
            ->with(array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->createDBRef(array());
        $this->assertEquals(true, $result);
    }

    public function testDeleteIndex()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('deleteIndex')
            ->with(array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->deleteIndex(array());
        $this->assertEquals(true, $result);
    }

    public function testDeleteIndexes()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('deleteIndexes')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->deleteIndexes();
        $this->assertEquals(true, $result);
    }

    public function testDrop()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('drop')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->drop();
        $this->assertEquals(true, $result);
    }

    public function testEnsureIndex()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('ensureIndex')
            ->with(array(), array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->ensureIndex(array(), array());
        $this->assertEquals(true, $result);
    }

    public function testGetDBRef()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('getDBRef')
            ->with(array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->getDBRef(array());
        $this->assertEquals(true, $result);
    }

    public function testGetIndexInfo()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('getIndexInfo')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->getIndexInfo();
        $this->assertEquals(true, $result);
    }

    public function testGetName()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->getName();
        $this->assertEquals(true, $result);
    }

    public function testGroup()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('group')
            ->with(array(), array(), '', array())
            ->will($this->returnValue(array()));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->group(array(), array(), '', array());
        $this->assertEquals(new \Doctrine\MongoDB\ArrayIterator(array()), $result);
    }

    public function testInsert()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('insert')
            ->with(array(), array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $document = array();
        $result = $coll->insert($document, array());
        $this->assertEquals(true, $result);
    }

    public function testRemove()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('remove')
            ->with(array(), array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->remove(array(), array());
        $this->assertEquals(true, $result);
    }

    public function testSave()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('save')
            ->with(array(), array())
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $document = array();
        $result = $coll->save($document, array());
        $this->assertEquals(true, $result);
    }

    public function testValidate()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
        $result = $coll->validate();
        $this->assertEquals(true, $result);
    }

    public function testToString()
    {
        $mockMongoCollection = $this->getMockMongoCollection();
        $mockMongoCollection->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue(true));

        $mockDatabase = $this->getMockDatabase();
        $coll = $this->getTestCollection($mockMongoCollection, $mockDatabase);
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

    private function getMockDatabase()
    {
        return $this->getMock('Doctrine\MongoDB\Database', array(), array(), '', false, false);
    }

    private function getTestCollection(MongoCollection $mongoCollection, Database $db, $loggerCallable = null)
    {
        if (null === $loggerCallable) {
            return new Collection($mongoCollection, $db, new EventManager(), '$');
        }
        return new LoggableCollection($mongoCollection, $db, new EventManager(), '$', $loggerCallable);
    }
}