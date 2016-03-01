<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\UpdateEventArgs;

class UpdateEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testUpdateEventArgs()
    {
        $invoker = new \stdClass();
        $query = ['x' => 1];
        $newObj = ['$set' => ['x' => 2]];
        $options = ['upsert' => true];

        $updateEventArgs = new UpdateEventArgs($invoker, $query, $newObj, $options);

        $this->assertSame($invoker, $updateEventArgs->getInvoker());
        $this->assertSame($query, $updateEventArgs->getQuery());
        $this->assertSame($newObj, $updateEventArgs->getNewObj());
        $this->assertSame($options, $updateEventArgs->getOptions());
    }
}
