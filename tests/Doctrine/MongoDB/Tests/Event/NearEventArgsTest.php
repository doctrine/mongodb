<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\NearEventArgs;
use Doctrine\MongoDB\Tests\TestCase;

class NearEventArgsTest extends TestCase
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

        $query2 = ['x' => 2];
        $near2 = [20, 30];
        $options2 = ['limit' => 6];

        $nearEventArgs->setQuery($query2);
        $nearEventArgs->setNear($near2);
        $nearEventArgs->setOptions($options2);

        $this->assertSame($query2, $nearEventArgs->getQuery());
        $this->assertSame($near2, $nearEventArgs->getNear());
        $this->assertSame($options2, $nearEventArgs->getOptions());
    }
}
