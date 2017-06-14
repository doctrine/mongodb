<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Aggregation\Stage\Project;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class ProjectTest extends TestCase
{
    use AggregationTestCase;

    public function testProjectStage()
    {
        $projectStage = new Project($this->getTestAggregationBuilder());
        $projectStage
            ->excludeFields(['_id'])
            ->includeFields(['$field', '$otherField'])
            ->field('product')
            ->multiply('$field', 5);

        $this->assertSame(['$project' => ['_id' => false, '$field' => true, '$otherField' => true, 'product' => ['$multiply' => ['$field', 5]]]], $projectStage->getExpression());
    }

    public function testProjectFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->project()
            ->excludeFields(['_id'])
            ->includeFields(['$field', '$otherField'])
            ->field('product')
            ->multiply('$field', 5);

        $this->assertSame([['$project' => ['_id' => false, '$field' => true, '$otherField' => true, 'product' => ['$multiply' => ['$field', 5]]]]], $builder->getPipeline());
    }

    /**
     * @dataProvider provideAccumulators
     */
    public function testAccumulatorsWithMultipleArguments($operator)
    {
        $projectStage = new Project($this->getTestAggregationBuilder());
        $projectStage
            ->field('something')
            ->$operator('$expression1', '$expression2');

        $this->assertSame(['$project' => ['something' => ['$' . $operator => ['$expression1', '$expression2']]]], $projectStage->getExpression());
    }

    public function provideAccumulators()
    {
        $operators = ['avg', 'max', 'min', 'stdDevPop', 'stdDevSamp', 'sum'];

        return array_combine($operators, array_map(function ($operator) { return [$operator]; }, $operators));
    }

    /**
     * @dataProvider provideProxiedExprMethods
     */
    public function testProxiedExprMethods($method, array $args = [])
    {
        $expr = $this->getMockAggregationExpr();
        $expr
            ->expects($this->once())
            ->method($method)
            ->with(...$args);

        $stage = new GroupStub($this->getTestAggregationBuilder());
        $stage->setQuery($expr);

        $this->assertSame($stage, $stage->$method(...$args));
    }

    public static function provideProxiedExprMethods()
    {
        $expression = new Expr();
        $expression
            ->field('dayOfMonth')
            ->dayOfMonth('$dateField')
            ->field('dayOfWeek')
            ->dayOfWeek('$dateField');

        return [
            'avg()' => ['avg', ['$field']],
            'max()' => ['max', ['$field']],
            'min()' => ['min', ['$field']],
            'stdDevPop()' => ['stdDevPop', ['$field']],
            'stdDevSamp()' => ['stdDevSamp', ['$field']],
            'sum()' => ['sum', ['$field']],
        ];
    }
}
