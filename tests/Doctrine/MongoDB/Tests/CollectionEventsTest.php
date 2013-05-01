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
    const collectionName = 'collection';

    public function testAggregate()
    {
        $pipeline = array(array('$match' => array('_id' => '1')));
        $result = array(array('_id' => '1'));

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doAggregate' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doBatchInsert' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preBatchInsert, new EventArgs($collection, $documents, $options)),
            array(Events::postBatchInsert, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->batchInsert($documents, $options));
    }

    public function testCreateDBRef()
    {
        $document = array('_id' => 1);
        $result = array('$ref' => self::collectionName, '$id' => 1);

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doCreateDBRef' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preCreateDBRef, new EventArgs($collection, $document)),
            array(Events::postCreateDBRef, new EventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->createDBRef($document));
    }

    public function testDistinct()
    {
        $field = 'x';
        $query = array('y' => 1);
        $result = array(array('x' => 1, 'y' => 1), array('x' => 2, 'y' => 1));

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doDistinct' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preDistinct, new DistinctEventArgs($collection, $field, $query)),
            array(Events::postDistinct, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->distinct($field, $query));
    }

    public function testDrop()
    {
        $result = array('ok' => 1);

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doDrop' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doFind' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doFindOne' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doFindAndRemove' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doFindAndUpdate' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preFindAndUpdate, new UpdateEventArgs($collection, $query, $newObj, $options)),
            array(Events::postFindAndUpdate, new MutableEventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->findAndUpdate($query, $newObj, $options));
    }

    public function testGetDBRef()
    {
        $reference = array('$ref' => self::collectionName, '$id' => 1);
        $result = array('_id' => 1);

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doGetDBRef' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doGroup' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doInsert' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doMapReduce' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doNear' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doRemove' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doSave' => $result));

        $this->expectEvents($eventManager, array(
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

        $eventManager = $this->getMockEventManager();
        $collection = $this->getMockCollection($eventManager, array('doUpdate' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preUpdate, new UpdateEventArgs($collection, $query, $newObj, $options)),
            array(Events::postUpdate, new EventArgs($collection, $result)),
        ));

        $this->assertSame($result, $collection->update($query, $newObj, $options));
    }

    /**
     * Expect events to be dispatched by the event manager in the given order.
     *
     * @param EventManager $em     EventManager mock
     * @param array        $events Tuple of event name and dispatch argument
     */
    private function expectEvents(EventManager $em, array $events)
    {
        /* Each event should be a tuple consisting of the event name and the
         * dispatched argument (e.g. EventArgs).
         *
         * For each event, expect a call to hasListeners() immediately followed
         * by a call to dispatchEvent(). The dispatch argument is passed as-is
         * to with(), so constraints may be used (e.g. callback).
         */
        foreach ($events as $i => $event) {
            $em->expects($this->at($i * 2))
                ->method('hasListeners')
                ->with($event[0])
                ->will($this->returnValue(true));

            $em->expects($this->at($i * 2 + 1))
                ->method('dispatchEvent')
                ->with($event[0], $event[1]);
        }
    }

    private function getMockCollection(EventManager $em, array $methods)
    {
        $c = $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $db = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->disableOriginalConstructor()
            ->getMock();

        $collection = $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->setConstructorArgs(array($c, self::collectionName, $db, $em, '$'))
            ->setMethods(array_keys($methods))
            ->getMock();

        foreach ($methods as $method => $returnValue) {
            $collection->expects($this->once())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $collection;
    }

    private function getMockEventManager()
    {
        return $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
