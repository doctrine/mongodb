<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\GroupEventArgs;
use Doctrine\MongoDB\Tests\TestCase;

class GroupEventArgsTest extends TestCase
{
    public function testGroupEventArgs()
    {
        $invoker = new \stdClass();
        $keys = 'x';
        $initial = ['count' => 0];
        $reduce = new \MongoCode('');
        $options = ['finalize' => new \MongoCode('')];

        $groupEventArgs = new GroupEventArgs($invoker, $keys, $initial, $reduce, $options);

        $this->assertSame($invoker, $groupEventArgs->getInvoker());
        $this->assertSame($keys, $groupEventArgs->getKeys());
        $this->assertSame($initial, $groupEventArgs->getInitial());
        $this->assertSame($reduce, $groupEventArgs->getReduce());
        $this->assertSame($options, $groupEventArgs->getOptions());
    }
}
