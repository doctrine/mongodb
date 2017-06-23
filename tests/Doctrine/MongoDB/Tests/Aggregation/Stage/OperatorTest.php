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

    /**
     * @return OperatorStub
     */
    public function getStubStage()
    {
        return new OperatorStub($this->getTestAggregationBuilder());
    }
}
