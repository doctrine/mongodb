<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\ArrayIterator;
use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\Database;
use Doctrine\MongoDB\Tests\Constraint\ArrayHasKeyAndValue;
use MongoCollection;

class CollectionTest extends \PHPUnit_Framework_TestCase
{
    const collectionName = 'collection';

    public function testAggregateWithPipelineArgument()
    {
        $pipeline = array(
            array('$match' => array('_id' => 'bar')),
            array('$project' => array('_id' => 1)),
        );
        $aggregated = array(array('_id' => 'bar'));
        $commandResult = array('ok' => 1, 'result' => $aggregated);

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with(array('aggregate' => self::collectionName, 'pipeline' => $pipeline))
            ->will($this->returnValue($commandResult));

        $coll = $this->getTestCollection($database);
        $result = $coll->aggregate($pipeline);

        $arrayIterator = new ArrayIterator($aggregated);
        $arrayIterator->setCommandResult($commandResult);

        $this->assertEquals($arrayIterator, $result);
    }

    public function testAggregateWithOperatorArguments()
    {
        $firstOp = array('$match' => array('_id' => 'bar'));
        $secondOp = array('$project' => array('_id' => 1));
        $aggregated = array(array('_id' => 'bar'));
        $commandResult = array('ok' => 1, 'result' => $aggregated);

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with(array('aggregate' => self::collectionName, 'pipeline' => array($firstOp, $secondOp)))
            ->will($this->returnValue($commandResult));

        $coll = $this->getTestCollection($database);
        $result = $coll->aggregate($firstOp, $secondOp);

        $arrayIterator = new ArrayIterator($aggregated);
        $arrayIterator->setCommandResult($commandResult);

        $this->assertEquals($arrayIterator, $result);
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testAggregateShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(array('ok' => 0)));

