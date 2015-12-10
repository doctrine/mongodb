<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Lookup;

class LookupTest extends \PHPUnit_Framework_TestCase
{
    public function testLookupStage()
    {
        $lookupStage = new Lookup($this->getTestAggregationBuilder(), 'collection');
        $lookupStage
            ->localField('local.field')
            ->foreignField('foreign.field')
            ->alias('lookedUp');

        $this->assertSame(
            array('$lookup' => array('from' => 'collection', 'localField' => 'local.field', 'foreignField' => 'foreign.field', 'as' => 'lookedUp')),
            $lookupStage->getExpression()
        );
    }

    public function testLookupFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->lookup('collection')
            ->localField('local.field')
            ->foreignField('foreign.field')
            ->alias('lookedUp');

        $this->assertSame(array(array('$lookup' => array('from' => 'collection', 'localField' => 'local.field', 'foreignField' => 'foreign.field', 'as' => 'lookedUp'))), $builder->getPipeline());
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
