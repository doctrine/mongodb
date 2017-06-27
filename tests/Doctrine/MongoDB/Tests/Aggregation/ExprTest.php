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

    public function testSwitch()
    {
        $expr = new Expr();

        $expr->switch()
            ->case((new Expr())->eq('$numElements', 0))
            ->then('Zero elements given')
            ->case((new Expr())->eq('$numElements', 1))
            ->then('One element given')
            ->default((new Expr())->concat('$numElements', ' elements given'));

        $this->assertSame(
            [
                '$switch' => [
                    'branches' => [
                        ['case' => ['$eq' => ['$numElements', 0]], 'then' => 'Zero elements given'],
                        ['case' => ['$eq' => ['$numElements', 1]], 'then' => 'One element given'],
                    ],
                    'default' => ['$concat' => ['$numElements', ' elements given']],
                ]
            ],
            $expr->getExpression()
        );
    }

    public function testCallingCaseWithoutSwitchThrowsException()
    {
        $expr = new Expr();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\MongoDB\Aggregation\Expr::case requires a valid switch statement (call switch() first).');

        $expr->case('$field');
    }

    public function testCallingThenWithoutCaseThrowsException()
    {
        $expr = new Expr();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\MongoDB\Aggregation\Expr::then requires a valid case statement (call case() first).');

        $expr->then('$field');
    }

    public function testCallingThenWithoutCaseAfterSuccessfulCaseThrowsException()
    {
        $expr = new Expr();

        $expr->switch()
            ->case('$field')
            ->then('$field');

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\MongoDB\Aggregation\Expr::then requires a valid case statement (call case() first).');

        $expr->then('$field');
    }

    public function testCallingDefaultWithoutSwitchThrowsException()
    {
        $expr = new Expr();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\MongoDB\Aggregation\Expr::default requires a valid switch statement (call switch() first).');

        $expr->default('$field');
    }
}
