<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Sample;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class SampleTest extends TestCase
{
    use AggregationTestCase;

    public function testSampleStage()
    {
        $sampleStage = new Sample($this->getTestAggregationBuilder(), 10);

        $this->assertSame(['$sample' => ['size' => 10]], $sampleStage->getExpression());
    }

    public function testSampleFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->sample(10);

        $this->assertSame([['$sample' => ['size' => 10]]], $builder->getPipeline());
    }
}
