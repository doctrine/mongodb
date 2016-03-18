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
    public function testProxiedExprMethods($method, array $args = array())
    {
        $expr = $this->getMockAggregationExpr();
        $invocationMocker = $expr->expects($this->once())->method($method);
        call_user_func_array(array($invocationMocker, 'with'), $args);

        $stage = new GroupStub($this->getTestAggregationBuilder());
        $stage->setQuery($expr);

        $this->assertSame($stage, call_user_func_array(array($stage, $method), $args));
    }

    public function provideProxiedExprMethods()
    {
        $expression = new Expr();
        $expression
            ->field('dayOfMonth')
            ->dayOfMonth('$dateField')
            ->field('dayOfWeek')
            ->dayOfWeek('$dateField');

        return array(
            'addToSet()' => array('addToSet', array('$field')),
            'avg()' => array('avg', array('$field')),
            'expression()' => array('expression', array($expression)),
            'first()' => array('first', array('$field')),
            'last()' => array('last', array('$field')),
            'max()' => array('max', array('$field')),
            'min()' => array('min', array('$field')),
            'push()' => array('push', array('$field')),
            'stdDevPop()' => array('stdDevPop', array('$field')),
            'stdDevSamp()' => array('stdDevSamp', array('$field')),
            'sum()' => array('sum', array('$field')),
        );
    }

    public function testGroupStage()
    {
        $groupStage = new Group($this->getTestAggregationBuilder());
        $groupStage
            ->field('_id')
            ->expression('$field')
            ->field('count')
            ->sum(1);

        $this->assertSame(array('$group' => array('_id' => '$field', 'count' => array('$sum' => 1))), $groupStage->getExpression());
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

        $this->assertSame(array(array('$group' => array('_id' => '$field', 'count' => array('$sum' => 1)))), $builder->getPipeline());
    }

    public function testGroupWithOperatorInId()
    {
        $groupStage = new Group($this->getTestAggregationBuilder());
        $groupStage
            ->field('_id')
            ->year('$dateField')
            ->field('count')
            ->sum(1);

        $this->assertSame(array('$group' => array('_id' => ['$year' => '$dateField'], 'count' => array('$sum' => 1))), $groupStage->getExpression());
    }
}
