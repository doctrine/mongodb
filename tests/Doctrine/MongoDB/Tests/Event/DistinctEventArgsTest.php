<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\DistinctEventArgs;

class DistinctEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testDistinctEventArgs()
    {
        $invoker = new \stdClass();
        $field = 'x';
        $query = array('y' => 1);

        $distinctEventArgs = new DistinctEventArgs($invoker, $field, $query);

        $this->assertSame($invoker, $distinctEventArgs->getInvoker());
        $this->assertSame($field, $distinctEventArgs->getField());
        $this->assertSame($query, $distinctEventArgs->getQuery());
    }
}
