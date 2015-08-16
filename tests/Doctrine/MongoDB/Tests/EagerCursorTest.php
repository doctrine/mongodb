<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\EagerCursor;

class EagerCursorTest extends BaseTest
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
            ->with(true) // Constructor $useKeys option defaults to true
            ->will($this->returnValue(array()));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertFalse($eagerCursor->isInitialized());
        $eagerCursor->initialize();
        $this->assertTrue($eagerCursor->isInitialized());
        $eagerCursor->initialize();
        $this->assertTrue($eagerCursor->isInitialized());
    }

    /**
     * @dataProvider provideUseKeys
     */
    public function testInitializationForwardsUseKeysOption($useKeys)
    {
        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->with($useKeys);

        $eagerCursor = new EagerCursor($cursor, $useKeys);
        $eagerCursor->initialize();
    }

    public function provideUseKeys()
    {
        return array(
            array(true),
            array(false),
        );
    }

    public function testCount()
    {
        $results = array(
            array('_id' => 1, 'x' => 'foo'),
            array('_id' => 2, 'x' => 'bar'),
        );

        $cursor = $this->getMockCursor();

        $cursor->expects($this->once())
            ->method('toArray')
            ->will($this->returnValue($results));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertFalse($eagerCursor->isInitialized());
        $this->assertEquals(2, count($eagerCursor));
        $this->assertTrue($eagerCursor->isInitialized());
    }

    public function testGetSingleResultShouldAlwaysReturnTheFirstResult()
    {
        $results = array(
            array('_id' => 1, 'x' => 'foo'),
            array('_id' => 2, 'x' => 'bar'),
        );

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
            ->will($this->returnValue(array()));

        $eagerCursor = new EagerCursor($cursor);

        $this->assertNull($eagerCursor->getSingleResult());
    }

    public function testToArray()
    {
        $results = array(
            array('_id' => 1, 'x' => 'foo'),
            array('_id' => 2, 'x' => 'bar'),
        );

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
        $results = array(
            array('_id' => 1, 'x' => 'foo'),
            array('_id' => 2, 'x' => 'bar'),
        );

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
        $results = array(
            array('_id' => 1, 'x' => 'foo'),
            array('_id' => 2, 'x' => 'bar'),
        );

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

    /**
     * @return \Doctrine\MongoDB\Cursor
     */
    private function getMockCursor()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Cursor')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
