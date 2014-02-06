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

    public function testClosureLogging()
    {
        $called = false;

        $loggerCallable = function($msg) use (&$called) {
            $called = $msg;
        };

        $db = $this->getTestLoggableDatabase($loggerCallable);
        $db->execute('{}');

        $this->assertInternalType('array', $called);
        $this->assertArrayHasKey('execute', $called);
        $this->assertArrayHasKey('code', $called);
        $this->assertArrayHasKey('args', $called);
        $this->assertArrayHasKey('db', $called);

        $this->assertEquals('{}', $called['code']);
        $this->assertEquals(self::databaseName, $called['db']);
    }

    public function testQueryLogger()
    {
        $logger = $this->getMockBuilder('Doctrine\MongoDB\Logging\QueryLogger')
                ->disableOriginalConstructor()
                ->getMock();

        $db = $this->getTestQueryLoggerEnabledLoggableDatabase($logger);

        $called = null;

        $logger->expects($this->once())
            ->method('startQuery')
            ->will($this->returnCallback(function($log)use(&$called){
                    $called = $log;
                }));

        $logger->expects($this->once())
            ->method('stopQuery');

        $db->execute('{}');

        $this->assertInternalType('array', $called);
        $this->assertArrayHasKey('execute', $called);
        $this->assertArrayHasKey('code', $called);
        $this->assertArrayHasKey('args', $called);
        $this->assertArrayHasKey('db', $called);

        $this->assertEquals('{}', $called['code']);
        $this->assertEquals(self::databaseName, $called['db']);
    }

    /**
     * @expectedException \InvalidArgumentException
     */
    public function testNoLoggerGivenThrowsException()
    {
        $db = $this->getTestLoggableDatabase(null);
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

    private function getTestQueryLoggerEnabledLoggableDatabase($logger)
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

        return new LoggableDatabase($connection, $mongoDB, $eventManager, 0, null, $logger);
    }
}