<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\ArrayIterator;
use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Database;
use MongoCollection;
use PHPUnit\Framework\Error\Deprecated;

class CollectionTest extends TestCase
{
    const collectionName = 'collection';

    public function testAggregateWithPipelineArgument()
    {
        $pipeline = [
            ['$match' => ['_id' => 'bar']],
            ['$project' => ['_id' => 1]],
        ];
        $aggregated = [['_id' => 'bar']];
        $commandResult = ['ok' => 1, 'result' => $aggregated];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with(['aggregate' => self::collectionName, 'pipeline' => $pipeline])
            ->will($this->returnValue($commandResult));

        $coll = $this->getTestCollection($database);
        $result = $coll->aggregate($pipeline);

        $arrayIterator = new ArrayIterator($aggregated);
        $arrayIterator->setCommandResult($commandResult);

        $this->assertEquals($arrayIterator, $result);
    }

    public function testAggregateWithOperatorArguments()
    {
        $firstOp = ['$match' => ['_id' => 'bar']];
        $secondOp = ['$project' => ['_id' => 1]];
        $aggregated = [['_id' => 'bar']];
        $commandResult = ['ok' => 1, 'result' => $aggregated];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with(['aggregate' => self::collectionName, 'pipeline' => [$firstOp, $secondOp]])
            ->will($this->returnValue($commandResult));

        $coll = $this->getTestCollection($database);
        $result = $coll->aggregate($firstOp, $secondOp);

        $arrayIterator = new ArrayIterator($aggregated);
        $arrayIterator->setCommandResult($commandResult);

        $this->assertEquals($arrayIterator, $result);
    }

    public function testAggregateShouldReturnCursorForPipelineEndingWithOut()
    {
        $pipeline = [
            ['$match' => ['x' => '1']],
            ['$out' => 'foo'],
        ];
        $commandResult = ['ok' => 1];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with(['aggregate' => self::collectionName, 'pipeline' => $pipeline])
            ->will($this->returnValue($commandResult));

        $collection = $this->getMockCollection();
        $database->expects($this->once())
            ->method('selectCollection')
            ->with('foo')
            ->will($this->returnValue($collection));

        $cursor = $this->getMockCursor();
        $collection->expects($this->once())
            ->method('find')
            ->will($this->returnValue($cursor));

        $coll = $this->getTestCollection($database);
        $result = $coll->aggregate($pipeline);

        $this->assertSame($cursor, $result);
    }

    public function testAggregateWithCursorOption()
    {
        $pipeline = [
            ['$match' => ['_id' => 'bar']],
            ['$project' => ['_id' => 1]],
        ];
        $options = ['cursor' => true];
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('aggregateCursor')
            ->with($pipeline, [])
            ->will($this->returnValue($mongoCommandCursor));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);
        $result = $coll->aggregate($pipeline, $options);

