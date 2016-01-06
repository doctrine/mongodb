<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Aggregation\Stage\Match;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class OperatorTest extends \PHPUnit_Framework_TestCase
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

        $stage = $this->getStubStage();
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
            'abs()' => array('abs', array('$number')),
            'add()' => array('add', array(5, '$field', '$otherField')),
            'allElementsTrue()' => array('allElementsTrue', array('$field')),
            'anyElementTrue()' => array('anyElementTrue', array('$field')),
            'arrayElemAt()' => array('arrayElemAt', array('$array', '$index')),
            'ceil()' => array('ceil', array('$number')),
            'cmp()' => array('cmp', array('$field', '$otherField')),
            'concat()' => array('concat', array('foo', '$field', '$otherField')),
            'concatArrays()' => array('concatArrays', array('$field', '$otherField')),
            'cond()' => array('cond', array('$ifField', '$field', '$otherField')),
            'dateToString()' => array('dateToString', array('%Y-%m-%d', '$dateField')),
            'dayOfMonth()' => array('dayOfMonth', array('$dateField')),
            'dayOfWeek()' => array('dayOfWeek', array('$dateField')),
            'dayOfYear()' => array('dayOfYear', array('$dateField')),
            'divide()' => array('divide', array('$field', 5)),
            'eq()' => array('eq', array('$field', '$otherField')),
            'exp()' => array('exp', array('$field')),
            'expression()' => array('expression', array($expression)),
            'filter()' => array('filter', array('$input', '$as', '$cond')),
            'floor()' => array('floor', array('$number')),
            'gt()' => array('gt', array('$field', '$otherField')),
            'gte()' => array('gte', array('$field', '$otherField')),
            'hour()' => array('hour', array('$dateField')),
            'ifNull()' => array('ifNull', array('$field', '$otherField')),
            'isArray()' => array('isArray', array('$field')),
            'let()' => array('let', array('$vars', '$in')),
            'literal()' => array('literal', array('$field')),
            'ln()' => array('ln', array('$number')),
            'log()' => array('log', array('$number', '$base')),
            'log10()' => array('log10', array('$number')),
            'lt()' => array('lt', array('$field', '$otherField')),
            'lte()' => array('lte', array('$field', '$otherField')),
            'map()' => array('map', array('$quizzes', 'grade', array('$add' => array('$$grade' => 2)))),
            'meta()' => array('meta', array('textScore')),
            'millisecond()' => array('millisecond', array('$dateField')),
            'minute()' => array('minute', array('$dateField')),
            'mod()' => array('mod', array('$field', 5)),
            'month()' => array('month', array('$dateField')),
            'multiply()' => array('multiply', array('$field', 5)),
            'ne()' => array('ne', array('$field', '$otherField')),
            'not()' => array('not', array('$field')),
            'pow()' => array('pow', array('$number', '$exponent')),
            'second()' => array('second', array('$dateField')),
            'setDifference()' => array('setDifference', array('$field', '$otherField')),
            'setEquals()' => array('setEquals', array('$field', '$otherField', '$anotherField')),
            'setIntersection()' => array('setIntersection', array('$field', '$otherField', '$anotherField')),
            'setIsSubset()' => array('setIsSubset', array('$field', '$otherField')),
            'setUnion()' => array('setUnion', array('$field', '$otherField', '$anotherField')),
            'size()' => array('size', array('$field')),
            'slice()' => array('slice', array('$array', '$index')),
            'sqrt()' => array('sqrt', array('$number')),
            'strcasecmp()' => array('strcasecmp', array('$field', '$otherField')),
            'substr()' => array('substr', array('$field', 0, '$length')),
            'subtract()' => array('subtract', array('$field', 5)),
            'toLower()' => array('toLower', array('$field')),
            'toUpper()' => array('toUpper', array('$field')),
            'trunc()' => array('trunc', array('$number')),
            'week()' => array('week', array('$dateField')),
            'year()' => array('year', array('$dateField')),
        );
    }

    private function getStubStage()
    {
        return new OperatorStub($this->getTestAggregationBuilder());
    }
}
