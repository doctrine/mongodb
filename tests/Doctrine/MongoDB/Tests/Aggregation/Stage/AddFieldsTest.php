<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\AddFields;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class AddFieldsTest extends TestCase
{
    use AggregationTestCase;

    public function testAddFieldsStage()
    {
        $addFieldsStage = new AddFields($this->getTestAggregationBuilder());
        $addFieldsStage
            ->field('product')
            ->multiply('$field', 5);

        $this->assertSame(['$addFields' => ['product' => ['$multiply' => ['$field', 5]]]], $addFieldsStage->getExpression());
    }

    public function testProjectFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->addFields()
            ->field('product')
            ->multiply('$field', 5);

        $this->assertSame([['$addFields' => ['product' => ['$multiply' => ['$field', 5]]]]], $builder->getPipeline());
    }
}
