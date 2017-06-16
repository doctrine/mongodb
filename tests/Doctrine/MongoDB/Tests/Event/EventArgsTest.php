<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Tests\TestCase;

class EventArgsTest extends TestCase
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
