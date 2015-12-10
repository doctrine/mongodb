<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\IndexStats;

class IndexStatsTest extends \PHPUnit_Framework_TestCase
{
    public function testIndexStatsStage()
    {
        $indexStatsStage = new IndexStats($this->getTestAggregationBuilder());

        $this->assertEquals(array('$indexStats' => new \stdClass()), $indexStatsStage->getExpression());
    }

    public function testIndexStatsFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->indexStats();

        $this->assertEquals(array(array('$indexStats' => new \stdClass())), $builder->getPipeline());
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
