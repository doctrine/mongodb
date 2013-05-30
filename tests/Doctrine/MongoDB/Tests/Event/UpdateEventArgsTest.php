<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\UpdateEventArgs;

class UpdateEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdateEventArgs()
    {
        $invoker = new \stdClass();
        $query = array('x' => 1);
        $newObj = array('$set' => array('x' => 2));
        $options = array('upsert' => true);

        $updateEventArgs = new UpdateEventArgs($invoker, $query, $newObj, $options);

        $this->assertSame($invoker, $updateEventArgs->getInvoker());
        $this->assertSame($query, $updateEventArgs->getQuery());
        $this->assertSame($newObj, $updateEventArgs->getNewObj());
        $this->assertSame($options, $updateEventArgs->getOptions());
    }
}