        $this->assertInstanceOf('Doctrine\MongoDB\CommandCursor', $result);
        $this->assertSame($mongoCommandCursor, $result->getMongoCommandCursor());
    }

    public function testAggregateWithCursorOptionAndBatchSize()
    {
        $pipeline = [
            ['$match' => ['_id' => 'bar']],
            ['$project' => ['_id' => 1]],
        ];
        $options = ['cursor' => ['batchSize' => 10]];
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('aggregateCursor')
            ->with($pipeline, $options)
            ->will($this->returnValue($mongoCommandCursor));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);
        $result = $coll->aggregate($pipeline, $options);

        $this->assertInstanceOf('Doctrine\MongoDB\CommandCursor', $result);
        $this->assertSame($mongoCommandCursor, $result->getMongoCommandCursor());
    }

    public function testAggregateWithCursorOptionAndTimeout()
    {
        if ( ! method_exists('MongoCommandCursor', 'timeout')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCommandCursor::timeout()');
        }

        $pipeline = [
            ['$match' => ['_id' => 'bar']],
            ['$project' => ['_id' => 1]],
        ];
        $options = ['cursor' => true, 'socketTimeoutMS' => 1000];
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('aggregateCursor')
            ->with($pipeline, [])
            ->will($this->returnValue($mongoCommandCursor));

        $mongoCommandCursor->expects($this->once())
            ->method('timeout')
            ->with(1000);

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);
        $result = $coll->aggregate($pipeline, $options);

        $this->assertInstanceOf('Doctrine\MongoDB\CommandCursor', $result);
        $this->assertSame($mongoCommandCursor, $result->getMongoCommandCursor());
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testAggregateShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(['ok' => 0]));

        $coll = $this->getTestCollection($database);
        $coll->aggregate([]);
    }

    public function testBatchInsert()
    {
        $docs = [['x' => 1, 'y' => 2]];
        $options = ['w' => 0];

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('batchInsert')
            ->with($docs, $options)
            ->will($this->returnValue(true));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($coll->batchInsert($docs, $options));
    }

    public function testCountWithOptionsArray()
    {
        $expectedCommand = [
            'count' => self::collectionName,
            'query' => (object) ['x' => 1],
            'limit' => 0,
            'skip' => 0,
            'maxTimeMS' => 5000,
        ];

        $expectedClientOptions = ['socketTimeoutMS' => 15000];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($expectedCommand, $expectedClientOptions)
            ->will($this->returnValue(['ok' => 1, 'n' => 3]));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());

        $this->assertEquals(3, $coll->count(['x' => 1], ['maxTimeMS' => 5000, 'socketTimeoutMS' => 15000]));
    }

    public function testCountWithParameters()
    {
        $expectedCommand = [
            'count' => self::collectionName,
            'query' => (object) ['x' => 1],
            'limit' => 1,
            'skip' => 1,
        ];

        $expectedClientOptions = [];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($expectedCommand, $expectedClientOptions)
            ->will($this->returnValue(['ok' => 1, 'n' => 1]));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());

        $this->assertEquals(1, $coll->count(['x' => 1], 1, 1));
    }

    public function testCountWithoutParameters()
    {
        $expectedCommand = [
            'count' => self::collectionName,
            'query' => (object) ['x' => 1],
            'limit' => 0,
            'skip' => 0,
        ];

        $expectedClientOptions = [];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($expectedCommand, $expectedClientOptions)
            ->will($this->returnValue(['ok' => 1, 'n' => 3]));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());

        $this->assertEquals(3, $coll->count(['x' => 1]));
    }

    public function testCreateDBRef()
    {
        $document = ['_id' => 1];
        $dbRef = ['$ref' => 'test', '$id' => 1];

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
            ->will($this->returnValue([]));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals([], $coll->deleteIndex('foo'));
    }

    public function testDeleteIndexes()
    {
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('deleteIndexes')
            ->will($this->returnValue([]));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals([], $coll->deleteIndexes());
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testDistinctShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(['ok' => 0]));

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
        $keys = ['x' => 1];
        $options = ['w' => 0];

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
        $query = ['x' => 1];
        $fields = ['x' => 1, 'y' => 1, '_id' => 0];

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
        $query = ['completed' => true];

        $command = [
            'findandmodify' => self::collectionName,
            'query' => (object) $query,
            'remove' => true,
        ];

        $document = ['_id' => 1, 'completed' => true];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($command)
            ->will($this->returnValue(['ok' => 1, 'value' => $document]));

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
            ->will($this->returnValue(['ok' => 0]));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());
        $coll->findAndRemove([]);
    }

    public function testFindAndUpdate()
    {
        $query = ['inprogress' => false];
        $newObj = ['$set' => ['inprogress' => true]];
        $options = ['new' => true];

        $command = [
            'findandmodify' => self::collectionName,
            'query' => (object) $query,
            'update' => (object) $newObj,
            'new' => true,
        ];

        $document = ['_id' => 1, 'inprogress' => true];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($command)
            ->will($this->returnValue(['ok' => 1, 'value' => $document]));

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
            ->will($this->returnValue(['ok' => 0]));

        $coll = $this->getTestCollection($database, $this->getMockMongoCollection());
        $coll->findAndUpdate([], []);
    }

    public function testFindOne()
    {
        $query = ['x' => 1];
        $fields = ['x' => 1, 'y' => 1, '_id' => 0];
        $document = ['x' => 1, 'y' => 'foo'];

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
        $document = ['_id' => 1];
        $dbRef = ['$ref' => 'test', '$id' => 1];

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

    public function testGetSetSlaveOkayReadPreferences()
    {
        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->never())->method('getSlaveOkay');
        $mongoCollection->expects($this->never())->method('setSlaveOkay');

        $mongoCollection->expects($this->exactly(2))
            ->method('getReadPreference')
            ->will($this->returnValue([
                'type' => \MongoClient::RP_PRIMARY,
            ]));

        $mongoCollection->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED);

        $collection = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(false, $collection->setSlaveOkay(true));
    }

    public function testSetSlaveOkayPreservesReadPreferenceTags()
    {
        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->exactly(2))
            ->method('getReadPreference')
            ->will($this->returnValue([
                'type' => \MongoClient::RP_PRIMARY_PREFERRED,
                'tagsets' => [['dc' => 'east']],
            ]));

        $mongoCollection->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, [['dc' => 'east']])
            ->will($this->returnValue(false));

        $collection = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertEquals(true, $collection->setSlaveOkay(true));
    }

    public function testSetReadPreference()
    {
        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->at(0))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY)
            ->will($this->returnValue(true));

        $mongoCollection->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, [['dc' => 'east']])
            ->will($this->returnValue(true));

        $collection = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $this->assertTrue($collection->setReadPreference(\MongoClient::RP_PRIMARY));
        $this->assertTrue($collection->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, [['dc' => 'east']]));
    }

    public function testGroup()
    {
        $keys = ['category' => 1];
        $initial = ['items' => []];
        $reduce = 'reduce';
        $options = ['cond' => ['deleted' => false], 'finalize' => 'finalize'];

        $grouped = [
            ['category' => 'fruit', 'items' => ['apple', 'peach', 'banana']],
            ['category' => 'veggie', 'items' => ['corn', 'broccoli']],
        ];

        $command = ['group' => [
            'ns' => self::collectionName,
            'initial' => (object) $initial,
            '$reduce' => new \MongoCode('reduce'),
            'cond' => (object) $options['cond'],
            'key' => $keys,
            'finalize' => new \MongoCode('finalize'),
        ]];

        $commandResult = ['ok' => 1, 'retval' => $grouped, 'count' => 5, 'keys' => 2];

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
            ->will($this->returnValue(['ok' => 0]));

        $coll = $this->getTestCollection($database);
        $coll->group([], [], '');
    }

    public function testInsert()
    {
        $document = ['x' => 1];
        $options = ['w' => 0];

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
        $indexInfo = [
            [
                'name' => '_id_',
                'ns' => 'test.foo',
                'key' => ['_id' => 1],
            ],
            [
                'name' => 'bar_1_bat_-1',
                'ns' => 'test.foo',
                'key' => ['bar' => 1, 'bat' => -1]
            ],
        ];

        return [
            [$indexInfo, '_id', true],
            [$indexInfo, 'bar', true],
            [$indexInfo, 'bat', true],
            [$indexInfo, 'baz', false],
        ];
    }

    public function testMapReduceWithResultsInline()
    {
        $map = 'map';
        $reduce = 'reduce';
        $out = ['inline' => true];
        $query = ['deleted' => false];
        $options = ['finalize' => 'finalize'];

        $reduced = [
            ['category' => 'fruit', 'items' => ['apple', 'peach', 'banana']],
            ['category' => 'veggie', 'items' => ['corn', 'broccoli']],
        ];

        $commandResult = ['ok' => 1, 'results' => $reduced];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($this->logicalAnd(
                $this->arraySubset(['mapreduce' => self::collectionName]),
                $this->arraySubset(['map' => new \MongoCode('map')]),
                $this->arraySubset(['reduce' => new \MongoCode('reduce')]),
                $this->arraySubset(['out' => $out]),
                $this->arraySubset(['query' => (object) ['deleted' => false]]),
                $this->arraySubset(['finalize' => new \MongoCode('finalize')])
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
            ->with($this->arraySubset(['out' => 'outputCollection']))
            ->will($this->returnValue(['ok' => 1, 'result' => 'outputCollection']));

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
            ->with($this->arraySubset(['out' => ['replace' => 'outputCollection', 'db' => 'outputDatabase']]))
            ->will($this->returnValue(['ok' => 1, 'result' => ['db' => 'outputDatabase', 'collection' => 'outputCollection']]));

        $connection = $this->getMockConnection();
        $connection->expects($this->once())
            ->method('selectCollection')
            ->with('outputDatabase', 'outputCollection')
            ->will($this->returnValue($outputCollection));

        $database->expects($this->once())
            ->method('getConnection')
            ->will($this->returnValue($connection));

        $coll = $this->getTestCollection($database);
        $this->assertSame($cursor, $coll->mapReduce('', '', ['replace' => 'outputCollection', 'db' => 'outputDatabase']));
    }

    /**
     * @expectedException \Doctrine\MongoDB\Exception\ResultException
     */
    public function testMapReduceShouldThrowExceptionOnError()
    {
        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->will($this->returnValue(['ok' => 0]));

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
            ->will($this->returnValue(['ok' => 0]));

        $coll = $this->getTestCollection($database);
        $coll->near([]);
    }

    /**
     * @dataProvider providePoint
     */
    public function testNear($point, array $near, $spherical)
    {
        $results = [
            ['dis' => 1, 'obj' => ['_id' => 1, 'loc' => [1, 0]]],
            ['dis' => 2, 'obj' => ['_id' => 2, 'loc' => [2, 0]]],
        ];

        $command = [
            'geoNear' => self::collectionName,
            'near' => $near,
            'spherical' => $spherical,
            'query' => new \stdClass(),
        ];

        $commandResult = ['ok' => 1, 'results' => $results];

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
        $coordinates = [0, 0];
        $json = ['type' => 'Point', 'coordinates' => $coordinates];

        return [
            'legacy array' => [$coordinates, $coordinates, false],
            'GeoJSON array' => [$json, $json, true],
            'GeoJSON object' => [$this->getMockPoint($json), $json, true],
        ];
    }

    public function testRemove()
    {
        $criteria = ['x' => 1];
        $options = ['w' => 0];

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
        $document = ['x' => 1];
        $options = ['w' => 0];

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
        $criteria = ['x' => 1];
        $newObj = ['$set' => ['x' => 2]];
        $options = ['w' => 0];

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
        if (class_exists(\PHPUnit_Framework_Error_Deprecated::class)) {
            $this->expectException(\PHPUnit_Framework_Error_Deprecated::class);
        } else {
            $this->expectException(Deprecated::class);
        }

        $coll = $this->getTestCollection();
        $coll->update('id', []);
    }

    public function testUpdateShouldRenameMultiToMultiple()
    {
        $criteria = ['x' => 1];
        $newObj = ['$set' => ['x' => 2]];

        $mongoCollection = $this->getMockMongoCollection();

        $mongoCollection->expects($this->once())
            ->method('update')
            ->with($criteria, $newObj, ['multiple' => true]);

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $coll->update($criteria, $newObj, ['multi' => true]);
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
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(['x' => 1], ['w' => 1]);

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = ['x' => 1];
        $coll->insert($document, ['safe' => true]);
    }

    public function testSocketTimeoutOptionIsConverted()
    {
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(['x' => 1], ['socketTimeoutMS' => 1000]);

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = ['x' => 1];
        $coll->insert($document, ['timeout' => 1000]);
    }

    public function testWriteTimeoutOptionIsConverted()
    {
        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('insert')
            ->with(['x' => 1], ['wTimeoutMS' => 1000]);

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);

        $document = ['x' => 1];
        $coll->insert($document, ['wtimeout' => 1000]);
    }

    public function testSplittingOfCommandAndClientOptions()
    {
        $expectedCommand = [
            'distinct' => self::collectionName,
            'key' => 'foo',
            'query' => new \stdClass(),
            'maxTimeMS' => 1000,
        ];

        $expectedClientOptions = ['socketTimeoutMS' => 2000];

        $database = $this->getMockDatabase();
        $database->expects($this->once())
            ->method('command')
            ->with($expectedCommand, $expectedClientOptions)
            ->will($this->returnValue(['ok' => 1]));

        $coll = $this->getTestCollection($database);
        $coll->distinct('foo', [], ['maxTimeMS' => 1000, 'socketTimeoutMS' => 2000]);
    }

    public function testParallelCollectionScan()
    {
        $numCursors = 3;
        $mongoCommandCursors = [
            $this->getMockMongoCommandCursor(),
            $this->getMockMongoCommandCursor(),
            $this->getMockMongoCommandCursor(),
        ];

        $mongoCollection = $this->getMockMongoCollection();
        $mongoCollection->expects($this->once())
            ->method('parallelCollectionScan')
            ->with($numCursors)
            ->will($this->returnValue($mongoCommandCursors));

        $coll = $this->getTestCollection($this->getMockDatabase(), $mongoCollection);
        $result = $coll->parallelCollectionScan($numCursors);

        $this->assertCount(3, $result);

        foreach ($result as $index => $cursor) {
            $this->assertInstanceOf('Doctrine\MongoDB\CommandCursor', $cursor);
            $this->assertSame($mongoCommandCursors[$index], $cursor->getMongoCommandCursor());
        }
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

    private function getMockMongoCommandCursor()
    {
        return $this->getMockBuilder('MongoCommandCursor')
            ->disableOriginalConstructor()
            ->getMock();
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
