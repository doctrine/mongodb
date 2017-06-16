<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Lookup;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class LookupTest extends TestCase
{
    use AggregationTestCase;

    public function testLookupStage()
    {
        $lookupStage = new Lookup($this->getTestAggregationBuilder(), 'collection');
        $lookupStage
            ->localField('local.field')
            ->foreignField('foreign.field')
            ->alias('lookedUp');

        $this->assertSame(
            ['$lookup' => ['from' => 'collection', 'localField' => 'local.field', 'foreignField' => 'foreign.field', 'as' => 'lookedUp']],
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

        $this->assertSame([['$lookup' => ['from' => 'collection', 'localField' => 'local.field', 'foreignField' => 'foreign.field', 'as' => 'lookedUp']]], $builder->getPipeline());
    }
}
