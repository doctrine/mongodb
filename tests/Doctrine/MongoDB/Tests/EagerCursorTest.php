<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\EagerCursor;

class EagerCursorTest extends DatabaseTestCase
{
    public function testGetCursor()
    {
        $cursor = $this->getMockCursor();
        $eagerCursor = new EagerCursor($cursor);

        $this->assertSame($cursor, $eagerCursor->getCursor());
    }

    public function testInitializationConvertsCursorToArrayOnlyOnce()
    {
        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue([]));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertFalse($eagerCursor->isInitialized());
        $eagerCursor->initialize();
        $this->assertTrue($eagerCursor->isInitialized());
        $eagerCursor->initialize();
        $this->assertTrue($eagerCursor->isInitialized());
    }

    public function testCount()
    {
        $results = [
            ['_id' => 1, 'x' => 'foo'],
            ['_id' => 2, 'x' => 'bar'],
        ];

        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($results));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertFalse($eagerCursor->isInitialized());
        $this->assertEquals(2, count($eagerCursor));
        $this->assertTrue($eagerCursor->isInitialized());
    }

    public function testGetUseIdentifierKeys()
    {
        $cursor = $this->getMockCursor();

        $cursor->expects($this->at(0))
            ->method('getUseIdentifierKeys')
            ->will($this->returnValue(true));

        $cursor->expects($this->at(1))
            ->method('getUseIdentifierKeys')
            ->will($this->returnValue(false));

        $eagerCursor = new EagerCursor($cursor);
        $this->assertTrue($eagerCursor->getUseIdentifierKeys());
        $this->assertFalse($eagerCursor->getUseIdentifierKeys());
    }

    public function testSetUseIdentifierKeys()
    {
        $cursor = $this->getMockCursor();

        $cursor->expects($this->at(0))
            ->method('setUseIdentifierKeys')
            ->with(true);

        $cursor->expects($this->at(1))
            ->method('setUseIdentifierKeys')
            ->with(false);

        $eagerCursor = new EagerCursor($cursor);
        $eagerCursor->setUseIdentifierKeys(true);
        $eagerCursor->setUseIdentifierKeys(false);
    }

    public function testGetSingleResultShouldAlwaysReturnTheFirstResult()
    {
        $results = [
            ['_id' => 1, 'x' => 'foo'],
            ['_id' => 2, 'x' => 'bar'],
        ];

        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($results));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertFalse($eagerCursor->isInitialized());
        $this->assertEquals($results[0], $eagerCursor->getSingleResult());
        $this->assertTrue($eagerCursor->isInitialized());

        $eagerCursor->next();
        $this->assertEquals($results[0], $eagerCursor->getSingleResult());
    }

    public function testGetSingleResultShouldReturnNullForNoResults()
    {
        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue([]));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertNull($eagerCursor->getSingleResult());
    }

    public function testToArray()
    {
        $results = [
            ['_id' => 1, 'x' => 'foo'],
            ['_id' => 2, 'x' => 'bar'],
        ];

        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($results));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertFalse($eagerCursor->isInitialized());
        $this->assertEquals($results, $eagerCursor->toArray());
        $this->assertTrue($eagerCursor->isInitialized());
    }

    public function testIterationMethods()
    {
        $results = [
            ['_id' => 1, 'x' => 'foo'],
            ['_id' => 2, 'x' => 'bar'],
        ];

        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($results));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertFalse($eagerCursor->isInitialized());

        foreach (range(1,2) as $_) {
            $this->assertEquals(0, $eagerCursor->key());
            $this->assertTrue($eagerCursor->isInitialized());
            $this->assertEquals($results[0], $eagerCursor->current());
            $eagerCursor->next();
            $this->assertEquals(1, $eagerCursor->key());
            $this->assertEquals($results[1], $eagerCursor->current());
            $eagerCursor->next();
            $this->assertFalse($eagerCursor->valid());

            $eagerCursor->rewind();
        }
    }

    public function testGetNextHasNext()
    {
        $results = [
            ['_id' => 1, 'x' => 'foo'],
            ['_id' => 2, 'x' => 'bar'],
        ];

        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($results));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertTrue($eagerCursor->hasNext());
        $this->assertEquals($results[0], $eagerCursor->getNext());

        $this->assertTrue($eagerCursor->hasNext());
        $this->assertEquals($results[0], $eagerCursor->current(), 'hasNext does not advance internal cursor');
        $this->assertEquals($results[1], $eagerCursor->getNext());

        $this->assertFalse($eagerCursor->hasNext());
        $this->assertNull($eagerCursor->getNext());

        $eagerCursor->rewind();
        $this->assertTrue($eagerCursor->hasNext());
        $this->assertEquals($results[0], $eagerCursor->getNext());
    }

    public function testLimit()
    {
        $cursor = $this->getMockCursor();

        $limit = 10;
        $cursor->expects($this->once())
            ->method('limit')
            ->with($limit);

        $eagerCursor = new EagerCursor($cursor);
        $result = $eagerCursor->limit($limit);

        $this->assertInstanceOf('\Doctrine\MongoDB\EagerCursor', $result);
    }

    public function testSkip()
    {
        $cursor = $this->getMockCursor();

        $offset = 10;
        $cursor->expects($this->once())
            ->method('skip')
            ->with($offset);

        $eagerCursor = new EagerCursor($cursor);
        $result = $eagerCursor->skip($offset);

        $this->assertInstanceOf('\Doctrine\MongoDB\EagerCursor', $result);
    }

    /**
     * @return \Doctrine\MongoDB\Cursor|\PHPUnit_Framework_MockObject_MockObject
     */
    private function getMockCursor()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\CursorInterface')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
