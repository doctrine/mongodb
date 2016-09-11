<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Skip;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class SkipTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testSkipStage()
    {
        $skipStage = new Skip($this->getTestAggregationBuilder(), 10);

        $this->assertSame(['$skip' => 10], $skipStage->getExpression());
    }

    public function testSkipFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->skip(10);

        $this->assertSame([['$skip' => 10]], $builder->getPipeline());
    }
}
