<?php

namespace Doctrine\MongoDB\Tests;

class CursorTest extends BaseTest
{
    private $doc1;
    private $doc2;
    private $doc3;

    private $cursor;

    public function setUp()
    {
        parent::setUp();

        $this->doc1 = array('name' => 'A');
        $this->doc2 = array('name' => 'B');
        $this->doc3 = array('name' => 'C');

        $collection = $this->conn->selectCollection(self::$dbName, 'docs');
        $collection->insert($this->doc1);
        $collection->insert($this->doc2);
        $collection->insert($this->doc3);

        $this->cursor = $collection->createQueryBuilder()->getQuery()->execute();
        $this->cursor->sort(array('name' => 1));
    }

    /**
     * @covers Doctrine\MongoDB\Cursor::getSingleResult
     */
    public function testGetSingleResult()
    {
        $this->assertEquals($this->doc1, $this->cursor->getSingleResult());
    }

    /**
     * @covers Doctrine\MongoDB\Cursor::getSingleResult
     */
    public function testCursorIsResetAfterGetSingleResult()
    {
        $this->assertEquals($this->doc1, $this->cursor->getSingleResult());

        // Make sure limit is restored and cursor is rewound
        $expected = array($this->doc1, $this->doc2, $this->doc3);
        $actual = array();
        foreach ($this->cursor as $entry) {
            $actual[] = $entry;
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers Doctrine\MongoDB\Cursor::getSingleResult
     */
    public function testGetSingleResultReturnsNull()
    {
        $collection = $this->conn->selectCollection(self::$dbName, 'tmp');
        $collection->remove(array());
        $cursor = $collection->createQueryBuilder()->getQuery()->execute();
        $this->assertNull($cursor->getSingleResult());
    }

    public function testToArray()
    {
        $this->assertEquals(
            array(
                (string) $this->doc1['_id'] => $this->doc1,
                (string) $this->doc2['_id'] => $this->doc2,
                (string) $this->doc3['_id'] => $this->doc3
            ),
            $this->cursor->toArray()
        );
    }

    public function testToArrayWithoutKeys()
    {
        $this->assertEquals(array($this->doc1, $this->doc2, $this->doc3), $this->cursor->toArray(false));
    }
}