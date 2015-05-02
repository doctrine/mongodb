<?php

namespace Doctrine\MongoDB\Tests\Aggregation;

use Doctrine\MongoDB\Aggregation\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPipeline()
    {
        $point = array('type' => 'Point', 'coordinates' => array(0, 0));

        $expectedPipeline = array(
            array(
                '$geoNear' => array(
                    'near' => $point,
                    'spherical' => true,
                    'distanceField' => 'distance',
                    'query' => array(
                        'hasCoordinates' => array('$exists' => true),
                        'username' => 'foo',
                    ),
                    'num' => 10
                )
            ),
            array('$match' =>
                array(
                    'group' => array('$in' => array('a', 'b'))
                )
            ),
            array('$unwind' => 'a'),
            array('$unwind' => 'b'),
            array('$redact' => array()),
            array('$project' => array()),
            array('$group' => array()),
            array('$sort' => array('a' => 0, 'b' => 1, 'c' => -1)),
            array('$limit' => 5),
            array('$skip' => 2),
            array('$out' => 'collectionName')
        );

        $builder = $this->getTestAggregationBuilder();
        $builder
            ->geoNear($point)
                ->distanceField('distance')
                ->limit(10) // Limit is applied on $geoNear
                ->field('hasCoordinates')
                ->exists(true)
                ->field('username')
                ->equals('foo')
            ->match()
                ->field('group')
                ->in(array('a', 'b'))
            ->unwind('a')
            ->unwind('b')
            ->redact() // To be implemented
            ->project() // To be implemented
            ->group() // To be implemented
            ->sort('a')
            ->sort(array('b' => 'asc', 'c' => 'desc')) // Multiple subsequent sorts are combined into a single stage
            ->limit(5)
            ->skip(2)
            ->out('collectionName');

        $this->assertEquals($expectedPipeline, $builder->getPipeline());
    }

    private function getTestAggregationBuilder()
    {
        return new Builder($this->getMockCollection());
    }

    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
