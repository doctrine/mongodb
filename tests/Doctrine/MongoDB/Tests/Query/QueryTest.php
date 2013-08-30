<?php
namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Query;
use Doctrine\MongoDB\Tests\Constraint\ArrayHasKeyAndValue;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConstructorShouldThrowExceptionForInvalidType()
    {
        new Query($this->getMockDatabase(), $this->getMockCollection(), array('type' => -1), array(), '$');
    }

    /**
     * @dataProvider provideQueryTypesThatDoNotReturnAnIterator
     * @expectedException BadMethodCallException
     */
    public function testGetIteratorShouldThrowExceptionWithoutExecutingForTypesThatDoNotReturnAnIterator($type, $method)
    {
        $collection = $this->getMockCollection();
        $collection->expects($this->never())->method($method);

        $query = new Query($this->getMockDatabase(), $collection, array('type' => $type), array(), '$');

        $query->getIterator();
    }

    public function provideQueryTypesThatDoNotReturnAnIterator()
    {
        return array(
            array(Query::TYPE_FIND_AND_UPDATE, 'findAndUpdate'),
            array(Query::TYPE_FIND_AND_REMOVE, 'findAndRemove'),
            array(Query::TYPE_INSERT, 'insert'),
            array(Query::TYPE_UPDATE, 'update'),
            array(Query::TYPE_REMOVE, 'remove'),
            array(Query::TYPE_COUNT, 'count'),
        );
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
        $queryArray = array(
            'type' => $type,
            'query' => array(),
            'group' => array('keys' => array(), 'initial' => array(), 'reduce' => '', 'options' => array()),
            'mapReduce' => array('map' => '', 'reduce' => '', 'out' => '', 'options' => array()),
            'geoNear' => array('near' => array(), 'options' => array()),
            'distinct' => 0,
        );

        $query = new Query($this->getMockDatabase(), $collection, $queryArray, array(), '$');

        $query->getIterator();
    }

    public function provideQueryTypesThatDoReturnAnIterator()
    {
        return array(
            // Skip Query::TYPE_FIND, since prepareCursor() would error first
            array(Query::TYPE_GROUP, 'group'),
            array(Query::TYPE_MAP_REDUCE, 'mapReduce'),
            array(Query::TYPE_DISTINCT, 'distinct'),
            array(Query::TYPE_GEO_NEAR, 'near'),
        );
    }

    public function testGroup()
    {
        $keys = array('a' => 1);
        $initial = array('count' => 0, 'sum' => 0);
        $reduce = 'function(obj, prev) { prev.count++; prev.sum += obj.a; }';
        $finalize = 'function(obj) { if (obj.count) { obj.avg = obj.sum / obj.count; } else { obj.avg = 0; } }';

        $queryArray = array(
            'type' => Query::TYPE_GROUP,
            'group' => array(
                'keys' => $keys,
                'initial' => $initial,
                'reduce' => $reduce,
                'options' => array('finalize' => $finalize),
            ),
            'query' => array('type' => 1),
        );

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('group')
            ->with($keys, $initial, $reduce, array('finalize' => $finalize, 'cond' => array('type' => 1)));

        $query = new Query($this->getMockDatabase(), $collection, $queryArray, array(), '$');
        $query->execute();
    }

    public function testMapReduceOptionsArePassed()
    {
        $map = 'function() { emit(this.a, 1); }';
        $reduce = 'function(key, values) { return Array.sum(values); }';

        $queryArray = array(
            'type' => Query::TYPE_MAP_REDUCE,
            'mapReduce' => array(
                'map' => $map,
                'reduce' => $reduce,
                'out' => 'collection',
                'options' => array('jsMode' => true),
            ),
            'limit' => 10,
            'query' => array('type' => 1),
        );

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('mapReduce')
            ->with($map, $reduce, 'collection', array('type' => 1), array('limit' => 10, 'jsMode' => true));

        $query = new Query($this->getMockDatabase(), $collection, $queryArray, array(), '$');
        $query->execute();
    }

    public function testGeoNearOptionsArePassed()
    {
        $queryArray = array(
            'type' => Query::TYPE_GEO_NEAR,
            'geoNear' => array(
                'near' => array(1, 1),
                'options' => array('spherical' => true),
            ),
            'limit' => 10,
            'query' => array('type' => 1),
        );

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('near')
            ->with(array(1, 1), array('type' => 1), array('num' => 10, 'spherical' => true));

        $query = new Query($this->getMockDatabase(), $collection, $queryArray, array(), '$');
        $query->execute();
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
     * @return \Doctrine\MongoDB\Database
     */
    private function getMockDatabase()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
