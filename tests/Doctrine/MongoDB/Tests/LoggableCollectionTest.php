<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\LoggableCollection;
use Doctrine\Common\EventManager;

class LoggableCollectionTest extends \PHPUnit_Framework_TestCase
{
    public function testLog()
    {
        $called = false;

        $loggerCallable = function($msg) use (&$called) {
            $called = $msg;
        };

        $database = $this->getMockDatabase();

        $database->expects($this->once())
            ->method('getName')
            ->will($this->returnValue('test'));

        $coll = new LoggableCollection($this->getMockConnection(), 'foo', $database, new EventManager(), '$', $loggerCallable);
        $coll->log(array('test' => 'test'));

        $this->assertEquals(array('collection' => 'foo', 'db' => 'test', 'test' => 'test'), $called);
    }

    private function getMockDatabase()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockConnection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
