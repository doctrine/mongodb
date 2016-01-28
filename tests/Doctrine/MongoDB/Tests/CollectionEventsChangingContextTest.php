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


    private function getMockCollection(EventManager $eventManager, array $methods)
    {
        $collection = $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->setConstructorArgs(array($this->database, $this->mongoCollection, $eventManager))
            ->setMethods(array_keys($methods))
            ->getMock();

        foreach ($methods as $method => $withValues) {
            $expectation = $collection->expects($this->once());
            $expectation->method($method);
            call_user_func_array(array($expectation, "with"), $withValues);
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

class PreAggregateListener {

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
