<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\AggregateEventArgs;
use Doctrine\MongoDB\Tests\TestCase;

class AggregateEventArgsTest extends TestCase
{
    public function testAggregateEventArgs()
    {
        $invoker = new \stdClass();
        $pipeline = [['$match' => ['_id' => 1]]];

        $aggregateEventArgs = new AggregateEventArgs($invoker, $pipeline);

        $this->assertSame($invoker, $aggregateEventArgs->getInvoker());
        $this->assertSame($pipeline, $aggregateEventArgs->getPipeline());
    }
}
