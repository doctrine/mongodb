<?php
namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Query;
use Doctrine\MongoDB\Tests\Constraint\ArrayHasKeyAndValue;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testMapReduceOptionsArePassed()
    {
        $queryArray = array(
            'type' => Query::TYPE_MAP_REDUCE,
            'mapReduce' => array(
                'map' => 'map',
                'reduce' => 'reduce',
                'out' => 'collection',
                'options' => array('limit' => 10, 'jsMode' => true),
            ),
            'query' => array('type' => 1),
        );

        $collection = $this->getMockCollection();
        $collection->expects($this->any())
            ->method('mapReduce')
            ->with('map',
                  'reduce',
                  'collection',
                  array('type' => 1),
                  $this->logicalAnd(
                      new ArrayHasKeyAndValue('limit', 10),
                      new ArrayHasKeyAndValue('jsMode', true)
                  )
            );

        $query = new Query($this->getMockDatabase(), $collection, $queryArray, array(), '$');
        $query->execute();
    }

    public function testGeoNearOptionsArePassed()
    {
        $queryArray = array(
            'type' => Query::TYPE_GEO_NEAR,
            'geoNear' => array(
                'near' => array(50, 50),
                'distanceMultiplier' => 2.5,
                'maxDistance' => 5,
                'spherical' => true,
            ),
            'limit' => 10,
            'query' => array('altitude' => array('$gt' => 1)),
        );

        $collection = $this->getMockCollection();
        $collection->expects($this->any())
            ->method('geoNear')
            ->with(array(50, 50),
                  array('altitude' => array('$gt' => 1)),
                  $this->logicalAnd(
                      new ArrayHasKeyAndValue('distanceMultiplier', 2.5),
                      new ArrayHasKeyAndValue('maxDistance', 5),
                      new ArrayHasKeyAndValue('spherical', true),
                      new ArrayHasKeyAndValue('num', 10)
                  )
            );

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
