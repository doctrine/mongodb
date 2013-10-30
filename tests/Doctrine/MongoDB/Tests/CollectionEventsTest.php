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

class CollectionEventsTest extends \PHPUnit_Framework_TestCase
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
        $pipeline = array(array('$match' => array('_id' => '1')));
        $result = array(array('_id' => '1'));

        $collection = $this->getMockCollection(array('doAggregate' => $result));

        $this->expectEvents(array(
            array(Events::preAggregate, new AggregateEventArgs($collection, $pipeline)),
            array(Events::postAggregate, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->aggregate($pipeline));
    }

    public function testBatchInsert()
    {
        $documents = array(array('x' => 1));
        $options = array('continueOnError' => true);
        $result = array(array('_id' => new \MongoId(), 'x' => 1));

        $collection = $this->getMockCollection(array('doBatchInsert' => $result));

        $this->expectEvents(array(
            array(Events::preBatchInsert, new EventArgs($collection, $documents, $options)),
            array(Events::postBatchInsert, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->batchInsert($documents, $options));
    }

    public function testDistinct()
    {
        $field = 'x';
        $query = array('y' => 1);
        $result = array(array('x' => 1, 'y' => 1), array('x' => 2, 'y' => 1));

        $collection = $this->getMockCollection(array('doDistinct' => $result));

        $this->expectEvents(array(
            array(Events::preDistinct, new DistinctEventArgs($collection, $field, $query)),
            array(Events::postDistinct, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->distinct($field, $query));
    }

    public function testDrop()
    {
        $result = array('ok' => 1);

        $collection = $this->getMockCollection(array('doDrop' => $result));

        $this->expectEvents(array(
            array(Events::preDropCollection, new EventArgs($collection)),
            array(Events::postDropCollection, new EventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->drop());
    }

    public function testFind()
    {
        $query = array('x' => 1);
        $fields = array('_id' => 0);
        $result = array(array('x' => 1, 'y' => 2));

        $collection = $this->getMockCollection(array('doFind' => $result));

        $this->expectEvents(array(
            array(Events::preFind, new FindEventArgs($collection, $query, $fields)),
            array(Events::postFind, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->find($query, $fields));
    }

    public function testFindOne()
    {
        $query = array('x' => 1);
        $fields = array('_id' => 0);
        $result = array('x' => 1, 'y' => 2);

        $collection = $this->getMockCollection(array('doFindOne' => $result));

        $this->expectEvents(array(
            array(Events::preFindOne, new FindEventArgs($collection, $query, $fields)),
            array(Events::postFindOne, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->findOne($query, $fields));
    }

    public function testFindAndRemove()
    {
        $query = array('x' => 1);
        $options = array('sort' => array('y' => -1));
        $result = array('x' => 1);

        $collection = $this->getMockCollection(array('doFindAndRemove' => $result));

        $this->expectEvents(array(
            array(Events::preFindAndRemove, new EventArgs($collection, $query, $options)),
            array(Events::postFindAndRemove, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->findAndRemove($query, $options));
    }

    public function testFindAndUpdate()
    {
        $query = array('x' => 1);
        $newObj = array('$set' => array('x' => 2));
        $options = array('sort' => array('y' => -1));
        $result = array('x' => 2);

        $collection = $this->getMockCollection(array('doFindAndUpdate' => $result));

        $this->expectEvents(array(
            array(Events::preFindAndUpdate, new UpdateEventArgs($collection, $query, $newObj, $options)),
            array(Events::postFindAndUpdate, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->findAndUpdate($query, $newObj, $options));
    }

    public function testGetDBRef()
    {
        $reference = array('$ref' => 'collection', '$id' => 1);
        $result = array('_id' => 1);

        $collection = $this->getMockCollection(array('doGetDBRef' => $result));

        $this->expectEvents(array(
            array(Events::preGetDBRef, new EventArgs($collection, $reference)),
            array(Events::postGetDBRef, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->getDBRef($reference));
    }

    public function testGroup()
    {
        $keys = 'x';
        $initial = array('count' => 0);
        $reduce = new \MongoCode('');
        $options = array('finalize' => new \MongoCode(''));
        $result = array(array('count' => '1'));

        $collection = $this->getMockCollection(array('doGroup' => $result));

        $this->expectEvents(array(
            array(Events::preGroup, new GroupEventArgs($collection, $keys, $initial, $reduce, $options)),
            array(Events::postGroup, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->group($keys, $initial, $reduce, $options));
    }

    public function testInsert()
    {
        $document = array('x' => 1);
        $options = array('w' => 1);
        $result = array('_id' => new \MongoId(), 'x' => 1);

        $collection = $this->getMockCollection(array('doInsert' => $result));

        $this->expectEvents(array(
            array(Events::preInsert, new EventArgs($collection, $document, $options)),
            array(Events::postInsert, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->insert($document, $options));
    }

    public function testMapReduce()
    {
        $map = new \MongoCode('');
        $reduce = new \MongoCode('');
        $out = array('inline' => true);
        $query = array('x' => 1);
        $options = array('finalize' => new \MongoCode(''));
        $result = array(array('count' => '1'));

        $collection = $this->getMockCollection(array('doMapReduce' => $result));

        $this->expectEvents(array(
            array(Events::preMapReduce, new MapReduceEventArgs($collection, $map, $reduce, $out, $query, $options)),
            array(Events::postMapReduce, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->mapReduce($map, $reduce, $out, $query, $options));
    }

    public function testNear()
    {
        $query = array('x' => 1);
        $near = array(10, 20);
        $options = array('limit' => 5);
        $result = array(array('x' => 1, 'loc' => array(11, 19)));

        $collection = $this->getMockCollection(array('doNear' => $result));

        $this->expectEvents(array(
            array(Events::preNear, new NearEventArgs($collection, $query, $near, $options)),
            array(Events::postNear, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->near($near, $query, $options));
    }

    public function testRemove()
    {
        $query = array('x' => 1);
        $options = array('justOne' => true);
        $result = array('ok' => 1);

        $collection = $this->getMockCollection(array('doRemove' => $result));

        $this->expectEvents(array(
            array(Events::preRemove, new EventArgs($collection, $query, $options)),
            array(Events::postRemove, new EventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->remove($query, $options));
    }

    public function testSave()
    {
        $document = array('x' => 1);
        $options = array('w' => 1);
        $result = array('_id' => new \MongoId(), 'x' => 1);

        $collection = $this->getMockCollection(array('doSave' => $result));

        $this->expectEvents(array(
            array(Events::preSave, new EventArgs($collection, $document, $options)),
            array(Events::postSave, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->save($document, $options));
    }

    public function testUpdate()
    {
        $query = array('x' => 1);
        $newObj = array('$set' => array('x' => 2));
        $options = array('upsert' => true);
        $result = array(array('ok' => 1));

        $collection = $this->getMockCollection(array('doUpdate' => $result));

        $this->expectEvents(array(
            array(Events::preUpdate, new UpdateEventArgs($collection, $query, $newObj, $options)),
            array(Events::postUpdate, new EventArgs($collection, $result)),
        ));

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
            ->setConstructorArgs(array($this->database, $this->mongoCollection, $this->eventManager))
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
