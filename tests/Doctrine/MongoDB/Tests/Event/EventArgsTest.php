<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\EventArgs;

class EventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testEventArgs()
    {
        $invoker = new \stdClass();
        $data = ['ok' => 1];
        $options = ['w' => 1];

        $eventArgs = new EventArgs($invoker, $data, $options);

        $this->assertSame($invoker, $eventArgs->getInvoker());
        $this->assertSame($data, $eventArgs->getData());
        $this->assertSame($options, $eventArgs->getOptions());
    }
}
