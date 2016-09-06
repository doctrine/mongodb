<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\GridFSFile;
use Doctrine\MongoDB\LoggableGridFS;

class LoggableGridFSTest extends \PHPUnit_Framework_TestCase
{
    const collectionName = 'collectionName';
    const databaseName = 'databaseName';

    public function testLog()
    {
        $called = false;
        $loggableGridFS = $this->getLoggableGridFS($this->getLoggerCallable($called));
        $loggableGridFS->log(['test' => 'test']);

        $this->assertEquals(['collection' => self::collectionName, 'db' => self::databaseName, 'test' => 'test'], $called);
    }

    private function getLoggerCallable(&$called)
    {
        return function($msg) use (&$called) {
            $called = $msg;
        };
    }

    private function getLoggableGridFS($loggerCallable)
    {
        $database = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->disableOriginalConstructor()
            ->getMock();

        $database->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::databaseName));

        $mongoCollection = $this->getMockBuilder('MongoGridFS')
            ->disableOriginalConstructor()
            ->getMock();

        $mongoCollection->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::collectionName));

        $eventManager = $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();

        return new LoggableGridFS($database, $mongoCollection, $eventManager, 0, $loggerCallable);
    }
}
