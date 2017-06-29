<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Tests\Aggregation\AggregationOperatorsProviderTrait;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class OperatorTest extends TestCase
{
    use AggregationTestCase, AggregationOperatorsProviderTrait;

    /**
     * @dataProvider provideExpressionOperators
     */
    public function testProxiedExpressionOperators($expected, $operator, $args)
    {
        $stage = $this->getStubStage();

        $this->assertSame($stage, $stage->$operator(...$args));
        $this->assertSame($expected, $stage->getExpression());
    }

    public function testExpression()
    {
        $stage = $this->getStubStage();

        $nestedExpr = new Expr();
        $nestedExpr
            ->field('dayOfMonth')
            ->dayOfMonth('$dateField')
            ->field('dayOfWeek')
            ->dayOfWeek('$dateField');

        $this->assertSame($stage, $stage->field('nested')->expression($nestedExpr));
        $this->assertSame(
            [
                'nested' => [
                    'dayOfMonth' => ['$dayOfMonth' => '$dateField'],
                    'dayOfWeek' => ['$dayOfWeek' => '$dateField']
                ]
            ],
            $stage->getExpression()
        );
    }

    public function testSwitch()
    {
        $stage = $this->getStubStage();

        $stage->switch()
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
            $stage->getExpression()
        );
    }

    public function testCallingCaseWithoutSwitchThrowsException()
    {
        $stage = $this->getStubStage();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\MongoDB\Aggregation\Expr::case requires a valid switch statement (call switch() first).');

        $stage->case('$field');
    }

    public function testCallingThenWithoutCaseThrowsException()
    {
        $stage = $this->getStubStage();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\MongoDB\Aggregation\Expr::then requires a valid case statement (call case() first).');

        $stage->then('$field');
    }

    public function testCallingThenWithoutCaseAfterSuccessfulCaseThrowsException()
    {
        $stage = $this->getStubStage();

        $stage->switch()
            ->case('$field')
            ->then('$field');

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\MongoDB\Aggregation\Expr::then requires a valid case statement (call case() first).');

        $stage->then('$field');
    }

    public function testCallingDefaultWithoutSwitchThrowsException()
    {
        $stage = $this->getStubStage();

        $this->expectException(\BadMethodCallException::class);
        $this->expectExceptionMessage('Doctrine\MongoDB\Aggregation\Expr::default requires a valid switch statement (call switch() first).');

        $stage->default('$field');
    }

    /**
     * @return OperatorStub
     */
    public function getStubStage()
    {
        return new OperatorStub($this->getTestAggregationBuilder());
    }
}
