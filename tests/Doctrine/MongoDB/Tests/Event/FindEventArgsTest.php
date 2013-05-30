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
    }
}
