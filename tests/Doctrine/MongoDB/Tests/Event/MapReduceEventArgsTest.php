<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\MapReduceEventArgs;

class MapReduceEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testMapReduceEventArgs()
    {
        $invoker = new \stdClass();
        $map = new \MongoCode('');
        $reduce = new \MongoCode('');
        $out = ['inline' => true];
        $query = ['x' => 1];
        $options = ['finalize' => new \MongoCode('')];

        $mapReduceEventArgs = new MapReduceEventArgs($invoker, $map, $reduce, $out, $query, $options);

        $this->assertSame($invoker, $mapReduceEventArgs->getInvoker());
        $this->assertSame($map, $mapReduceEventArgs->getMap());
        $this->assertSame($reduce, $mapReduceEventArgs->getReduce());
        $this->assertSame($out, $mapReduceEventArgs->getOut());
        $this->assertSame($query, $mapReduceEventArgs->getQuery());
        $this->assertSame($options, $mapReduceEventArgs->getOptions());
    }
}
