<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\ArrayIterator;

class ArrayIteratorTest extends TestCase
{
    public function testArrayAccess()
    {
        $arrayIterator = new ArrayIterator();

        $this->assertInstanceOf('ArrayAccess', $arrayIterator);
        $this->assertFalse($arrayIterator->offsetExists(0));
        $this->assertNull($arrayIterator['undefinedOffset']);

        $arrayIterator[0] = null;

        $this->assertFalse($arrayIterator->offsetExists(0));
        $this->assertArrayNotHasKey(0, $arrayIterator);
        $this->assertNull($arrayIterator[0]);

        $arrayIterator[] = true;

        $this->assertTrue($arrayIterator->offsetExists(1));
        $this->assertArrayHasKey(1, $arrayIterator);
        $this->assertTrue($arrayIterator[1]);

        unset($arrayIterator[0]);

        $this->assertFalse($arrayIterator->offsetExists(0));
        $this->assertEmpty($arrayIterator[0]);
    }

    public function testCount()
    {
        $this->assertInstanceOf('Countable', new ArrayIterator());

        $this->assertCount(0, new ArrayIterator());
        $this->assertCount(1, new ArrayIterator([1]));
        $this->assertCount(2, new ArrayIterator([1, 2]));
    }

    public function testGetSingleResult()
    {
        $arrayIterator = new ArrayIterator([1, 2, 3]);

        $this->assertSame(1, $arrayIterator->getSingleResult());

        $arrayIterator->next();
        $arrayIterator->next();
        $arrayIterator->next();

        $this->assertSame(1, $arrayIterator->getSingleResult());
    }

    public function testGetSingleResultWithEmptyArray()
    {
        $arrayIterator = new ArrayIterator();

        $this->assertNull($arrayIterator->getSingleResult());
    }

    public function testIteration()
    {
        $arrayIterator = new ArrayIterator([1, 2, 3]);

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

        $this->assertNull($arrayIterator->key());
        $this->assertFalse($arrayIterator->current());
        $this->assertFalse($arrayIterator->valid());

        $arrayIterator->rewind();

        $this->assertSame(0, $arrayIterator->key());
        $this->assertSame(1, $arrayIterator->current());
        $this->assertTrue($arrayIterator->valid());
    }

    public function testToArray()
    {
        $arrayIterator = new ArrayIterator([1, 2, 3]);

        $this->assertSame([1, 2, 3], $arrayIterator->toArray());
    }

    public function testFirstAndLast()
    {
        $arrayIterator = new ArrayIterator([1, 2, 3]);

        $this->assertSame(1, $arrayIterator->first());
        $this->assertSame(3, $arrayIterator->last());
    }
}
