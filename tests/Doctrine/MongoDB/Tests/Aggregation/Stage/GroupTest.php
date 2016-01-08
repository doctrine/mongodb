<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Aggregation\Stage\Group;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class GroupTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

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

    public function provideProxiedExprMethods()
    {
        $expression = new Expr();
        $expression
            ->field('dayOfMonth')
            ->dayOfMonth('$dateField')
            ->field('dayOfWeek')
            ->dayOfWeek('$dateField');

        return [
            'addToSet()' => ['addToSet', ['$field']],
            'avg()' => ['avg', ['$field']],
            'expression()' => ['expression', [$expression]],
            'first()' => ['first', ['$field']],
            'last()' => ['last', ['$field']],
            'max()' => ['max', ['$field']],
            'min()' => ['min', ['$field']],
            'push()' => ['push', ['$field']],
            'stdDevPop()' => ['stdDevPop', ['$field']],
            'stdDevSamp()' => ['stdDevSamp', ['$field']],
            'sum()' => ['sum', ['$field']],
        ];
    }

    public function testGroupStage()
    {
        $groupStage = new Group($this->getTestAggregationBuilder());
        $groupStage
            ->field('_id')
            ->expression('$field')
            ->field('count')
            ->sum(1);

        $this->assertSame(['$group' => ['_id' => '$field', 'count' => ['$sum' => 1]]], $groupStage->getExpression());
    }

    public function testGroupFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->group()
            ->field('_id')
            ->expression('$field')
            ->field('count')
            ->sum(1);

        $this->assertSame([['$group' => ['_id' => '$field', 'count' => ['$sum' => 1]]]], $builder->getPipeline());
    }
}
