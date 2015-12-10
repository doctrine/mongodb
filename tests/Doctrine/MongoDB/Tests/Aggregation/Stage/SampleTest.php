<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Sample;

class SampleTest extends \PHPUnit_Framework_TestCase
{
    public function testSampleStage()
    {
        $sampleStage = new Sample($this->getTestAggregationBuilder(), 10);

        $this->assertSame(array('$sample' => 10), $sampleStage->getExpression());
    }

    public function testSampleFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->sample(10);

        $this->assertSame(array(array('$sample' => 10)), $builder->getPipeline());
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
