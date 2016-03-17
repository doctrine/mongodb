<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Events;
use Doctrine\MongoDB\Event\AggregateEventArgs;
use Doctrine\MongoDB\Event\FindEventArgs;
use Doctrine\MongoDB\Event\GroupEventArgs;
use Doctrine\MongoDB\Event\MapReduceEventArgs;
use Doctrine\MongoDB\Event\MutableEventArgs;
use Doctrine\MongoDB\Event\NearEventArgs;
use Doctrine\MongoDB\Event\UpdateEventArgs;

class CollectionEventsChangingContextTest extends \PHPUnit_Framework_TestCase
{
    private $database;
    private $mongoCollection;

    public function setUp()
    {
        $this->database = $this->getMockDatabase();
        $this->mongoCollection = $this->getMockMongoCollection();
    }

    public function testAggregate()
    {
        $pipeline = array(array('$match' => array('_id' => '1')));

        $modifiedPipeline = array(array('$match' => array('_id' => '2')));
        $modifiedOptions = array('foo');

        // This listener will modify the pipeline and the options.
        $preAggregateListener = new PreAggregateListener($modifiedPipeline, $modifiedOptions);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preAggregate), $preAggregateListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doAggregate' => [$modifiedPipeline, $modifiedOptions])
        );

        $collection->aggregate($pipeline);
    }

    public function testFind()
    {
        $query = array('a');
        $fields = array('b');

        $modifiedQuery = array('c');
        $modifiedFields = array('d');

        // This listener will modify the data and options.
        $preFindEventListener = new PreFindListener($modifiedQuery, $modifiedFields);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preFind), $preFindEventListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doFind' => [$modifiedQuery, $modifiedFields])
        );

        $collection->find($query, $fields);
    }

    public function testFindAndRemove()
    {
        $query = array('a');
        $options = array('b');

        $modifiedQuery = array('c');
        $modifiedOptions = array('d');

        // This listener will modify the data and options.
        $preFindAndRemoveEventListener = new PreFindAndRemoveListener($modifiedQuery, $modifiedOptions);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preFindAndRemove), $preFindAndRemoveEventListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doFindAndRemove' => [$modifiedQuery, $modifiedOptions])
        );

        $collection->findAndRemove($query, $options);
    }

    public function testFindAndUpdate()
    {
        $query = array('a');
        $newObj = array('b');
        $options = array('c');

        $modifiedQuery = array('d');
        $modifiedNewObj = array('e');
        $modifiedOptions = array('f');

        // This listener will modify the data and options.
        $preFindAndUpdateEventListener = new PreFindAndUpdateListener($modifiedQuery, $modifiedNewObj, $modifiedOptions);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preFindAndUpdate), $preFindAndUpdateEventListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doFindAndUpdate' => [$modifiedQuery, $modifiedNewObj, $modifiedOptions])
        );

        $collection->findAndUpdate($query, $newObj, $options);
    }

    public function testFindOne()
    {
        $query = array('a');
        $fields = array('b');

        $modifiedQuery = array('c');
        $modifiedFields = array('d');

        // This listener will modify the pipeline and the options.
        $preFindOneListener = new PreFindOneListener($modifiedQuery, $modifiedFields);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preFindOne), $preFindOneListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doFindOne' => [$modifiedQuery, $modifiedFields])
        );

        $collection->findOne($query, $fields);
    }

    public function testGroup()
    {
        $keys = array('a');
        $initial = array('b');
        $reduce = array('c');
        $options = array('d');

        $modifiedKeys = array('e');
        $modifiedInitial = array('f');
        $modifiedReduce = array('g');
        $modifiedOptions = array('h');

        // This listener will modify the pipeline and the options.
        $preGroupListener = new PreGroupListener($modifiedKeys, $modifiedInitial, $modifiedReduce, $modifiedOptions);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preGroup), $preGroupListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doGroup' => [$modifiedKeys, $modifiedInitial, $modifiedReduce, $modifiedOptions])
        );

        $collection->group($keys, $initial, $reduce, $options);
    }

    public function testMapReduce()
    {
        $map = array('a');
        $reduce = array('b');
        $out = array('c');
        $query = array('d');
        $options = array('e');

        $modifiedMap = array('f');
        $modifiedReduce = array('g');
        $modifiedOut = array('h');
        $modifiedQuery = array('i');
        $modifiedOptions = array('j');

        // This listener will modify the data and options.
        $mapReduceListener = new PreMapReduceListener($modifiedMap, $modifiedReduce, $modifiedOut, $modifiedQuery, $modifiedOptions);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preMapReduce), $mapReduceListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doMapReduce' => [$modifiedMap, $modifiedReduce, $modifiedOut, $modifiedQuery, $modifiedOptions])
        );

        $collection->mapReduce($map, $reduce, $out, $query, $options);
    }

    public function testNear()
    {
        $near = 'a';
        $query = array('b');
        $options = array('c');

        $modifiedNear = 'd';
        $modifiedQuery = array('e');
        $modifiedOptions = array('f');

        // This listener will modify the data and options.
        $preNearListener = new PreNearListener($modifiedNear, $modifiedQuery, $modifiedOptions);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preNear), $preNearListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doNear' => [$modifiedNear, $modifiedQuery, $modifiedOptions])
        );

        $collection->near($near, $query, $options);
    }

    public function testRemove()
    {
        $query = array('b');
        $options = array('c');

        $modifiedQuery = array('e');
        $modifiedOptions = array('f');

        // This listener will modify the data and options.
        $preRemoveListener = new PreRemoveListener($modifiedQuery, $modifiedOptions);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preRemove), $preRemoveListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doRemove' => [$modifiedQuery, $modifiedOptions])
        );

        $collection->remove($query, $options);
    }

    public function testUpdate()
    {
        $query = array('a');
        $newObj = array('b');
        $options = array('c');

        $modifiedQuery = array('d');
        $modifiedNewObj = array('e');
        $modifiedOptions = array('f');

        // This listener will modify the data and options.
        $preUpdateListener = new PreUpdateListener($modifiedQuery, $modifiedNewObj, $modifiedOptions);

        $eventManager = new EventManager();
        $eventManager->addEventListener(array(Events::preUpdate), $preUpdateListener);

        // Ensure that the modified pipeline and options are sent to the doAggregate call.
        $collection = $this->getMockCollection(
            $eventManager,
            array('doUpdate' => [$modifiedQuery, $modifiedNewObj, $modifiedOptions])
        );

        $collection->update($query, $newObj, $options);
    }

    private function getMockCollection(EventManager $eventManager, array $methods)
    {
        $collection = $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->setConstructorArgs(array($this->database, $this->mongoCollection, $eventManager))
            ->setMethods(array_keys($methods))
            ->getMock();

        foreach ($methods as $method => $withValues) {
            $expectation = $collection->expects($this->once());
            $expectation->method($method);
            call_user_func_array(array($expectation, 'with'), $withValues);
        }

        return $collection;
    }

    private function getMockDatabase()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Database')
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

