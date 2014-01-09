<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\Cursor;

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

        $collection = $this->conn->selectCollection(self::$dbName, 'CursorTest');
        $collection->drop();
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
        $collection->remove(array());
        $cursor = $collection->createQueryBuilder()->getQuery()->execute();
        $this->assertNull($cursor->getSingleResult());
    }

    public function testGetSingleResultWhenIdIsObject()
    {
        $doc = array('_id' => array('key' => 'value'), 'test' => 'value');

        $collection = $this->conn->selectCollection(self::$dbName, 'tmp');
        $collection->insert($doc);

        $cursor = $collection->find();

        $this->assertNotNull($cursor->getSingleResult());
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

    public function testSlaveOkay()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '>=')) {
            $this->markTestSkipped('This test is not applicable to driver versions >= 1.3.0');
        }

        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->at(0))
            ->method('slaveOkay')
            ->with(true);

        $mongoCursor->expects($this->at(1))
            ->method('slaveOkay')
            ->with(false);

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $cursor->slaveOkay(true);
        $cursor->slaveOkay(false);
    }

    public function testSlaveOkayNoopWithoutReadPreferences()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }

        if (method_exists('MongoCursor', 'setReadPreference')) {
            $this->markTestSkipped('This test is not applicable to drivers with MongoCursor::setReadPreference()');
        }

        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->never())
            ->method('slaveOkay');

        $mongoCursor->expects($this->never())
            ->method('setReadPreference');

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $cursor->slaveOkay(true);
        $cursor->slaveOkay(false);
    }

    public function testSlaveOkayReadPreferences()
    {
        if (!method_exists('MongoCursor', 'setReadPreference')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCursor::setReadPreference()');
        }

        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->never())->method('slaveOkay');

        $mongoCursor->expects($this->once())
            ->method('getReadPreference')
            ->will($this->returnValue(array(
                'type' => \MongoClient::RP_PRIMARY,
            )));

        $mongoCursor->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array());

        $mongoCursor->expects($this->at(2))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY);

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $cursor->slaveOkay(true);
        $cursor->slaveOkay(false);
    }

    public function testSlaveOkayPreservesReadPreferenceTags()
    {
        if (!method_exists('MongoCursor', 'setReadPreference')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCursor::setReadPreference()');
        }

        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->once())
            ->method('getReadPreference')
            ->will($this->returnValue(array(
                'type' => \MongoClient::RP_PRIMARY_PREFERRED,
                'tagsets' => array(array('dc' => 'east')),
            )));

        $mongoCursor->expects($this->once())
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east')))
            ->will($this->returnValue(false));

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $cursor->slaveOkay(true);
    }

    public function testSetReadPreference()
    {
        if (!method_exists('MongoCursor', 'setReadPreference')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCursor::setReadPreference()');
        }

        $mongoCursor = $this->getMockMongoCursor();

        $mongoCursor->expects($this->at(0))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_PRIMARY)
            ->will($this->returnValue(true));

        $mongoCursor->expects($this->at(1))
            ->method('setReadPreference')
            ->with(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east')))
            ->will($this->returnValue(true));

        $cursor = $this->getTestCursor($this->getMockCollection(), $mongoCursor);

        $this->assertSame($cursor, $cursor->setReadPreference(\MongoClient::RP_PRIMARY));
        $this->assertSame($cursor, $cursor->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, array(array('dc' => 'east'))));
    }

    public function testRecreate()
    {
        if (!method_exists('MongoCursor', 'setReadPreference')) {
            $this->markTestSkipped('This test requires MongoCursor::setReadPreference()');
        }

        $self = $this;

        $setCursorExpectations = function($mongoCursor) use ($self) {
            $mongoCursor->expects($self->once())
                ->method('hint')
                ->with(array('x' => 1));
            $mongoCursor->expects($self->once())
                ->method('immortal')
                ->with(false);
            $mongoCursor->expects($self->at(2))
                ->method('addOption')
                ->with('$min', array('x' => 9000));
            $mongoCursor->expects($self->at(3))
                ->method('addOption')
                ->with('$max', array('x' => 9999));
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
                ->with(\MongoClient::RP_NEAREST, array(array('dc' => 'east')))
                ->will($self->returnValue(true));
            $mongoCursor->expects($self->once())
                ->method('snapshot');
            $mongoCursor->expects($self->once())
                ->method('sort')
                ->with(array('x' => -1));
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
            ->with(array('x' => 9500), array())
            ->will($this->returnValue($recreatedMongoCursor));

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('getMongoCollection')
            ->will($this->returnValue($mongoCollection));

        $cursor = $this->getTestCursor($collection, $mongoCursor, array('x' => 9500));

        $cursor
            ->hint(array('x' => 1))
            ->immortal(false)
            ->addOption('$min', array('x' => 9000))
            ->addOption('$max', array('x' => 9999))
            ->batchSize(10)
            ->limit(20)
            ->skip(0)
            ->slaveOkay(false)
            ->setReadPreference(\MongoClient::RP_NEAREST, array(array('dc' => 'east')))
            ->snapshot()
            ->sort(array('x' => -1))
            ->tailable(false)
            ->timeout(1000);

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

    private function getTestCursor(Collection $collection, \MongoCursor $mongoCursor, $query = array())
    {
        return new Cursor($collection, $mongoCursor, $query);
    }
}
