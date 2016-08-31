<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Sample;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class SampleTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testSampleStage()
    {
        $sampleStage = new Sample($this->getTestAggregationBuilder(), 10);

        $this->assertSame(['$sample' => 10], $sampleStage->getExpression());
    }

    public function testSampleFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->sample(10);

        $this->assertSame([['$sample' => 10]], $builder->getPipeline());
    }
}
