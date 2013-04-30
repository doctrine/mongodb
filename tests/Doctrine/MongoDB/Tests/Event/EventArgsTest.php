<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\EventArgs;

class EventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testEventArgs()
    {
        $invoker = new \stdClass();
        $data = array('ok' => 1);

        $eventArgs = new EventArgs($invoker, $data);

        $this->assertSame($invoker, $eventArgs->getInvoker());
        $this->assertSame($data, $eventArgs->getData());
    }
}
