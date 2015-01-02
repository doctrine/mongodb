<?php

namespace Doctrine\MongoDB\Tests;

class CommandCursorFunctionalTest extends BaseTest
{
    private $collection;
    private $docs;

    public function setUp()
    {
        parent::setUp();

        if ( ! method_exists('MongoCollection', 'aggregateCursor')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCollection::aggregateCursor()');
        }

        if (version_compare($this->getServerVersion(), '2.6.0', '<')) {
            $this->markTestSkipped('This test is not applicable to server versions < 2.6.0');
        }

        $this->docs = array(
            array('_id' => 1),
            array('_id' => 2),
            array('_id' => 3),
        );

        $this->collection = $this->conn->selectCollection(self::$dbName, 'CommandCursorFunctionalTest');
        $this->collection->drop();

        foreach ($this->docs as $doc) {
            $this->collection->insert($doc);
        }
    }

    public function testCount()
    {
        $commandCursor = $this->collection->aggregate(
            array(array('$sort' => array('_id' => 1))),
            array('cursor' => true)
        );

        $this->assertCount(3, $commandCursor);
    }

    public function testGetSingleResult()
    {
        $commandCursor = $this->collection->aggregate(
            array(array('$sort' => array('_id' => 1))),
            array('cursor' => true)
        );

        $this->assertEquals($this->docs[0], $commandCursor->getSingleResult());
    }

    public function testGetSingleResultRewindsBeforeReturningFirstResult()
    {
        $commandCursor = $this->collection->aggregate(
            array(array('$sort' => array('_id' => 1))),
            array('cursor' => true)
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
            array(array('$match' => array('_id' => 0))),
            array('cursor' => true)
        );

        $this->assertNull($commandCursor->getSingleResult());
    }

    public function testToArray()
    {
        $commandCursor = $this->collection->aggregate(
            array(array('$sort' => array('_id' => 1))),
            array('cursor' => true)
        );

        $this->assertEquals($this->docs, $commandCursor->toArray());
    }
}
