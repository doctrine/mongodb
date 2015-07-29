<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Out;

class OutTest extends \PHPUnit_Framework_TestCase
{
    public function testOutStage()
    {
        $outStage = new Out($this->getTestAggregationBuilder(), 'someCollection');

        $this->assertSame(array('$out' => 'someCollection'), $outStage->getExpression());
    }

    public function testOutFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->out('someCollection');

        $this->assertSame(array(array('$out' => 'someCollection')), $builder->getPipeline());
    }

    public function testSubsequentOutStagesAreOverwritten()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->out('someCollection')
            ->out('otherCollection');

        $this->assertSame(array(array('$out' => 'otherCollection')), $builder->getPipeline());
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
