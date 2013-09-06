<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\LoggableDatabase;

class LoggableDatabaseTest extends \PHPUnit_Framework_TestCase
{
    const databaseName = 'database';

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

    public function testCreateCollectionWithMultipleArguments()
    {
        $called = false;

        $loggerCallable = function($msg) use (&$called) {
            $called = $msg;
        };

        $db = $this->getTestLoggableDatabase($loggerCallable);
        $db->createCollection('foo', true, 10485760, 0);

        $expected = array(
            'createCollection' => true,
            'name' => 'foo',
            'options' => array('capped' => true, 'size' => 10485760, 'max' => 0),
            'capped' => true,
            'size' => 10485760,
            'max' => 0,
            'db' => self::databaseName,
        );

        $this->assertEquals($expected, $called);
    }

    public function testCreateCollectionWithOptionsArgument()
    {
        $called = false;

        $loggerCallable = function($msg) use (&$called) {
            $called = $msg;
        };

        $db = $this->getTestLoggableDatabase($loggerCallable);
        $db->createCollection('foo', array('capped' => true, 'size' => 10485760, 'autoIndexId' => false));

        $expected = array(
            'createCollection' => true,
            'name' => 'foo',
            'options' => array('capped' => true, 'size' => 10485760, 'max' => 0, 'autoIndexId' => false),
            'capped' => true,
            'size' => 10485760,
            'max' => 0,
            'db' => self::databaseName,
        );

        $this->assertEquals($expected, $called);
    }

    private function getTestLoggableDatabase($loggerCallable)
    {
        $c = $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $mdb = $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();

        $em = $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();

        $db = new TestLoggableDatabaseStub($c, self::databaseName, $em, 0, $loggerCallable);
        $db->setMongoDB($mdb);

        return $db;
    }
}

class TestLoggableDatabaseStub extends LoggableDatabase
{
    public function setMongoDB($mongoDB)
    {
        $this->mongoDB = $mongoDB;
    }

    public function getMongoDB()
    {
        return $this->mongoDB;
    }
}
