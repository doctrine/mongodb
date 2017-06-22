<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Bucket;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class BucketTest extends TestCase
{
    use AggregationTestCase;

    public function testBucketStage()
    {
        $bucketStage = new Bucket($this->getTestAggregationBuilder());
        $bucketStage
            ->groupBy('$someField')
            ->boundaries(1, 2, 3)
            ->defaultBucket(0)
            ->output()
                ->field('averageValue')
                ->avg('$value');

        $this->assertSame(['$bucket' => [
            'groupBy' => '$someField',
            'boundaries' => [1, 2, 3],
            'default' => 0,
            'output' => ['averageValue' => ['$avg' => '$value']]
        ]], $bucketStage->getExpression());
    }

    public function testBucketFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->bucket()
            ->groupBy('$someField')
            ->boundaries(1, 2, 3)
            ->defaultBucket(0)
            ->output()
            ->field('averageValue')
            ->avg('$value');

        $this->assertSame([['$bucket' => [
            'groupBy' => '$someField',
            'boundaries' => [1, 2, 3],
            'default' => 0,
            'output' => ['averageValue' => ['$avg' => '$value']]
        ]]], $builder->getPipeline());
    }

    public function testBucketSkipsUndefinedProperties()
    {
        $bucketStage = new Bucket($this->getTestAggregationBuilder());
        $bucketStage
            ->groupBy('$someField')
            ->boundaries(1, 2, 3);

        $this->assertSame(['$bucket' => [
            'groupBy' => '$someField',
            'boundaries' => [1, 2, 3],
        ]], $bucketStage->getExpression());
    }
}
