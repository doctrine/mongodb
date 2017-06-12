<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\BucketAuto;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class BucketAutoTest extends TestCase
{
    use AggregationTestCase;

    public function testBucketAutoStage()
    {
        $bucketStage = new BucketAuto($this->getTestAggregationBuilder());
        $bucketStage
            ->groupBy('$someField')
            ->buckets(3)
            ->granularity('R10')
            ->output()
                ->field('averageValue')
                ->avg('$value');

        $this->assertSame(['$bucket' => [
            'groupBy' => '$someField',
            'buckets' => 3,
            'granularity' => 'R10',
            'output' => ['averageValue' => ['$avg' => '$value']]
        ]], $bucketStage->getExpression());
    }

    public function testBucketAutoFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->bucketAuto()
            ->groupBy('$someField')
            ->buckets(3)
            ->granularity('R10')
            ->output()
                ->field('averageValue')
                ->avg('$value');

        $this->assertSame([['$bucket' => [
            'groupBy' => '$someField',
            'buckets' => 3,
            'granularity' => 'R10',
            'output' => ['averageValue' => ['$avg' => '$value']]
        ]]], $builder->getPipeline());
    }

    public function testBucketAutoSkipsUndefinedProperties()
    {
        $bucketStage = new BucketAuto($this->getTestAggregationBuilder());
        $bucketStage
            ->groupBy('$someField')
            ->buckets(3);

        $this->assertSame(['$bucket' => [
            'groupBy' => '$someField',
            'buckets' => 3,
        ]], $bucketStage->getExpression());
    }
}
