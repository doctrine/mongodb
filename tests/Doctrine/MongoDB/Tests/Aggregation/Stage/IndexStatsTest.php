<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\IndexStats;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class IndexStatsTest extends TestCase
{
    use AggregationTestCase;

    public function testIndexStatsStage()
    {
        $indexStatsStage = new IndexStats($this->getTestAggregationBuilder());

        $this->assertEquals(['$indexStats' => new \stdClass()], $indexStatsStage->getExpression());
    }

    public function testIndexStatsFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->indexStats();

        $this->assertEquals([['$indexStats' => new \stdClass()]], $builder->getPipeline());
    }
}
