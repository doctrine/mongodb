<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\GroupEventArgs;

class GroupEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testGroupEventArgs()
    {
        $invoker = new \stdClass();
        $keys = 'x';
        $initial = array('count' => 0);
        $reduce = new \MongoCode('');
        $options = array('finalize' => new \MongoCode(''));

        $groupEventArgs = new GroupEventArgs($invoker, $keys, $initial, $reduce, $options);

        $this->assertSame($invoker, $groupEventArgs->getInvoker());
        $this->assertSame($keys, $groupEventArgs->getKeys());
        $this->assertSame($initial, $groupEventArgs->getInitial());
        $this->assertSame($reduce, $groupEventArgs->getReduce());
        $this->assertSame($options, $groupEventArgs->getOptions());
    }
}
