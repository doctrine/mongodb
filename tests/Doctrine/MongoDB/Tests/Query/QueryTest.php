<?php
namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Query;
use Doctrine\MongoDB\Tests\Constraint\ArrayHasValueUnderKey;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    public function testMapReduceOptionsArePassed()
    {
        $collection = $this->getMockCollection();

        $queryArray = array(
            'type' => Query::TYPE_MAP_REDUCE,
            'mapReduce' => array(
                'map'     => '',
                'reduce'  => '',
                'options' => array('limit' => 10),
            ),
            'query' => array()
        );

        $query = new Query(
            $this->getMockDatabase(),
            $collection,
            $queryArray,
            array(),
            ''
        );

        $collection->expects($this->any())
                   ->method('mapReduce')
                   ->with($this->anything(),
                          $this->anything(),
                          $this->anything(),
                          $this->anything(),
                          new ArrayHasValueUnderKey('limit', 10)
                   );

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
