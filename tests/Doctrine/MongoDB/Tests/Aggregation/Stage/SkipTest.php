<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Skip;

class SkipTest extends \PHPUnit_Framework_TestCase
{
    public function testSkipStage()
    {
        $skipStage = new Skip($this->getTestAggregationBuilder(), 10);

        $this->assertSame(array('$skip' => 10), $skipStage->getExpression());
    }

    public function testSkipFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->skip(10);

        $this->assertSame(array(array('$skip' => 10)), $builder->getPipeline());
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
