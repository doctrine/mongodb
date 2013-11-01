<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\LoggableDatabase;

class LoggableDatabaseTest extends \PHPUnit_Framework_TestCase
{
    const databaseName = 'databaseName';

    public function testLog()
    {
        $called = false;

        $loggerCallable = function($msg) use (&$called) {
            $called = $msg;
        };

        $db = $this->getTestLoggableDatabase($loggerCallable);
        $db->log(array('test' => 'test'));

        $this->assertEquals(array('db' => self::databaseName, 'test' => 'test'), $called);
    }

    private function getTestLoggableDatabase($loggerCallable)
    {
        $connection = $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoDB = $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoDB->expects($this->any())
            ->method('__toString')
            ->will($this->returnValue(self::databaseName));

        $eventManager = $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();

        return new LoggableDatabase($connection, $mongoDB, $eventManager, 0, $loggerCallable);
    }
}
