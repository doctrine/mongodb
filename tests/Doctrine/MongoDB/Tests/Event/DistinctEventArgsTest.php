<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\DistinctEventArgs;

class DistinctEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testDistinctEventArgs()
    {
        $invoker = new \stdClass();
        $field = 'x';
        $query = ['y' => 1];

        $distinctEventArgs = new DistinctEventArgs($invoker, $field, $query);

        $this->assertSame($invoker, $distinctEventArgs->getInvoker());
        $this->assertSame($field, $distinctEventArgs->getField());
        $this->assertSame($query, $distinctEventArgs->getQuery());

        $field2 = 'y';
        $query2 = ['y' => 2];

        $distinctEventArgs->setQuery($query2);
        $distinctEventArgs->setField($field2);

        $this->assertSame($field2, $distinctEventArgs->getField());
        $this->assertSame($query2, $distinctEventArgs->getQuery());
    }
}
