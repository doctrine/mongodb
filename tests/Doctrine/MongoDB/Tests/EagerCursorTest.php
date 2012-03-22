<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\GridFSFile;

class EagerCursorTest extends BaseTest
{
    private $document;
    private $test;

    public function setUp()
    {
        parent::setUp();
        $this->document = array('test' => 'test');
        $this->conn->selectCollection(self::$dbName, 'users')->insert($this->document);

        $qb = $this->conn->selectCollection(self::$dbName, 'users')->createQueryBuilder();
        $qb->eagerCursor(true);
        $this->test = $qb->getQuery()->execute();
    }

    public function testEagerCursor()
    {
        $this->assertInstanceOf('Doctrine\MongoDB\EagerCursor', $this->test);
    }

    public function testIsInitialized()
    {
        $this->assertFalse($this->test->isInitialized());
        $this->test->initialize();
        $this->assertTrue($this->test->isInitialized());
    }

    public function testCount()
    {
        $this->assertEquals(1, count($this->test));
    }

    public function testGetSingleResult()
    {
        $this->assertEquals($this->document, $this->test->getSingleResult());
    }

    public function testToArray()
    {
        $this->assertEquals(array((string) $this->document['_id'] => $this->document), $this->test->toArray());
    }

    public function testRewind()
    {
        $this->test->toArray();
        $this->assertFalse($this->test->next());
        $this->test->rewind();
        $this->assertEquals($this->document, $this->test->current());
        $this->assertFalse($this->test->next());
    }
}