class PreAggregateListener
{
    public function __construct(array $pipeline, array $options)
    {
        $this->pipeline = $pipeline;
        $this->options = $options;
    }

    public function collectionPreAggregate(AggregateEventArgs $args)
    {
        $args->setOptions($this->options);
        $args->setPipeline($this->pipeline);
    }
}

class PreFindListener
{
    public function __construct(array $query, array $fields)
    {
        $this->query = $query;
        $this->fields = $fields;
    }

    public function collectionPreFind(FindEventArgs $args)
    {
        $args->setFields($this->fields);
        $args->setQuery($this->query);
    }
}

class PreFindAndRemoveListener
{
    public function __construct(array $query, array $options)
    {
        $this->query = $query;
        $this->options = $options;
    }

    public function collectionPreFindAndRemove(MutableEventArgs $args)
    {
        $args->setData($this->query);
        $args->setOptions($this->options);
    }
}

class PreFindAndUpdateListener
{
    public function __construct(array $query, array $newObj, array $options)
    {
        $this->query = $query;
        $this->newObj = $newObj;
        $this->options = $options;
    }

    public function collectionPreFindAndUpdate(UpdateEventArgs $args)
    {
        $args->setQuery($this->query);
        $args->setNewObj($this->newObj);
        $args->setOptions($this->options);
    }
}

class PreUpdateListener
{
    public function __construct(array $query, array $newObj, array $options)
    {
        $this->query = $query;
        $this->newObj = $newObj;
        $this->options = $options;
    }

    public function collectionPreUpdate(UpdateEventArgs $args)
    {
        $args->setQuery($this->query);
        $args->setNewObj($this->newObj);
        $args->setOptions($this->options);
    }
}

class PreFindOneListener
{
    public function __construct(array $query, array $fields)
    {
        $this->query = $query;
        $this->fields = $fields;
    }

    public function collectionPreFindOne(FindEventArgs $args)
    {
        $args->setFields($this->fields);
        $args->setQuery($this->query);
    }
}

class PreGroupListener
{
    public function __construct(array $keys, array $initial, array $reduce, array $options)
    {
        $this->keys = $keys;
        $this->initial = $initial;
        $this->reduce = $reduce;
        $this->options = $options;
    }

    public function collectionPreGroup(GroupEventArgs $args)
    {
        $args->setKeys($this->keys);
        $args->setInitial($this->initial);
        $args->setReduce($this->reduce);
        $args->setOptions($this->options);
    }
}

class PreMapReduceListener
{
    public function __construct($map, $reduce, array $out, array $query, array $options)
    {
        $this->map = $map;
        $this->reduce = $reduce;
        $this->out = $out;
        $this->query = $query;
        $this->options = $options;
    }

    public function preMapReduce(MapReduceEventArgs $args)
    {
        $args->setMap($this->map);
        $args->setReduce($this->reduce);
        $args->setOut($this->out);
        $args->setQuery($this->query);
        $args->setOptions($this->options);
    }
}

class PreNearListener
{
    public function __construct($near, array $query, array $options)
    {
        $this->query = $query;
        $this->near = $near;
        $this->options = $options;
    }

    public function collectionPreNear(NearEventArgs $args)
    {
        $args->setQuery($this->query);
        $args->setNear($this->near);
        $args->setOptions($this->options);
    }
}

class PreRemoveListener
{
    public function __construct(array $query, array $options)
    {
        $this->query = $query;
        $this->options = $options;
    }

    public function collectionPreRemove(MutableEventArgs $args)
    {
        $args->setData($this->query);
        $args->setOptions($this->options);
    }
}
