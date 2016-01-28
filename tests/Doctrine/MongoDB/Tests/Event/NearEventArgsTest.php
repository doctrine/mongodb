<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\NearEventArgs;

class NearEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testNearEventArgs()
    {
        $invoker = new \stdClass();
        $query = array('x' => 1);
        $near = array(10, 20);
        $options = array('limit' => 5);

        $nearEventArgs = new NearEventArgs($invoker, $query, $near, $options);

        $this->assertSame($invoker, $nearEventArgs->getInvoker());
        $this->assertSame($query, $nearEventArgs->getQuery());
        $this->assertSame($near, $nearEventArgs->getNear());
        $this->assertSame($options, $nearEventArgs->getOptions());

        $query2 = array('x' => 2);
        $near2 = array(20, 30);
        $options2 = array('limit' => 6);

        $nearEventArgs->setQuery($query2);
        $nearEventArgs->setNear($near2);
        $nearEventArgs->setOptions($options2);

        $this->assertSame($query2, $nearEventArgs->getQuery());
        $this->assertSame($near2, $nearEventArgs->getNear());
        $this->assertSame($options2, $nearEventArgs->getOptions());
    }
}
