<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Connection;
use Doctrine\Common\EventManager;
use PHPUnit_Framework_TestCase;
use Mongo;

class EventTest extends PHPUnit_Framework_TestCase
{
    public function testEventArgsNamespaceTest() 
    {
        $listener = new ListenerStub();
        $manager  = new EventManager();

        $manager->addEventListener(\Doctrine\MongoDB\Events::preConnect, $listener);

        $connection = new Connection(null, array(), null, $manager);
        $connection->initialize();
    }
}

class ListenerStub {
    function preConnect() {}
}