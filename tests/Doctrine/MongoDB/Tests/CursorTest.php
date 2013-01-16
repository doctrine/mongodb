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

        $cursor = $this->getTestCursor($this->getMockConnection(), $this->getMockCollection(), $mongoCursor);

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

        $cursor = $this->getTestCursor($this->getMockConnection(), $this->getMockCollection(), $mongoCursor);

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

        $cursor = $this->getTestCursor($this->getMockConnection(), $this->getMockCollection(), $mongoCursor);

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

        $cursor = $this->getTestCursor($this->getMockConnection(), $this->getMockCollection(), $mongoCursor);

        $cursor->slaveOkay(true);
    }

    private function getMockMongoCursor()
    {
        return $this->getMockBuilder('MongoCursor')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockConnection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getTestCursor(Connection $connection, Collection $collection, \MongoCursor $mongoCursor)
    {
        return new Cursor($connection, $collection, $mongoCursor);
    }
}
