<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Limit;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class LimitTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testLimitStage()
    {
        $limitStage = new Limit($this->getTestAggregationBuilder(), 10);

        $this->assertSame(array('$limit' => 10), $limitStage->getExpression());
    }

    public function testLimitFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->limit(10);

        $this->assertSame(array(array('$limit' => 10)), $builder->getPipeline());
    }
}
