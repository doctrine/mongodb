<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Limit;

class LimitTest extends \PHPUnit_Framework_TestCase
{
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

    public function testSubsequentLimitStagesAreOverwritten()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->limit(10)
            ->limit(15);

        $this->assertSame(array(array('$limit' => 15)), $builder->getPipeline());
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
