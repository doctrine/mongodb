<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\ArrayIterator;

class ArrayIteratorTest extends \PHPUnit_Framework_TestCase
{
    public function testArrayAccess()
    {
        $arrayIterator = new ArrayIterator();

        $this->assertInstanceOf('ArrayAccess', $arrayIterator);
        $this->assertFalse($arrayIterator->offsetExists(0));
        $this->assertNull($arrayIterator['undefinedOffset']);

        $arrayIterator[0] = null;

        $this->assertFalse($arrayIterator->offsetExists(0));
        $this->assertFalse(isset($arrayIterator[0]));
        $this->assertSame(null, $arrayIterator[0]);

        $arrayIterator[] = true;

        $this->assertTrue($arrayIterator->offsetExists(1));
        $this->assertTrue(isset($arrayIterator[1]));
        $this->assertSame(true, $arrayIterator[1]);

        unset($arrayIterator[0]);

        $this->assertFalse($arrayIterator->offsetExists(0));
        $this->assertTrue(empty($arrayIterator[0]));
    }

    public function testCount()
    {
        $this->assertInstanceOf('Countable', new ArrayIterator());

        $this->assertCount(0, new ArrayIterator());
        $this->assertCount(1, new ArrayIterator(array(1)));
        $this->assertCount(2, new ArrayIterator(array(1, 2)));
    }

    public function testGetSingleResult()
    {
        $arrayIterator = new ArrayIterator(array(1, 2, 3));

        $this->assertSame(1, $arrayIterator->getSingleResult());

        $arrayIterator->next();
        $arrayIterator->next();
        $arrayIterator->next();

        $this->assertSame(1, $arrayIterator->getSingleResult());
    }

    public function testGetSingleResultWithEmptyArray()
    {
        $arrayIterator = new ArrayIterator();

        $this->assertSame(null, $arrayIterator->getSingleResult());
    }

    public function testIteration()
    {
        $arrayIterator = new ArrayIterator(array(1, 2, 3));

        $this->assertInstanceOf('Iterator', $arrayIterator);

        $this->assertSame(0, $arrayIterator->key());
        $this->assertSame(1, $arrayIterator->current());
        $this->assertTrue($arrayIterator->valid());

        $arrayIterator->next();

        $this->assertSame(1, $arrayIterator->key());
        $this->assertSame(2, $arrayIterator->current());
        $this->assertTrue($arrayIterator->valid());

        $arrayIterator->next();

        $this->assertSame(2, $arrayIterator->key());
        $this->assertSame(3, $arrayIterator->current());
        $this->assertTrue($arrayIterator->valid());

        $arrayIterator->next();

        $this->assertSame(null, $arrayIterator->key());
        $this->assertSame(false, $arrayIterator->current());
        $this->assertFalse($arrayIterator->valid());

        $arrayIterator->rewind();

        $this->assertSame(0, $arrayIterator->key());
        $this->assertSame(1, $arrayIterator->current());
        $this->assertTrue($arrayIterator->valid());
    }

    public function testToArray()
    {
        $arrayIterator = new ArrayIterator(array(1, 2, 3));

        $this->assertSame(array(1, 2, 3), $arrayIterator->toArray());
    }

    public function testFirstAndLast()
    {
        $arrayIterator = new ArrayIterator(array(1, 2, 3));

        $this->assertSame(1, $arrayIterator->first());
        $this->assertSame(3, $arrayIterator->last());
    }
}
