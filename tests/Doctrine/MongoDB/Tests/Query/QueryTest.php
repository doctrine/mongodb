<?php
namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Query;
use Doctrine\MongoDB\Tests\Constraint\ArrayHasValueUnderKey;

class QueryTest extends \PHPUnit_Framework_TestCase
{
    const MAP_REDUCE_OPTION_KEY   = 'limit';
    const MAP_REDUCE_OPTION_VALUE = 10;

    public function testMapReduceOptionsArePassed()
    {
        $collection = $this->getMockCollection();

        $mapReduceOptions = array(
            self::MAP_REDUCE_OPTION_KEY => self::MAP_REDUCE_OPTION_VALUE
        );

        $queryArray = array(
            'type' => Query::TYPE_MAP_REDUCE,
            'mapReduce' => array(
                'map'     => '',
                'reduce'  => '',
                'options' => $mapReduceOptions
            ),
            'query'  => array()
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
                          $this->logicalAnd(
                              $this->arrayHasKey(self::MAP_REDUCE_OPTION_KEY),
                              new ArrayHasValueUnderKey(
                                  self::MAP_REDUCE_OPTION_KEY,
                                  self::MAP_REDUCE_OPTION_VALUE
                              )
                          )
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
