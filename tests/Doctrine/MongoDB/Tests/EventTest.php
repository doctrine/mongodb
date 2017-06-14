<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Connection;
use Doctrine\Common\EventManager;
use PHPUnit\Framework\TestCase;

class EventTest extends TestCase
{
    public function testEventArgsNamespaceTest() 
    {
        $listener = new ListenerStub();
        $manager  = new EventManager();

        $manager->addEventListener(\Doctrine\MongoDB\Events::preConnect, $listener);

        $connection = new Connection(null, [], null, $manager);
        $connection->initialize();
    }
}

class ListenerStub {
    function preConnect() {}
}
