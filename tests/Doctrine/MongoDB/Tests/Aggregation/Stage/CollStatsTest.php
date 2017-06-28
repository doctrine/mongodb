<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\CollStats;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class CollStatsTest extends TestCase
{
    use AggregationTestCase;

    public function testCollStatsStage()
    {
        $collStatsStage = new CollStats($this->getTestAggregationBuilder());

        $this->assertSame(['$collStats' => []], $collStatsStage->getExpression());
    }

    public function testCollStatsStageWithLatencyStats()
    {
        $collStatsStage = new CollStats($this->getTestAggregationBuilder());
        $collStatsStage->showLatencyStats();

        $this->assertSame(['$collStats' => ['latencyStats' => ['histograms' => false]]], $collStatsStage->getExpression());
    }

    public function testCollStatsStageWithLatencyStatsHistograms()
    {
        $collStatsStage = new CollStats($this->getTestAggregationBuilder());
        $collStatsStage->showLatencyStats(true);

        $this->assertSame(['$collStats' => ['latencyStats' => ['histograms' => true]]], $collStatsStage->getExpression());
    }

    public function testCollStatsStageWithStorageStats()
    {
        $collStatsStage = new CollStats($this->getTestAggregationBuilder());
        $collStatsStage->showStorageStats();

        $this->assertSame(['$collStats' => ['storageStats' => []]], $collStatsStage->getExpression());
    }

    public function testCollStatsFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->collStats()
            ->showLatencyStats(true)
            ->showStorageStats();

        $this->assertSame([[
            '$collStats' => [
                'latencyStats' => ['histograms' => true],
                'storageStats' => []
            ]
        ]], $builder->getPipeline());
    }
}
