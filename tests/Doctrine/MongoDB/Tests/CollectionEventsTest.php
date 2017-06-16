<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Events;
use Doctrine\MongoDB\Event\AggregateEventArgs;
use Doctrine\MongoDB\Event\DistinctEventArgs;
use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Event\FindEventArgs;
use Doctrine\MongoDB\Event\GroupEventArgs;
use Doctrine\MongoDB\Event\MapReduceEventArgs;
use Doctrine\MongoDB\Event\MutableEventArgs;
use Doctrine\MongoDB\Event\NearEventArgs;
use Doctrine\MongoDB\Event\UpdateEventArgs;

class CollectionEventsTest extends TestCase
{
    private $database;
    private $eventManager;
    private $mongoCollection;

    public function setUp()
    {
        $this->database = $this->getMockDatabase();
        $this->eventManager = $this->getMockEventManager();
        $this->mongoCollection = $this->getMockMongoCollection();
    }

    public function testAggregate()
    {
        $pipeline = [['$match' => ['_id' => '1']]];
        $result = [['_id' => '1']];

        $collection = $this->getMockCollection(['doAggregate' => $result]);

        $this->expectEvents([
            [Events::preAggregate, new AggregateEventArgs($collection, $pipeline)],
            [Events::postAggregate, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->aggregate($pipeline));
    }

    public function testBatchInsert()
    {
        $documents = [['x' => 1]];
        $options = ['continueOnError' => true];
        $result = [['_id' => new \MongoId(), 'x' => 1]];

        $collection = $this->getMockCollection(['doBatchInsert' => $result]);

        $this->expectEvents([
            [Events::preBatchInsert, new EventArgs($collection, $documents, $options)],
            [Events::postBatchInsert, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->batchInsert($documents, $options));
    }

    public function testDistinct()
    {
        $field = 'x';
        $query = ['y' => 1];
        $result = [['x' => 1, 'y' => 1], ['x' => 2, 'y' => 1]];

        $collection = $this->getMockCollection(['doDistinct' => $result]);

        $this->expectEvents([
            [Events::preDistinct, new DistinctEventArgs($collection, $field, $query)],
            [Events::postDistinct, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->distinct($field, $query));
    }

    public function testDrop()
    {
        $result = ['ok' => 1];

        $collection = $this->getMockCollection(['doDrop' => $result]);

        $this->expectEvents([
            [Events::preDropCollection, new EventArgs($collection)],
            [Events::postDropCollection, new EventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->drop());
    }

    public function testFind()
    {
        $query = ['x' => 1];
        $fields = ['_id' => 0];
        $result = [['x' => 1, 'y' => 2]];

        $collection = $this->getMockCollection(['doFind' => $result]);

        $this->expectEvents([
            [Events::preFind, new FindEventArgs($collection, $query, $fields)],
            [Events::postFind, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->find($query, $fields));
    }

    public function testFindOne()
    {
        $query = ['x' => 1];
        $fields = ['_id' => 0];
        $result = ['x' => 1, 'y' => 2];

        $collection = $this->getMockCollection(['doFindOne' => $result]);

        $this->expectEvents([
            [Events::preFindOne, new FindEventArgs($collection, $query, $fields)],
            [Events::postFindOne, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->findOne($query, $fields));
    }

    public function testFindAndRemove()
    {
        $query = ['x' => 1];
        $options = ['sort' => ['y' => -1]];
        $result = ['x' => 1];

        $collection = $this->getMockCollection(['doFindAndRemove' => $result]);

        $this->expectEvents([
            [Events::preFindAndRemove, new MutableEventArgs($collection, $query, $options)],
            [Events::postFindAndRemove, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->findAndRemove($query, $options));
    }

    public function testFindAndUpdate()
    {
        $query = ['x' => 1];
        $newObj = ['$set' => ['x' => 2]];
        $options = ['sort' => ['y' => -1]];
        $result = ['x' => 2];

        $collection = $this->getMockCollection(['doFindAndUpdate' => $result]);

        $this->expectEvents([
            [Events::preFindAndUpdate, new UpdateEventArgs($collection, $query, $newObj, $options)],
            [Events::postFindAndUpdate, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->findAndUpdate($query, $newObj, $options));
    }

    public function testGetDBRef()
    {
        $reference = ['$ref' => 'collection', '$id' => 1];
        $result = ['_id' => 1];

        $collection = $this->getMockCollection(['doGetDBRef' => $result]);

        $this->expectEvents([
            [Events::preGetDBRef, new EventArgs($collection, $reference)],
            [Events::postGetDBRef, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->getDBRef($reference));
    }

    public function testGroup()
    {
        $keys = 'x';
        $initial = ['count' => 0];
        $reduce = new \MongoCode('');
        $options = ['finalize' => new \MongoCode('')];
        $result = [['count' => '1']];

        $collection = $this->getMockCollection(['doGroup' => $result]);

        $this->expectEvents([
            [Events::preGroup, new GroupEventArgs($collection, $keys, $initial, $reduce, $options)],
            [Events::postGroup, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->group($keys, $initial, $reduce, $options));
    }

    public function testInsert()
    {
        $document = ['x' => 1];
        $options = ['w' => 1];
        $result = ['_id' => new \MongoId(), 'x' => 1];

        $collection = $this->getMockCollection(['doInsert' => $result]);

        $this->expectEvents([
            [Events::preInsert, new EventArgs($collection, $document, $options)],
            [Events::postInsert, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->insert($document, $options));
    }

    public function testMapReduce()
    {
        $map = new \MongoCode('');
        $reduce = new \MongoCode('');
        $out = ['inline' => true];
        $query = ['x' => 1];
        $options = ['finalize' => new \MongoCode('')];
        $result = [['count' => '1']];

        $collection = $this->getMockCollection(['doMapReduce' => $result]);

        $this->expectEvents([
            [Events::preMapReduce, new MapReduceEventArgs($collection, $map, $reduce, $out, $query, $options)],
            [Events::postMapReduce, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->mapReduce($map, $reduce, $out, $query, $options));
    }

    public function testNear()
    {
        $query = ['x' => 1];
        $near = [10, 20];
        $options = ['limit' => 5];
        $result = [['x' => 1, 'loc' => [11, 19]]];

        $collection = $this->getMockCollection(['doNear' => $result]);

        $this->expectEvents([
            [Events::preNear, new NearEventArgs($collection, $query, $near, $options)],
            [Events::postNear, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->near($near, $query, $options));
    }

    public function testRemove()
    {
        $query = ['x' => 1];
        $options = ['justOne' => true];
        $result = ['ok' => 1];

        $collection = $this->getMockCollection(['doRemove' => $result]);

        $this->expectEvents([
            [Events::preRemove, new MutableEventArgs($collection, $query, $options)],
            [Events::postRemove, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->remove($query, $options));
    }

    public function testSave()
    {
        $document = ['x' => 1];
        $options = ['w' => 1];
        $result = ['_id' => new \MongoId(), 'x' => 1];

        $collection = $this->getMockCollection(['doSave' => $result]);

        $this->expectEvents([
            [Events::preSave, new EventArgs($collection, $document, $options)],
            [Events::postSave, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->save($document, $options));
    }

    public function testUpdate()
    {
        $query = ['x' => 1];
        $newObj = ['$set' => ['x' => 2]];
        $options = ['upsert' => true];
        $result = [['ok' => 1]];

        $collection = $this->getMockCollection(['doUpdate' => $result]);

        $this->expectEvents([
            [Events::preUpdate, new UpdateEventArgs($collection, $query, $newObj, $options)],
            [Events::postUpdate, new MutableEventArgs($collection, $result)],
        ]);

        $this->assertSame($result, $collection->update($query, $newObj, $options));
    }

    /**
     * Expect events to be dispatched by the event manager in the given order.
     *
     * @param array $events Tuple of event name and dispatch argument
     */
    private function expectEvents(array $events)
    {
        /* Each event should be a tuple consisting of the event name and the
         * dispatched argument (e.g. EventArgs).
         *
         * For each event, expect a call to hasListeners() immediately followed
         * by a call to dispatchEvent(). The dispatch argument is passed as-is
         * to with(), so constraints may be used (e.g. callback).
         */
        foreach ($events as $i => $event) {
            $this->eventManager->expects($this->at($i * 2))
                ->method('hasListeners')
                ->with($event[0])
                ->will($this->returnValue(true));

            $this->eventManager->expects($this->at($i * 2 + 1))
                ->method('dispatchEvent')
                ->with($event[0], $event[1]);
        }
    }

    private function getMockCollection(array $methods)
    {
        $collection = $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->setConstructorArgs([$this->database, $this->mongoCollection, $this->eventManager])
            ->setMethods(array_keys($methods))
            ->getMock();

        foreach ($methods as $method => $returnValue) {
            $collection->expects($this->once())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $collection;
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
        return $this->getMockBuilder('MongoCollection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
