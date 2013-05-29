<?php

namespace Doctrine\MongoDB\Tests;

class EagerCursorTest extends BaseTest
{
    private $doc1;
    private $doc2;
    private $cursor;

    public function setUp()
    {
        parent::setUp();

        $this->doc1 = array('name' => 'A');
        $this->doc2 = array('name' => 'B');

        $collection = $this->conn->selectCollection(self::$dbName, 'EagerCursorTest');
        $collection->drop();
        $collection->insert($this->doc1);
        $collection->insert($this->doc2);

        $this->cursor = $collection->createQueryBuilder()->eagerCursor(true)->getQuery()->execute();
    }

    public function testEagerCursor()
    {
        $this->assertInstanceOf('Doctrine\MongoDB\EagerCursor', $this->cursor);
    }

    public function testIsInitialized()
    {
        $this->assertFalse($this->cursor->isInitialized());
        $this->cursor->initialize();
        $this->assertTrue($this->cursor->isInitialized());
    }

    public function testCount()
    {
        $this->assertEquals(2, count($this->cursor));
    }

    public function testCountIsImplicitlyFoundOnly()
    {
        $this->cursor->getCursor()->limit(1);
        $this->assertEquals(1, count($this->cursor));
    }

    public function testGetSingleResult()
    {
        $this->assertEquals($this->doc1, $this->cursor->getSingleResult());
    }

    public function testToArray()
    {
        $this->assertEquals(
            array(
                (string) $this->doc1['_id'] => $this->doc1,
                (string) $this->doc2['_id'] => $this->doc2,
            ),
            $this->cursor->toArray()
        );
    }

    public function testRewind()
    {
        foreach (range(1,2) as $_) {
            $this->assertEquals($this->doc1, $this->cursor->current());
            $this->cursor->next();
            $this->assertEquals($this->doc2, $this->cursor->current());
            $this->cursor->next();
            $this->assertFalse($this->cursor->valid());

            $this->cursor->rewind();
        }
    }
}
