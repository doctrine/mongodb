<?php

namespace Doctrine\MongoDB\Tests\Aggregation;

use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Tests\TestCase;

class ExprTest extends TestCase
{
    use AggregationOperatorsProviderTrait;

    /**
     * @dataProvider provideAllOperators
     */
    public function testGenericOperator($expected, $operator, $args)
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->$operator(...$args));
        $this->assertSame($expected, $expr->getExpression());
    }

    /**
     * @dataProvider provideAllOperators
     */
    public function testGenericOperatorWithField($expected, $operator, $args)
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('foo')->$operator(...$args));
        $this->assertSame(['foo' => $expected], $expr->getExpression());
    }

    public function testExpr()
    {
        $expr = new Expr();

        $newExpr = $expr->expr();
        $this->assertInstanceOf(Expr::class, $newExpr);
        $this->assertNotSame($newExpr, $expr);
    }

    public function testExpression()
    {
        $nestedExpr = new Expr();
        $nestedExpr
            ->field('dayOfMonth')
            ->dayOfMonth('$dateField')
            ->field('dayOfWeek')
            ->dayOfWeek('$dateField');

        $expr = new Expr();

        $this->assertSame($expr, $expr->field('nested')->expression($nestedExpr));
        $this->assertSame(
            [
                'nested' => [
                    'dayOfMonth' => ['$dayOfMonth' => '$dateField'],
                    'dayOfWeek' => ['$dayOfWeek' => '$dateField']
                ]
            ],
            $expr->getExpression()
        );
    }

    /**
     * @expectedException \LogicException
     */
    public function testExpressionWithoutField()
    {
        $nestedExpr = new Expr();
        $nestedExpr
            ->field('dayOfMonth')
            ->dayOfMonth('$dateField')
            ->field('dayOfWeek')
            ->dayOfWeek('$dateField');

        $expr = new Expr();

        $expr->expression($nestedExpr);
    }
}
