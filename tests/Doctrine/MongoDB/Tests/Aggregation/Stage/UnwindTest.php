<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Unwind;

class UnwindTest extends \PHPUnit_Framework_TestCase
{
    public function testUnwindStage()
    {
        $unwindStage = new Unwind($this->getTestAggregationBuilder(), 'fieldName');

        $this->assertSame(array('$unwind' => 'fieldName'), $unwindStage->getExpression());
    }

    public function testUnwindFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->unwind('fieldName');

        $this->assertSame(array(array('$unwind' => 'fieldName')), $builder->getPipeline());
    }

    public function testSubsequentUnwindStagesArePreserved()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->unwind('fieldName')
            ->unwind('otherField');

        $this->assertSame(array(array('$unwind' => 'fieldName'), array('$unwind' => 'otherField')), $builder->getPipeline());
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
