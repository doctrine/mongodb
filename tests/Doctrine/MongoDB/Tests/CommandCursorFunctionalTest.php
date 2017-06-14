<?php

namespace Doctrine\MongoDB\Tests;

class CommandCursorFunctionalTest extends DatabaseTestCase
{
    private $collection;
    private $docs;

    public function setUp()
    {
        parent::setUp();

        if (version_compare($this->getServerVersion(), '2.6.0', '<')) {
            $this->markTestSkipped('This test is not applicable to server versions < 2.6.0');
        }

        $this->docs = [
            ['_id' => 1],
            ['_id' => 2],
            ['_id' => 3],
        ];

        $this->collection = $this->conn->selectCollection(self::$dbName, 'CommandCursorFunctionalTest');
        $this->collection->drop();

        foreach ($this->docs as $doc) {
            $this->collection->insert($doc);
        }
    }

    public function testCount()
    {
        $commandCursor = $this->collection->aggregate(
            [['$sort' => ['_id' => 1]]],
            ['cursor' => true]
        );

        $this->assertCount(3, $commandCursor);
    }

    public function testGetSingleResult()
    {
        $commandCursor = $this->collection->aggregate(
            [['$sort' => ['_id' => 1]]],
            ['cursor' => true]
        );

        $this->assertEquals($this->docs[0], $commandCursor->getSingleResult());
    }

    public function testGetSingleResultRewindsBeforeReturningFirstResult()
    {
        $commandCursor = $this->collection->aggregate(
            [['$sort' => ['_id' => 1]]],
            ['cursor' => true]
        );

        $commandCursor->rewind();
        $commandCursor->next();
        $this->assertTrue($commandCursor->valid());
        $this->assertEquals($this->docs[1], $commandCursor->current());
        $this->assertEquals($this->docs[0], $commandCursor->getSingleResult());
    }

    /**
     * @covers Doctrine\MongoDB\Cursor::getSingleResult
     */
    public function testGetSingleResultReturnsNullForEmptyResultSet()
    {
        $commandCursor = $this->collection->aggregate(
            [['$match' => ['_id' => 0]]],
            ['cursor' => true]
        );

        $this->assertNull($commandCursor->getSingleResult());
    }

    public function testToArray()
    {
        $commandCursor = $this->collection->aggregate(
            [['$sort' => ['_id' => 1]]],
            ['cursor' => true]
        );

        $this->assertEquals($this->docs, $commandCursor->toArray());
    }
}
