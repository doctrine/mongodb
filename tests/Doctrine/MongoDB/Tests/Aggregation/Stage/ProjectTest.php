<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Aggregation\Stage\Project;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class ProjectTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testProjectStage()
    {
        $projectStage = new Project($this->getTestAggregationBuilder());
        $projectStage
            ->excludeIdField()
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
            ->excludeIdField()
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
        $invocationMocker = $expr->expects($this->once())->method($method);
        call_user_func_array([$invocationMocker, 'with'], $args);

        $stage = new GroupStub($this->getTestAggregationBuilder());
        $stage->setQuery($expr);

        $this->assertSame($stage, call_user_func_array([$stage, $method], $args));
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
