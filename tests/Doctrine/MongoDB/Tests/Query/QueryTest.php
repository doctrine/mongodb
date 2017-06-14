<?php
namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Query;
use Doctrine\MongoDB\Tests\TestCase;

class QueryTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorShouldThrowExceptionForInvalidType()
    {
        new Query($this->getMockCollection(), ['type' => -1], []);
    }

    /**
     * @dataProvider provideQueryTypesThatDoNotReturnAnIterator
     * @expectedException BadMethodCallException
     */
    public function testGetIteratorShouldThrowExceptionWithoutExecutingForTypesThatDoNotReturnAnIterator($type, $method)
    {
        $collection = $this->getMockCollection();
        $collection->expects($this->never())->method($method);

        $query = new Query($collection, ['type' => $type], []);

        $query->getIterator();
    }

    public function provideQueryTypesThatDoNotReturnAnIterator()
    {
        return [
            [Query::TYPE_FIND_AND_UPDATE, 'findAndUpdate'],
            [Query::TYPE_FIND_AND_REMOVE, 'findAndRemove'],
            [Query::TYPE_INSERT, 'insert'],
            [Query::TYPE_UPDATE, 'update'],
            [Query::TYPE_REMOVE, 'remove'],
            [Query::TYPE_COUNT, 'count'],
        ];
    }

    /**
     * @dataProvider provideQueryTypesThatDoReturnAnIterator
     * @expectedException UnexpectedValueException
     */
    public function testGetIteratorShouldThrowExceptionAfterExecutingForTypesThatShouldReturnAnIteratorButDoNot($type, $method)
    {
        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method($method)
            ->will($this->returnValue(null));

        // Create a query array with any fields that may be expected to exist
        $queryArray = [
            'type' => $type,
            'query' => [],
            'group' => ['keys' => [], 'initial' => [], 'reduce' => '', 'options' => []],
            'mapReduce' => ['map' => '', 'reduce' => '', 'out' => '', 'options' => []],
            'geoNear' => ['near' => [], 'options' => []],
            'distinct' => 0,
        ];

        $query = new Query($collection, $queryArray, []);

        $query->getIterator();
    }

    public function provideQueryTypesThatDoReturnAnIterator()
    {
        return [
            // Skip Query::TYPE_FIND, since prepareCursor() would error first
            [Query::TYPE_GROUP, 'group'],
            [Query::TYPE_MAP_REDUCE, 'mapReduce'],
            [Query::TYPE_DISTINCT, 'distinct'],
            [Query::TYPE_GEO_NEAR, 'near'],
        ];
    }

    public function testFindAndModifyOptionsAreRenamed()
    {
        $queryArray = [
            'type' => Query::TYPE_FIND_AND_REMOVE,
            'query' => ['type' => 1],
            'select' => ['_id' => 1],
        ];

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('findAndRemove')
            ->with(['type' => 1], ['fields' => ['_id' => 1]]);

        $query = new Query($collection, $queryArray, []);
        $query->execute();
    }

    public function testGroup()
    {
        $keys = ['a' => 1];
        $initial = ['count' => 0, 'sum' => 0];
        $reduce = 'function(obj, prev) { prev.count++; prev.sum += obj.a; }';
        $finalize = 'function(obj) { if (obj.count) { obj.avg = obj.sum / obj.count; } else { obj.avg = 0; } }';

        $queryArray = [
            'type' => Query::TYPE_GROUP,
            'group' => [
                'keys' => $keys,
                'initial' => $initial,
                'reduce' => $reduce,
                'options' => ['finalize' => $finalize],
            ],
            'query' => ['type' => 1],
        ];

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('group')
            ->with($keys, $initial, $reduce, ['finalize' => $finalize, 'cond' => ['type' => 1]]);

        $query = new Query($collection, $queryArray, []);
        $query->execute();
    }

    public function testMapReduceOptionsArePassed()
    {
        $map = 'function() { emit(this.a, 1); }';
        $reduce = 'function(key, values) { return Array.sum(values); }';

        $queryArray = [
            'type' => Query::TYPE_MAP_REDUCE,
            'mapReduce' => [
                'map' => $map,
                'reduce' => $reduce,
                'out' => 'collection',
                'options' => ['jsMode' => true],
            ],
            'limit' => 10,
            'query' => ['type' => 1],
        ];

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('mapReduce')
            ->with($map, $reduce, 'collection', ['type' => 1], ['limit' => 10, 'jsMode' => true]);

        $query = new Query($collection, $queryArray, []);
        $query->execute();
    }

    public function testGeoNearOptionsArePassed()
    {
        $queryArray = [
            'type' => Query::TYPE_GEO_NEAR,
            'geoNear' => [
                'near' => [1, 1],
                'options' => ['spherical' => true],
            ],
            'limit' => 10,
            'query' => ['type' => 1],
        ];

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('near')
            ->with([1, 1], ['type' => 1], ['num' => 10, 'spherical' => true]);

        $query = new Query($collection, $queryArray, []);
        $query->execute();
    }

    public function testWithReadPreference()
    {
        $collection = $this->getMockCollection();

        $collection->expects($this->at(0))
            ->method('getReadPreference')
            ->will($this->returnValue(['type' => 'primary']));

        $collection->expects($this->at(1))
            ->method('setReadPreference')
            ->with('secondary', [['dc' => 'east']]);

        $collection->expects($this->at(2))
            ->method('count')
            ->with(['foo' => 'bar'])
            ->will($this->returnValue(100));

        $collection->expects($this->at(3))
            ->method('setReadPreference')
            ->with('primary');

        $queryArray = [
            'type' => Query::TYPE_COUNT,
            'query' => ['foo' => 'bar'],
            'readPreference' => 'secondary',
            'readPreferenceTags' => [['dc' => 'east']],
        ];

        $query = new Query($collection, $queryArray, []);

        $this->assertEquals(100, $query->execute());
    }

    public function testWithReadPreferenceRestoresReadPreferenceBeforePropagatingException()
    {
        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('count');

        $collection = $this->getMockCollection();

        $collection->expects($this->at(0))
            ->method('getReadPreference')
            ->will($this->returnValue(['type' => 'primary']));

        $collection->expects($this->at(1))
            ->method('setReadPreference')
            ->with('secondary', [['dc' => 'east']]);

        $collection->expects($this->at(2))
            ->method('count')
            ->with(['foo' => 'bar'])
            ->will($this->throwException(new \RuntimeException('count')));

        $collection->expects($this->at(3))
            ->method('setReadPreference')
            ->with('primary');

        $queryArray = [
            'type' => Query::TYPE_COUNT,
            'query' => ['foo' => 'bar'],
            'readPreference' => 'secondary',
            'readPreferenceTags' => [['dc' => 'east']],
        ];

        $query = new Query($collection, $queryArray, []);

        $query->execute();
    }

    public function testCountWithOptions()
    {
        $collection = $this->getMockCollection();

        $collection->expects($this->at(0))
            ->method('count')
            ->with(['foo' => 'bar'], ['skip' => 5, 'maxTimeMS' => 10])
            ->will($this->returnValue(100));

        $queryArray = [
            'type' => Query::TYPE_COUNT,
            'query' => ['foo' => 'bar'],
            'skip' => 5,
            'maxTimeMS' => 10,
        ];

        $query = new Query($collection, $queryArray, []);

        $this->assertSame(100, $query->execute());
    }

    public function testEagerCursorPreparation()
    {
        $cursor = $this->getMockCursor();
        $collection = $this->getMockCollection();

        $collection->expects($this->once())
            ->method('find')
            ->with(['foo' => 'bar'])
            ->will($this->returnValue($cursor));

        $queryArray = [
            'type' => Query::TYPE_FIND,
            'query' => ['foo' => 'bar'],
            'eagerCursor' => true,
        ];

        $query = new Query($collection, $queryArray, []);

        $eagerCursor = $query->execute();

        $this->assertInstanceOf('Doctrine\MongoDB\EagerCursor', $eagerCursor);
        $this->assertSame($cursor, $eagerCursor->getCursor());
    }

    public function testUseIdentifierKeys()
    {
        $cursor = $this->getMockCursor();
        $collection = $this->getMockCollection();

        $collection->expects($this->once())
            ->method('find')
            ->with(['foo' => 'bar'])
            ->will($this->returnValue($cursor));

        $cursor->expects($this->once())
            ->method('setUseIdentifierKeys')
            ->with(false)
            ->will($this->returnValue($cursor));

        $queryArray = [
            'type' => Query::TYPE_FIND,
            'query' => ['foo' => 'bar'],
            'useIdentifierKeys' => false,
        ];

        $query = new Query($collection, $queryArray, []);

        $this->assertSame($cursor, $query->execute());
    }

    public function testSpecifyMaxTimeMSOnCursor()
    {
        $cursor = $this->getMockCursor();
        $collection = $this->getMockCollection();

        $collection->expects($this->once())
            ->method('find')
            ->with($this->equalTo(['foo' => 'bar']))
            ->will($this->returnValue($cursor));

        $cursor->expects($this->once())
            ->method('maxTimeMS')
            ->with($this->equalTo(30000))
            ->will($this->returnValue($cursor));

        $queryArray = [
            'type' => Query::TYPE_FIND,
            'query' => ['foo' => 'bar'],
            'maxTimeMS' => 30000
        ];

        $query = new Query($collection, $queryArray, []);

        $this->assertSame($cursor, $query->execute());
    }

    /**
     * @return \Doctrine\MongoDB\Collection
     */
    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
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
