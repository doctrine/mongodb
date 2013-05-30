<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\AggregateEventArgs;

class AggregateEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testAggregateEventArgs()
    {
        $invoker = new \stdClass();
        $pipeline = array(array('$match' => array('_id' => 1)));

        $aggregateEventArgs = new AggregateEventArgs($invoker, $pipeline);

        $this->assertSame($invoker, $aggregateEventArgs->getInvoker());
        $this->assertSame($pipeline, $aggregateEventArgs->getPipeline());
    }
}
