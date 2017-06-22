<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Count;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class CountTest extends TestCase
{
    use AggregationTestCase;

    public function testCountStage()
    {
        $countStage = new Count($this->getTestAggregationBuilder(), 'document_count');

        $this->assertSame(['$count' => 'document_count'], $countStage->getExpression());
    }

    public function testCountFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->count('document_count');

        $this->assertSame([['$count' => 'document_count']], $builder->getPipeline());
    }
}
