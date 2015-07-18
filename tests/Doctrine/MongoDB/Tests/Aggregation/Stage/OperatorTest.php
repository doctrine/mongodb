<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Aggregation\Stage\Match;

class OperatorTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideProxiedExprMethods
     */
    public function testProxiedExprMethods($method, array $args = array())
    {
        $expr = $this->getMockExpr();
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
            'add()' => array('add', array(5, '$field', '$otherField')),
            'allElementsTrue()' => array('allElementsTrue', array('$field')),
            'anyElementTrue()' => array('anyElementTrue', array('$field')),
            'cmp()' => array('cmp', array('$field', '$otherField')),
            'concat()' => array('concat', array('foo', '$field', '$otherField')),
            'cond()' => array('cond', array('$ifField', '$field', '$otherField')),
            'dateToString()' => array('dateToString', array('%Y-%m-%d', '$dateField')),
            'dayOfMonth()' => array('dayOfMonth', array('$dateField')),
            'dayOfWeek()' => array('dayOfWeek', array('$dateField')),
            'dayOfYear()' => array('dayOfYear', array('$dateField')),
            'divide()' => array('divide', array('$field', 5)),
            'eq()' => array('eq', array('$field', '$otherField')),
            'expression()' => array('expression', array($expression)),
            'gt()' => array('gt', array('$field', '$otherField')),
            'gte()' => array('gte', array('$field', '$otherField')),
            'hour()' => array('hour', array('$dateField')),
            'ifNull()' => array('ifNull', array('$field', '$otherField')),
            'let()' => array('let', array('$vars', '$in')),
            'literal()' => array('literal', array('$field')),
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
            'second()' => array('second', array('$dateField')),
            'setDifference()' => array('setDifference', array('$field', '$otherField')),
            'setEquals()' => array('setEquals', array('$field', '$otherField', '$anotherField')),
            'setIntersection()' => array('setIntersection', array('$field', '$otherField', '$anotherField')),
            'setIsSubset()' => array('setIsSubset', array('$field', '$otherField')),
            'setUnion()' => array('setUnion', array('$field', '$otherField', '$anotherField')),
            'size()' => array('size', array('$field')),
            'strcasecmp()' => array('strcasecmp', array('$field', '$otherField')),
            'substr()' => array('substr', array('$field', 0, '$length')),
            'subtract()' => array('subtract', array('$field', 5)),
            'toLower()' => array('toLower', array('$field')),
            'toUpper()' => array('toUpper', array('$field')),
            'week()' => array('week', array('$dateField')),
            'year()' => array('year', array('$dateField')),
        );
    }

    private function getTestAggregationBuilder()
    {
        return new Builder($this->getMockCollection());
    }

    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockExpr()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Aggregation\Expr')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getStubStage()
    {
        return new OperatorStub($this->getTestAggregationBuilder());
    }
}
