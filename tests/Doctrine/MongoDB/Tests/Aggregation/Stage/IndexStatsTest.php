<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\IndexStats;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class IndexStatsTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

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
}
