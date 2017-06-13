<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\SortByCount;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class SortByCountTest extends TestCase
{
    use AggregationTestCase;

    public function testSortByCountStage()
    {
        $sortByCountStage = new SortByCount($this->getTestAggregationBuilder(), '$expression');

        $this->assertSame(['$sortByCount' => '$expression'], $sortByCountStage->getExpression());
    }

    public function testSortByCountFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->sortByCount('$fieldName');

        $this->assertSame([['$sortByCount' => '$fieldName']], $builder->getPipeline());
    }
}