        $coll = $this->getTestCollection($database);
        $coll->aggregate(array());
    }

    public function testBatchInsert()
    {
        $docs = array(array('x' => 1, 'y' => 2));
        $options = array('w'=> 0);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('batchInsert')
            ->with($docs, $options)
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($coll->batchInsert($docs, $options));
    }

    public function testCountWithParameters()
    {
        $query = array('x' => 1);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('count')
            ->with($query, 1, 1)
            ->will($this->returnValue(1));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(1, $coll->count($query, 1, 1));
    }

    public function testCountWithoutParameters()
    {
        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('count')
            ->with(array(), 0, 0)
            ->will($this->returnValue(1));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(1, $coll->count());
    }

    public function testCreateDBRef()
    {
        $document = array('_id' => 1);
        $dbRef = array('$ref' => 'test', '$id' => 1);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('createDBRef')
            ->with($document)
            ->will($this->returnValue($dbRef));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals($dbRef, $coll->createDBRef($document));
    }

    public function testDeleteIndex()
    {
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('deleteIndex')
            ->with('foo')
            ->will($this->returnValue(array()));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(array(), $coll->deleteIndex('foo'));
    }

    public function testDeleteIndexes()
    {
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('deleteIndexes')
            ->will($this->returnValue(array()));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(array(), $coll->deleteIndexes());
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testDistinctShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(array('ok' => 0)));

        $coll = $this->getTestCollection($database);
        $coll->distinct('foo');
    }

    public function testDrop()
    {
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('drop')
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);
        $result = $coll->drop();
        $this->assertEquals(true, $result);
    }

    public function testEnsureIndex()
    {
        $keys = array('x' => 1);
        $options = array('w' => 0);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('ensureIndex')
            ->with($keys, $options)
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($coll->ensureIndex($keys, $options));
    }

    public function testFind()
    {
        $query = array('x' => 1);
        $fields = array('x' => 1, 'y' => 1, '_id' => 0);

        $mongoCursor = $this->getMockMongoCursor();

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('find')
            ->with($query, $fields)
            ->will($this->returnValue($mongoCursor));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);
        $result = $coll->find($query, $fields);

        $this->assertInstanceOf('Doctrine\MongoDB\Cursor', $result);
        $this->assertSame($mongoCursor, $result->getMongoCursor());
    }

    public function testFindAndRemove()
    {
        $query = array('completed' => true);

        $command = array(
            'findandmodify' => self::collectionName,
            'query' => (object) $query,
            'remove' => true,
        );

        $document = array('_id' => 1, 'completed' => true);

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($command)
            ->will($this->returnValue(array('ok' => 1, 'value' => $document)));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());

        $this->assertEquals($document, $coll->findAndRemove($query));
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testFindAndRemoveShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(array('ok' => 0)));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());
        $coll->findAndRemove(array());
    }

    public function testFindAndUpdate()
    {
        $query = array('inprogress' => false);
        $newObj = array('$set' => array('inprogress' => true));
        $options = array('new' => true);

        $command = array(
            'findandmodify' => self::collectionName,
            'query' => (object) $query,
            'update' => (object) $newObj,
            'new' => true,
        );

        $document = array('_id' => 1, 'inprogress' => true);

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($command)
            ->will($this->returnValue(array('ok' => 1, 'value' => $document)));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());

        $this->assertEquals($document, $coll->findAndUpdate($query, $newObj, $options));
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testFindAndUpdateShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(array('ok' => 0)));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());
        $coll->findAndUpdate(array(), array());
    }

    public function testFindOne()
    {
        $query = array('x' => 1);
        $fields = array('x' => 1, 'y' => 1, '_id' => 0);
        $document = array('x' => 1, 'y' => 'foo');

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('findOne')
            ->with($query, $fields)
            ->will($this->returnValue($document));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals($document, $coll->findOne($query, $fields));
    }

    public function testGetDBRef()
    {
        $document = array('_id' => 1);
        $dbRef = array('$ref' => 'test', '$id' => 1);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('getDBRef')
            ->with($dbRef)
            ->will($this->returnValue($document));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals($document, $coll->getDBRef($dbRef));
    }

    public function testGetName()
    {
        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('getName')
            ->will($this->returnValue(self::collectionName));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(self::collectionName, $coll->getName());
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

        $collection = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

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

        $collection = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

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

        $collection = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(true, $collection->setSlaveOkay(true));
    }

    public function testSetReadPreference()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->at(0))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY)
            ->will($this->returnValue(true));

        $mongoCollection->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east')))
            ->will($this->returnValue(true));

        $collection = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($collection->setReadPreference(\MongoClient::RP_PRIMARY));
        $this->assertTrue($collection->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east'))));
    }

    public function testGroup()
    {
        $keys = array('category' => 1);
        $initial = array('items' => array());
        $reduce = 'reduce';
        $options = array('cond' => array('deleted' => false), 'finalize' => 'finalize');

        $grouped = array(
            array('category' => 'fruit', 'items' => array('apple', 'peach', 'banana')),
            array('category' => 'veggie', 'items' => array('corn', 'broccoli')),
        );

        $command = array('group' => array(
            'ns' => self::collectionName,
            'initial' => (object) $initial,
            '$reduce' => new \MongoCode('reduce'),
            'cond' => (object) $options['cond'],
            'key' => $keys,
            'finalize' => new \MongoCode('finalize'),
        ));

        $commandResult = array('ok' => 1, 'retval' => $grouped, 'count' => 5, 'keys' => 2);

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($command)
            ->will($this->returnValue($commandResult));

        $coll = $this->getTestCollection($database);
        $result = $coll->group($keys, $initial, $reduce, $options);

        $arrayIterator = new ArrayIterator($grouped);
        $arrayIterator->setCommandResult($commandResult);

        $this->assertEquals($arrayIterator, $result);
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testGroupShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(array('ok' => 0)));

        $coll = $this->getTestCollection($database);
        $coll->group(array(), array(), '');
    }

    public function testInsert()
    {
        $document = array('x' => 1);
        $options = array('w' => 0);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with($document, $options)
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($coll->insert($document, $options));
    }

    /**
     * @covers Doctrine\MongoDB\Collection::getIndexInfo
     * @dataProvider provideIsFieldIndex
     */
    public function testIsFieldIndexed($indexInfo, $field, $expectedResult)
    {
        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('getIndexInfo')
            ->will($this->returnValue($indexInfo));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals($expectedResult, $coll->isFieldIndexed($field));
    }

    public function provideIsFieldIndex()
    {
        $indexInfo = array(
            array(
                'name' => '_id_',
                'ns' => 'test.foo',
                'key' => array('_id' => 1),
            ),
            array(
                'name' => 'bar_1_bat_-1',
                'ns' => 'test.foo',
                'key' => array('bar' => 1, 'bat' => -1)
            ),
        );

        return array(
            array($indexInfo, '_id', true),
            array($indexInfo, 'bar', true),
            array($indexInfo, 'bat', true),
            array($indexInfo, 'baz', false),
        );
    }

    public function testMapReduceWithResultsInline()
    {
        $map = 'map';
        $reduce = 'reduce';
        $out = array('inline' => true);
        $query = array('deleted' => false);
        $options = array('finalize' => 'finalize');

        $reduced = array(
            array('category' => 'fruit', 'items' => array('apple', 'peach', 'banana')),
            array('category' => 'veggie', 'items' => array('corn', 'broccoli')),
        );

        $commandResult = array('ok' => 1, 'results' => $reduced);

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($this->logicalAnd(
                new ArrayHasKeyAndValue('mapreduce', self::collectionName),
                new ArrayHasKeyAndValue('map', new \MongoCode('map')),
                new ArrayHasKeyAndValue('reduce', new \MongoCode('reduce')),
                new ArrayHasKeyAndValue('out', $out),
                new ArrayHasKeyAndValue('query', (object) array('deleted' => false)),
                new ArrayHasKeyAndValue('finalize', new \MongoCode('finalize'))
            ))
            ->will($this->returnValue($commandResult));

        $coll = $this->getTestCollection($database);
        $result = $coll->mapReduce($map, $reduce, $out, $query, $options);

        $arrayIterator = new ArrayIterator($reduced);
        $arrayIterator->setCommandResult($commandResult);

        $this->assertEquals($arrayIterator, $result);
    }

    public function testMapReduceWithResultsInAnotherCollection()
    {
        $cursor = $this->getMockCursor();

        $outputCollection = $this->getMockCollection();
        $outputCollection->expects($this->once())
            ->method('find')
            ->will($this->returnValue($cursor));

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with(new ArrayHasKeyAndValue('out', 'outputCollection'))
            ->will($this->returnValue(array('ok' => 1, 'result' => 'outputCollection')));

        $database->expects($this->once())
            ->method('selectCollection')
            ->with('outputCollection')
            ->will($this->returnValue($outputCollection));

        $coll = $this->getTestCollection($database);
        $this->assertSame($cursor, $coll->mapReduce('', '', 'outputCollection'));
    }

    public function testMapReduceWithResultsInAnotherDatabase()
    {
        $cursor = $this->getMockCursor();

        $outputCollection = $this->getMockCollection();
        $outputCollection->expects($this->once())
            ->method('find')
            ->will($this->returnValue($cursor));

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with(new ArrayHasKeyAndValue('out', array('replace' => 'outputCollection', 'db' => 'outputDatabase')))
            ->will($this->returnValue(array('ok' => 1, 'result' => array('db' => 'outputDatabase', 'collection' => 'outputCollection'))));

        $connection = $this->getMockConnection();
        $connection->expects($this->once())
            ->method('selectCollection')
            ->with('outputDatabase', 'outputCollection')
            ->will($this->returnValue($outputCollection));

        $database->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $coll = $this->getTestCollection($database);
        $this->assertSame($cursor, $coll->mapReduce('', '', array('replace' => 'outputCollection', 'db' => 'outputDatabase')));
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testMapReduceShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(array('ok' => 0)));

        $coll = $this->getTestCollection($database);
        $coll->mapReduce('', '');
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testNearShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(array('ok' => 0)));

        $coll = $this->getTestCollection($database);
        $coll->near(array());
    }

    /**
     * @dataProvider providePoint
     */
    public function testNear($point, array $near, $spherical)
    {
        $results = array(
            array('dis' => 1, 'obj' => array('_id' => 1, 'loc' => array(1, 0))),
            array('dis' => 2, 'obj' => array('_id' => 2, 'loc' => array(2, 0))),
        );

        $command = array(
            'geoNear' => self::collectionName,
            'near' => $near,
            'spherical' => $spherical,
            'query' => new \stdClass(),
        );

        $commandResult = array('ok' => 1, 'results' => $results);

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($command)
            ->will($this->returnValue($commandResult));

        $coll = $this->getTestCollection($database);
        $result = $coll->near($point);

        $arrayIterator = new ArrayIterator($results);
        $arrayIterator->setCommandResult($commandResult);

        $this->assertEquals($arrayIterator, $result);
    }

    public function providePoint()
    {
        $coordinates = array(0, 0);
        $json = array('type' => 'Point', 'coordinates' => $coordinates);

        return array(
            'legacy array' => array($coordinates, $coordinates, false),
            'GeoJSON array' => array($json, $json, true),
            'GeoJSON object' => array($this->getMockPoint($json), $json, true),
        );
    }

    public function testRemove()
    {
        $criteria = array('x' => 1);
        $options = array('w' => 0);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('remove')
            ->with($criteria, $options)
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($coll->remove($criteria, $options));
    }

    public function testSave()
    {
        $document = array('x' => 1);
        $options = array('w' => 0);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('save')
            ->with($document, $options)
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($coll->save($document, $options));
    }

    public function testUpdate()
    {
        $criteria = array('x' => 1);
        $newObj = array('$set' => array('x' => 2));
        $options = array('w'=> 0);

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('update')
            ->with($criteria, $newObj, $options)
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($coll->update($criteria, $newObj, $options));
    }

    /**
     * @expectedException PHPUnit_Framework_Error_Deprecated
     */
    public function testUpdateShouldTriggerErrorForDeprecatedScalarQueryArgument()
    {
        $coll = $this->getTestCollection();
        $coll->update('id', array());
    }

    public function testValidate()
    {
        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('validate')
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($coll->validate());
    }

    public function test__toString()
    {
        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('__toString')
            ->will($this->returnValue(self::collectionName));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(self::collectionName, $coll->__toString());
    }

    public function testWriteConcernOptionIsConverted()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(array('x' => 1), array('w' => 1));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = array('x' => 1);
        $coll->insert($document, array('safe' => true));
    }

    public function testWriteConcernOptionIsNotConvertedForOlderDrivers()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '>=')) {
            $this->markTestSkipped('This test is not applicable to driver versions >= 1.3.0');
        }

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(array('x' => 1), array('safe' => true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = array('x' => 1);
        $coll->insert($document, array('safe' => true));
    }

    public function testSocketTimeoutOptionIsConverted()
    {
        if (version_compare(phpversion('mongo'), '1.5.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.5.0');
        }

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(array('x' => 1), array('socketTimeoutMS' => 1000));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = array('x' => 1);
        $coll->insert($document, array('timeout' => 1000));
    }

    public function testSocketTimeoutOptionIsNotConvertedForOlderDrivers()
    {
        if (version_compare(phpversion('mongo'), '1.5.0', '>=')) {
            $this->markTestSkipped('This test is not applicable to driver versions >= 1.5.0');
        }

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(array('x' => 1), array('timeout' => 1000));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = array('x' => 1);
        $coll->insert($document, array('timeout' => 1000));
    }

    public function testWriteTimeoutOptionIsConverted()
    {
        if (version_compare(phpversion('mongo'), '1.5.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.5.0');
        }

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(array('x' => 1), array('wTimeoutMS' => 1000));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = array('x' => 1);
        $coll->insert($document, array('wtimeout' => 1000));
    }

    public function testWriteTimeoutOptionIsNotConvertedForOlderDrivers()
    {
        if (version_compare(phpversion('mongo'), '1.5.0', '>=')) {
            $this->markTestSkipped('This test is not applicable to driver versions >= 1.5.0');
        }

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(array('x' => 1), array('wtimeout' => 1000));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = array('x' => 1);
        $coll->insert($document, array('wtimeout' => 1000));
    }

    public function testSplittingOfCommandAndClientOptions()
    {
        $expectedCommand = array(
            'distinct' => self::collectionName,
            'key' => 'foo',
            'query' => new \stdClass(),
            'maxTimeMS' => 1000,
        );

        $expectedClientOptions = array('socketTimeoutMS' => 2000);

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($expectedCommand, $expectedClientOptions)
            ->will($this->returnValue(array('ok' => 1)));

        $coll = $this->getTestCollection($database);
        $coll->distinct('foo', array(), array('maxTimeMS' => 1000, 'socketTimeoutMS' => 2000));
    }

    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockConnection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockCursor()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Cursor')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockDatabase()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Database')
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
        $mc = $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();

        $mc->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::collectionName));

        return $mc;
    }

    private function getMockMongoCursor()
    {
        return $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockPoint($json)
    {
        $point = $this->getMockBuilder('GeoJson\Geometry\Point')
            ->disableOriginalConstructor()
            ->getMock();

        $point->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue($json));

        return $point;
    }

    private function getTestCollection(Database $db = null, MongoCollection $mc = null, EventManager $em = null)
    {
        $db = $db ?: $this->getMockDatabase();
        $mc = $mc ?: $this->getMockMongoCollection();
        $em = $em ?: $this->getMockEventManager();

        return new Collection($db, $mc, $em);
    }
}
