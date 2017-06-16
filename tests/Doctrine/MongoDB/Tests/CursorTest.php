<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Cursor;

class CursorTest extends DatabaseTestCase
{
    private $doc1;
    private $doc2;
    private $doc3;
    private $cursor;

    public function setUp()
    {
        parent::setUp();

        $this->doc1 = ['name' => 'A'];
        $this->doc2 = ['name' => 'B'];
        $this->doc3 = ['name' => 'C'];

        $collection = $this->conn->selectCollection(self::$dbName, 'CursorTest');
        $collection->drop();
        $collection->insert($this->doc1);
        $collection->insert($this->doc2);
        $collection->insert($this->doc3);

        $this->cursor = $collection->createQueryBuilder()->getQuery()->execute();
        $this->cursor->sort(['name' => 1]);
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
        $expected = [$this->doc1, $this->doc2, $this->doc3];
        $actual = [];
        foreach ($this->cursor as $entry) {
            $actual[] = $entry;
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * @covers Doctrine\MongoDB\Cursor::getSingleResult
     */
    public function testCursorIsResetBeforeGetSingleResult()
    {
        $this->assertEquals($this->doc1, $this->cursor->getNext());
        $this->assertEquals($this->doc1, $this->cursor->getSingleResult());
    }

    /**
     * @covers Doctrine\MongoDB\Cursor::getSingleResult
     */
    public function testGetSingleResultReturnsNull()
    {
        $collection = $this->conn->selectCollection(self::$dbName, 'tmp');
        $collection->remove([]);
        $cursor = $collection->createQueryBuilder()->getQuery()->execute();
        $this->assertNull($cursor->getSingleResult());
    }

    public function testGetSingleResultWhenIdIsObject()
    {
        $doc = ['_id' => ['key' => 'value'], 'test' => 'value'];

        $collection = $this->conn->selectCollection(self::$dbName, 'tmp');
        $collection->insert($doc);

        $cursor = $collection->find();

        $this->assertNotNull($cursor->getSingleResult());
    }

    public function testSetUseIdentifierKeys()
    {
        $this->cursor->setUseIdentifierKeys(false);
        $this->assertFalse($this->cursor->getUseIdentifierKeys());

        foreach ($this->cursor as $key => $document) {
            /* Note: Driver versions before 1.5.0 had an off-by-one error and
             * count from one, so just assert the key's type here.
             */
            $this->assertTrue(is_integer($key));
        }
    }

    public function testToArray()
    {
        $this->assertEquals(
            [
                (string) $this->doc1['_id'] => $this->doc1,
                (string) $this->doc2['_id'] => $this->doc2,
                (string) $this->doc3['_id'] => $this->doc3,
            ],
            $this->cursor->toArray()
        );
    }

    public function testToArrayWithKeysOverridesClassOption()
    {
        $this->cursor->setUseIdentifierKeys(false);

        $this->assertEquals(
            [
                (string) $this->doc1['_id'] => $this->doc1,
                (string) $this->doc2['_id'] => $this->doc2,
                (string) $this->doc3['_id'] => $this->doc3,
            ],
            $this->cursor->toArray(true)
        );
    }

    public function testToArrayWithoutKeysOverridesClassOption()
    {
        $this->cursor->setUseIdentifierKeys(false);

        $this->assertEquals([$this->doc1, $this->doc2, $this->doc3], $this->cursor->toArray(false));
    }

    public function testSlaveOkayReadPreferences()
    {
        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->never())->method('slaveOkay');

        $mongoCursor->expects($this->once())
            ->method('getReadPreference')
            ->will($this->returnValue([
                'type' => \MongoClient::RP_PRIMARY,
            ]));

        $mongoCursor->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, []);

        $mongoCursor->expects($this->at(2))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY);

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $cursor->slaveOkay(true);
        $cursor->slaveOkay(false);
    }

    public function testSlaveOkayPreservesReadPreferenceTags()
    {
        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->once())
            ->method('getReadPreference')
            ->will($this->returnValue([
                'type' => \MongoClient::RP_PRIMARY_PREFERRED,
                'tagsets' => [['dc' => 'east']],
            ]));

        $mongoCursor->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, [['dc' => 'east']])
            ->will($this->returnValue(false));

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $cursor->slaveOkay(true);
    }

    public function testSetReadPreference()
    {
        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->at(0))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY)
            ->will($this->returnValue(true));

        $mongoCursor->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, [['dc' => 'east']])
            ->will($this->returnValue(true));

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $this->assertSame($cursor, $cursor->setReadPreference(\MongoClient::RP_PRIMARY));
        $this->assertSame($cursor, $cursor->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, [['dc' => 'east']]));
    }

    /**
     * @dataProvider provideSortOrderValues
     */
    public function testSortOrderConversion($actual, $expected)
    {
        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->once())
            ->method('sort')
            ->with(['x' => $expected]);

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $cursor->sort(['x' => $actual]);
    }

    public function provideSortOrderValues()
    {
        return [
            // Strings should be compared to "asc"
            ['asc', 1],
            ['ASC', 1],
            ['desc', -1],
            ['DESC', -1],
            ['not-asc', -1],
            // Scalar values should be cast to integers (even though boolean false doesn't make sense)
            [1.0, 1],
            [-1.0, -1],
            [true, 1],
            [false, 0],
            // Non-scalar values should be left as-is
            [['$meta' => 'textScore'], ['$meta' => 'textScore']],
        ];
    }

    public function testRecreate()
    {
        $self = $this;

        $setCursorExpectations = function($mongoCursor) use ($self) {
            $mongoCursor->expects($self->once())
                ->method('hint')
                ->with(['x' => 1]);
            $mongoCursor->expects($self->once())
                ->method('immortal')
                ->with(false);
            $mongoCursor->expects($self->at(2))
                ->method('addOption')
                ->with('$min', ['x' => 9000]);
            $mongoCursor->expects($self->at(3))
                ->method('addOption')
                ->with('$max', ['x' => 9999]);
            $mongoCursor->expects($self->once())
                ->method('batchSize')
                ->with(10);
            $mongoCursor->expects($self->once())
                ->method('limit')
                ->with(20);
            $mongoCursor->expects($self->once())
                ->method('skip')
                ->with(0);
            $mongoCursor->expects($self->at(7))
                ->method('setReadPreference')
                ->with(\MongoClient::RP_PRIMARY)
                ->will($self->returnValue(true));
            $mongoCursor->expects($self->at(8))
                ->method('setReadPreference')
                ->with(\MongoClient::RP_NEAREST, [['dc' => 'east']])
                ->will($self->returnValue(true));
            $mongoCursor->expects($self->once())
                ->method('snapshot');
            $mongoCursor->expects($self->once())
                ->method('sort')
                ->with(['x' => -1]);
            $mongoCursor->expects($self->once())
                ->method('tailable')
                ->with(false);
            $mongoCursor->expects($self->once())
                ->method('timeout')
                ->with(1000);
        };

        $mongoCursor = $this->getMockMongoCursor();
        $recreatedMongoCursor = $this->getMockMongoCursor();

        $setCursorExpectations($mongoCursor);
        $setCursorExpectations($recreatedMongoCursor);

        $mongoCollection = $this->getMockCollection();
        $mongoCollection->expects($this->once())
            ->method('find')
            ->with(['x' => 9500], [])
            ->will($this->returnValue($recreatedMongoCursor));

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('getMongoCollection')
            ->will($this->returnValue($mongoCollection));

        $cursor = $this->getTestCursor($collection, $mongoCursor, ['x' => 9500]);

        $cursor
            ->hint(['x' => 1])
            ->immortal(false)
            ->addOption('$min', ['x' => 9000])
            ->addOption('$max', ['x' => 9999])
            ->batchSize(10)
            ->limit(20)
            ->skip(0)
            ->slaveOkay(false)
            ->setReadPreference(\MongoClient::RP_NEAREST, [['dc' => 'east']])
            ->snapshot()
            ->sort(['x' => -1])
            ->tailable(false)
            ->timeout(1000);

        $cursor->recreate();
    }

    public function testSetMaxTimeMSWhenRecreateCursor()
    {
        $self = $this;

        $setCursorExpectations = function($mongoCursor) use ($self) {
            $mongoCursor->expects($self->once())
                ->method('maxTimeMS')
                ->with(30000);
        };

        $mongoCursor = $this->getMockMongoCursor();
        $recreatedMongoCursor = $this->getMockMongoCursor();

        $setCursorExpectations($mongoCursor);
        $setCursorExpectations($recreatedMongoCursor);

        $mongoCollection = $this->getMockCollection();
        $mongoCollection->expects($this->once())
            ->method('find')
            ->with(['x' => 9500], [])
            ->will($this->returnValue($recreatedMongoCursor));

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('getMongoCollection')
            ->will($this->returnValue($mongoCollection));

        $cursor = $this->getTestCursor($collection, $mongoCursor, ['x' => 9500]);

        $cursor->maxTimeMS(30000);

        $cursor->recreate();
    }

    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockMongoCursor()
    {
        return $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTestCursor(Collection $collection, \MongoCursor $mongoCursor, $query = [])
    {
        return new Cursor($collection, $mongoCursor, $query);
    }
}
