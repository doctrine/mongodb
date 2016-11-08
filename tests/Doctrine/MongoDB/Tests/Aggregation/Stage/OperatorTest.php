<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class OperatorTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

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

        $stage = $this->getStubStage();
        $stage->setQuery($expr);

        $this->assertSame($stage, $stage->$method(...$args));
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
            'abs()' => ['abs', ['$number']],
            'add()' => ['add', [5, '$field', '$otherField']],
            'allElementsTrue()' => ['allElementsTrue', ['$field']],
            'anyElementTrue()' => ['anyElementTrue', ['$field']],
            'arrayElemAt()' => ['arrayElemAt', ['$array', '$index']],
            'ceil()' => ['ceil', ['$number']],
            'cmp()' => ['cmp', ['$field', '$otherField']],
            'concat()' => ['concat', ['foo', '$field', '$otherField']],
            'concatArrays()' => ['concatArrays', ['$field', '$otherField']],
            'cond()' => ['cond', ['$ifField', '$field', '$otherField']],
            'dateToString()' => ['dateToString', ['%Y-%m-%d', '$dateField']],
            'dayOfMonth()' => ['dayOfMonth', ['$dateField']],
            'dayOfWeek()' => ['dayOfWeek', ['$dateField']],
            'dayOfYear()' => ['dayOfYear', ['$dateField']],
            'divide()' => ['divide', ['$field', 5]],
            'eq()' => ['eq', ['$field', '$otherField']],
            'exp()' => ['exp', ['$field']],
            'expression()' => ['expression', [$expression]],
            'filter()' => ['filter', ['$input', '$as', '$cond']],
            'floor()' => ['floor', ['$number']],
            'gt()' => ['gt', ['$field', '$otherField']],
            'gte()' => ['gte', ['$field', '$otherField']],
            'hour()' => ['hour', ['$dateField']],
            'ifNull()' => ['ifNull', ['$field', '$otherField']],
            'isArray()' => ['isArray', ['$field']],
            'let()' => ['let', ['$vars', '$in']],
            'literal()' => ['literal', ['$field']],
            'ln()' => ['ln', ['$number']],
            'log()' => ['log', ['$number', '$base']],
            'log10()' => ['log10', ['$number']],
            'lt()' => ['lt', ['$field', '$otherField']],
            'lte()' => ['lte', ['$field', '$otherField']],
            'map()' => ['map', ['$quizzes', 'grade', ['$add' => ['$$grade' => 2]]]],
            'meta()' => ['meta', ['textScore']],
            'millisecond()' => ['millisecond', ['$dateField']],
            'minute()' => ['minute', ['$dateField']],
            'mod()' => ['mod', ['$field', 5]],
            'month()' => ['month', ['$dateField']],
            'multiply()' => ['multiply', ['$field', 5]],
            'ne()' => ['ne', ['$field', '$otherField']],
            'not()' => ['not', ['$field']],
            'pow()' => ['pow', ['$number', '$exponent']],
            'second()' => ['second', ['$dateField']],
            'setDifference()' => ['setDifference', ['$field', '$otherField']],
            'setEquals()' => ['setEquals', ['$field', '$otherField', '$anotherField']],
            'setIntersection()' => ['setIntersection', ['$field', '$otherField', '$anotherField']],
            'setIsSubset()' => ['setIsSubset', ['$field', '$otherField']],
            'setUnion()' => ['setUnion', ['$field', '$otherField', '$anotherField']],
            'size()' => ['size', ['$field']],
            'slice()' => ['slice', ['$array', '$index']],
            'sqrt()' => ['sqrt', ['$number']],
            'strcasecmp()' => ['strcasecmp', ['$field', '$otherField']],
            'substr()' => ['substr', ['$field', 0, '$length']],
            'subtract()' => ['subtract', ['$field', 5]],
            'toLower()' => ['toLower', ['$field']],
            'toUpper()' => ['toUpper', ['$field']],
            'trunc()' => ['trunc', ['$number']],
            'week()' => ['week', ['$dateField']],
            'year()' => ['year', ['$dateField']],
        ];
    }

    private function getStubStage()
    {
        return new OperatorStub($this->getTestAggregationBuilder());
    }
}
