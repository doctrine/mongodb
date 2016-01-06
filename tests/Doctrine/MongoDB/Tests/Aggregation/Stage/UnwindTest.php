<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Unwind;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class UnwindTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testUnwindStage()
    {
        $unwindStage = new Unwind($this->getTestAggregationBuilder(), 'fieldName');

        $this->assertSame(array('$unwind' => 'fieldName'), $unwindStage->getExpression());
    }

    public function testUnwindStageWithNewFields()
    {
        $unwindStage = new Unwind($this->getTestAggregationBuilder(), 'fieldName');
        $unwindStage
            ->preserveNullAndEmptyArrays()
            ->includeArrayIndex('index');

        $this->assertSame(array('$unwind' => array('path' => 'fieldName', 'includeArrayIndex' => 'index', 'preserveNullAndEmptyArrays' => true)), $unwindStage->getExpression());
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
}
