<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\NearEventArgs;

class NearEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testNearEventArgs()
    {
        $invoker = new \stdClass();
        $query = ['x' => 1];
        $near = [10, 20];
        $options = ['limit' => 5];

        $nearEventArgs = new NearEventArgs($invoker, $query, $near, $options);

        $this->assertSame($invoker, $nearEventArgs->getInvoker());
        $this->assertSame($query, $nearEventArgs->getQuery());
        $this->assertSame($near, $nearEventArgs->getNear());
        $this->assertSame($options, $nearEventArgs->getOptions());
    }
}
