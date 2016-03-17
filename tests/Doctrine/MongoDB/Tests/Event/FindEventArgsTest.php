<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\FindEventArgs;

class FindEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testFindEventArgs()
    {
        $invoker = new \stdClass();
        $query = array('x' => 1);
        $fields = array('_id' => 0);

        $findEventArgs = new FindEventArgs($invoker, $query, $fields);

        $this->assertSame($invoker, $findEventArgs->getInvoker());
        $this->assertSame($query, $findEventArgs->getQuery());
        $this->assertSame($fields, $findEventArgs->getFields());

        $query2 = array('x' => 2);
        $fields2 = array('_id' => 1);

        $findEventArgs->setQuery($query2);
        $findEventArgs->setFields($fields2);

        $this->assertSame($query2, $findEventArgs->getQuery());
        $this->assertSame($fields2, $findEventArgs->getFields());
    }
}
