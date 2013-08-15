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
            'query' => array('type' => 1),
        );

        $collection = $this->getMockCollection();
        $collection->expects($this->once())
            ->method('mapReduce')
            ->with($map, $reduce, 'collection', array('type' => 1), array('jsMode' => true));

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
