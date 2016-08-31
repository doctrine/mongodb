<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Out;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class OutTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testOutStage()
    {
        $outStage = new Out($this->getTestAggregationBuilder(), 'someCollection');

        $this->assertSame(['$out' => 'someCollection'], $outStage->getExpression());
    }

    public function testOutFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->out('someCollection');

        $this->assertSame([['$out' => 'someCollection']], $builder->getPipeline());
    }

    public function testSubsequentOutStagesAreOverwritten()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->out('someCollection')
            ->out('otherCollection');

        $this->assertSame([['$out' => 'otherCollection']], $builder->getPipeline());
    }
}
