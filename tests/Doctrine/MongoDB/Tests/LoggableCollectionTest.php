<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\LoggableCollection;

class LoggableCollectionTest extends \PHPUnit_Framework_TestCase
{
    const collectionName = 'collection';
    const databaseName = 'database';

    public function testLog()
    {
        $called = false;

        $loggerCallable = function($msg) use (&$called) {
            $called = $msg;
        };

        $collection = $this->getTestLoggableDatabase($loggerCallable);
        $collection->log(array('test' => 'test'));

        $this->assertEquals(array('collection' => self::collectionName, 'db' => self::databaseName, 'test' => 'test'), $called);
    }

    private function getTestLoggableDatabase($loggerCallable)
    {
        $c = $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $db = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->disableOriginalConstructor()
            ->getMock();

        $db->expects($this->any())
            ->method('getName')
            ->will($this->returnValue(self::databaseName));

        $em = $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();

        return new LoggableCollection($c, self::collectionName, $db, $em, $loggerCallable);
    }
}
