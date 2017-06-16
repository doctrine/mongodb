<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Unwind;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class UnwindTest extends TestCase
{
    use AggregationTestCase;

    public function testUnwindStage()
    {
        $unwindStage = new Unwind($this->getTestAggregationBuilder(), 'fieldName');

        $this->assertSame(['$unwind' => 'fieldName'], $unwindStage->getExpression());
    }

    public function testUnwindStageWithNewFields()
    {
        $unwindStage = new Unwind($this->getTestAggregationBuilder(), 'fieldName');
        $unwindStage
            ->preserveNullAndEmptyArrays()
            ->includeArrayIndex('index');

        $this->assertSame(['$unwind' => ['path' => 'fieldName', 'includeArrayIndex' => 'index', 'preserveNullAndEmptyArrays' => true]], $unwindStage->getExpression());
    }

    public function testUnwindFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->unwind('fieldName');

        $this->assertSame([['$unwind' => 'fieldName']], $builder->getPipeline());
    }

    public function testSubsequentUnwindStagesArePreserved()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->unwind('fieldName')
            ->unwind('otherField');

        $this->assertSame([['$unwind' => 'fieldName'], ['$unwind' => 'otherField']], $builder->getPipeline());
    }
}
