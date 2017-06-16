<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Connection;
use Doctrine\Common\EventManager;

class EventTest extends TestCase
{
    public function testEventArgsNamespaceTest() 
    {
        $listener = $this->createMock(ListenerStub::class);
        $listener
            ->expects($this->once())
            ->method('preConnect');
        $manager  = new EventManager();

        $manager->addEventListener(\Doctrine\MongoDB\Events::preConnect, $listener);

        $connection = new Connection(null, [], null, $manager);
        $connection->initialize();
    }
}

class ListenerStub {
    function preConnect() {}
}
