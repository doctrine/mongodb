<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Limit;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class LimitTest extends TestCase
{
    use AggregationTestCase;

    public function testLimitStage()
    {
        $limitStage = new Limit($this->getTestAggregationBuilder(), 10);

        $this->assertSame(['$limit' => 10], $limitStage->getExpression());
    }

    public function testLimitFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->limit(10);

        $this->assertSame([['$limit' => 10]], $builder->getPipeline());
    }
}
