<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\GraphLookup;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class GraphLookupTest extends TestCase
{
    use AggregationTestCase;

    public function testGraphLookupStage()
    {
        $graphLookupStage = new GraphLookup($this->getTestAggregationBuilder(), 'employees');
        $graphLookupStage
            ->startWith('$reportsTo')
            ->connectFromField('reportsTo')
            ->connectToField('name')
            ->alias('reportingHierarchy');

        $this->assertSame(
            ['$graphLookup' => [
                'from' => 'employees',
                'startWith' => '$reportsTo',
                'connectFromField' => 'reportsTo',
                'connectToField' => 'name',
                'as' => 'reportingHierarchy',
                'restrictSearchWithMatch' => [],
            ]],
            $graphLookupStage->getExpression()
        );
    }

    public function testGraphLookupFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->graphLookup('employees')
            ->startWith('$reportsTo')
            ->connectFromField('reportsTo')
            ->connectToField('name')
            ->alias('reportingHierarchy');

        $this->assertSame(
            [['$graphLookup' => [
                'from' => 'employees',
                'startWith' => '$reportsTo',
                'connectFromField' => 'reportsTo',
                'connectToField' => 'name',
                'as' => 'reportingHierarchy',
                'restrictSearchWithMatch' => [],
            ]]],
            $builder->getPipeline()
        );
    }

    public function testGraphLookupWithMatch()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->graphLookup('employees')
            ->startWith('$reportsTo')
            ->restrictSearchWithMatch()
                ->field('hobbies')
                ->equals('golf')
            ->connectFromField('reportsTo')
            ->connectToField('name')
            ->alias('reportingHierarchy')
            ->maxDepth(1)
            ->depthField('depth');

        $this->assertSame(
            [['$graphLookup' => [
                'from' => 'employees',
                'startWith' => '$reportsTo',
                'connectFromField' => 'reportsTo',
                'connectToField' => 'name',
                'as' => 'reportingHierarchy',
                'restrictSearchWithMatch' => ['hobbies' => 'golf'],
                'maxDepth' => 1,
                'depthField' => 'depth',
            ]]],
            $builder->getPipeline()
        );
    }
}
