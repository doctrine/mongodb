<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Group;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    public function testGroupStage()
    {
        $groupStage = new Group($this->getTestAggregationBuilder());
        $groupStage
            ->field('_id')
            ->expression('$field')
            ->field('count')
            ->sum(1);

        $this->assertSame(array('$group' => array('_id' => '$field', 'count' => array('$sum' => 1))), $groupStage->getExpression());
    }

    public function testGroupFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->group()
            ->field('_id')
            ->expression('$field')
            ->field('count')
            ->sum(1);

        $this->assertSame(array(array('$group' => array('_id' => '$field', 'count' => array('$sum' => 1)))), $builder->getPipeline());
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
