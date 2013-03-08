<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\EventArgs;

class EventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testEventArgs()
    {
        $invoker = new \stdClass;
        $data = array();
        $eventArgs = new EventArgs($invoker, $data);
        $eventArgs->setData(array('test'));
        $this->assertEquals($data, array('test'));
    }
}
