<?php

namespace Doctrine\MongoDB\Tests\Aggregation;

use Doctrine\MongoDB\Aggregation\Expr;

class ExprTest extends \PHPUnit_Framework_TestCase
{
    public function testAdd()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->add(5, '$field', '$otherField'));
        $this->assertSame(array('$add' => array(5, '$field', '$otherField')), $expr->getExpression());
    }

    public function testAddToSet()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->addToSet('$field'));
        $this->assertSame(array('$addToSet' => '$field'), $expr->getExpression());
    }

    public function testAllElementsTrue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->allElementsTrue('$field'));
        $this->assertSame(array('$allElementsTrue' => '$field'), $expr->getExpression());
    }

    public function testAnyElementTrue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->anyElementTrue('$field'));
        $this->assertSame(array('$anyElementTrue' => '$field'), $expr->getExpression());
    }

    public function testAvg()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->avg('$field'));
        $this->assertSame(array('$avg' => '$field'), $expr->getExpression());
    }

    public function testCmp()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->cmp('$field', '$otherField'));
        $this->assertSame(array('$cmp' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testConcat()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->concat('foo', '$field', '$otherField'));
        $this->assertSame(array('$concat' => array('foo', '$field', '$otherField')), $expr->getExpression());
    }

    public function testCond()
    {
        $if = new Expr();
        $if->gte('$field', 5);

        $expr = new Expr();

        $this->assertSame($expr, $expr->cond($if, '$field', '$otherField'));
        $this->assertSame(
            array('$cond' => array('if' => array('$gte' => array('$field', 5)), 'then' => '$field', 'else' => '$otherField')),
            $expr->getExpression()
        );
    }

    public function testDateToString()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->dateToString('%Y-%m-%d', '$dateField'));
        $this->assertSame(array('$dateToString' => array('format' => '%Y-%m-%d', 'date' => '$dateField')), $expr->getExpression());
    }

    public function testDayOfMonth()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->dayOfMonth('$dateField'));
        $this->assertSame(array('$dayOfMonth' => '$dateField'), $expr->getExpression());
    }

    public function testDayOfWeek()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->dayOfWeek('$dateField'));
        $this->assertSame(array('$dayOfWeek' => '$dateField'), $expr->getExpression());
    }

    public function testDayOfYear()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->dayOfYear('$dateField'));
        $this->assertSame(array('$dayOfYear' => '$dateField'), $expr->getExpression());
    }

    public function testDivide()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->divide('$field', 5));
        $this->assertSame(array('$divide' => array('$field', 5)), $expr->getExpression());
    }

    public function testEq()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->eq('$field', '$otherField'));
        $this->assertSame(array('$eq' => array('$field', '$otherField')), $expr->getExpression());
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
            array(
                'nested' => array(
                    'dayOfMonth' => array('$dayOfMonth' => '$dateField'),
                    'dayOfWeek' => array('$dayOfWeek' => '$dateField')
                )
            ),
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

    public function testFirst()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->first('$field'));
        $this->assertSame(array('$first' => '$field'), $expr->getExpression());
    }

    public function testGt()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->gt('$field', '$otherField'));
        $this->assertSame(array('$gt' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testGte()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->gte('$field', '$otherField'));
        $this->assertSame(array('$gte' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testHour()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->hour('$dateField'));
        $this->assertSame(array('$hour' => '$dateField'), $expr->getExpression());
    }

    public function testIfNull()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->ifNull('$field', '$otherField'));
        $this->assertSame(array('$ifNull' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testLast()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->last('$field'));
        $this->assertSame(array('$last' => '$field'), $expr->getExpression());
    }

    public function testLet()
    {
        $vars = new Expr();
        $vars
            ->field('total')
            ->add('$price', '$tax')
            ->field('discounted')
            ->cond('$applyDiscount', 0.9, 1);

        $in = new Expr();
        $in->multiply('$$total', '$$discounted');

        $expr = new Expr();

        $this->assertSame($expr, $expr->field('finalTotal')->let($vars, $in));
        $this->assertSame(
            array('finalTotal' => array(
                '$let' => array(
                    'vars' => array(
                        'total' => array('$add' => array('$price', '$tax')),
                        'discounted' => array('$cond' => array('if' => '$applyDiscount', 'then' => 0.9, 'else' => 1))
                    ),
                    'in' => array('$multiply' => array('$$total', '$$discounted'))
                )
            )),
            $expr->getExpression()
        );
    }

    public function testLiteral()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('field')->literal('$field'));
        $this->assertSame(array('field' => array('$literal' => '$field')), $expr->getExpression());
    }

    public function testLt()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->lt('$field', '$otherField'));
        $this->assertSame(array('$lt' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testLte()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->lte('$field', '$otherField'));
        $this->assertSame(array('$lte' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testMap()
    {
        $in = new Expr();
        $in->add('$$grade', 2);

        $expr = new Expr();

        $this->assertSame($expr, $expr->field('adjustedGrades')->map('$quizzes', 'grade', $in));
        $this->assertSame(
            array(
                'adjustedGrades' => array(
                    '$map' => array(
                        'input' => '$quizzes',
                        'as' => 'grade',
                        'in' => array(
                            '$add' => array('$$grade', 2)
                        )
                    )
                )
            ),
            $expr->getExpression()
        );
    }

    public function testMax()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->max('$field'));
        $this->assertSame(array('$max' => '$field'), $expr->getExpression());
    }

    public function testMeta()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->meta('textScore'));
        $this->assertSame(array('$meta' => 'textScore'), $expr->getExpression());
    }

    public function testMillisecond()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->millisecond('$dateField'));
        $this->assertSame(array('$millisecond' => '$dateField'), $expr->getExpression());
    }

    public function testMin()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->min('$field'));
        $this->assertSame(array('$min' => '$field'), $expr->getExpression());
    }

    public function testMinute()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->minute('$dateField'));
        $this->assertSame(array('$minute' => '$dateField'), $expr->getExpression());
    }

    public function testMod()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->mod('$field', 5));
        $this->assertSame(array('$mod' => array('$field', 5)), $expr->getExpression());
    }

    public function testMonth()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->month('$dateField'));
        $this->assertSame(array('$month' => '$dateField'), $expr->getExpression());
    }

    public function testMultiply()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->multiply('$field', 5));
        $this->assertSame(array('$multiply' => array('$field', 5)), $expr->getExpression());
    }

    public function testNe()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->ne('$field', '$otherField'));
        $this->assertSame(array('$ne' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testNot()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->not('$field'));
        $this->assertSame(array('$not' => '$field'), $expr->getExpression());
    }

    public function testPush()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->push('$field'));
        $this->assertSame(array('$push' => '$field'), $expr->getExpression());
    }

    public function testSecond()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->second('$dateField'));
        $this->assertSame(array('$second' => '$dateField'), $expr->getExpression());
    }

    public function testSetDifference()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setDifference('$field', '$otherField'));
        $this->assertSame(array('$setDifference' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testSetEquals()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setEquals('$field', '$otherField', '$anotherField'));
        $this->assertSame(
            array('$setEquals' => array('$field', '$otherField', '$anotherField')),
            $expr->getExpression()
        );
    }

    public function testSetIntersection()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setIntersection('$field', '$otherField', '$anotherField'));
        $this->assertSame(
            array('$setIntersection' => array('$field', '$otherField', '$anotherField')),
            $expr->getExpression()
        );
    }

    public function testSetIsSubset()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setIsSubset('$field', '$otherField'));
        $this->assertSame(array('$setIsSubset' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testSetUnion()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setUnion('$field', '$otherField', '$anotherField'));
        $this->assertSame(
            array('$setUnion' => array('$field', '$otherField', '$anotherField')),
            $expr->getExpression()
        );
    }

    public function testSize()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->size('$field'));
        $this->assertSame(array('$size' => '$field'), $expr->getExpression());
    }

    public function testStrcasecmp()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->strcasecmp('$field', '$otherField'));
        $this->assertSame(array('$strcasecmp' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testSubstr()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->substr('$field', 0, '$length'));
        $this->assertSame(array('$substr' => array('$field', 0, '$length')), $expr->getExpression());
    }

    public function testSubtract()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->subtract('$field', '$otherField'));
        $this->assertSame(array('$subtract' => array('$field', '$otherField')), $expr->getExpression());
    }

    public function testSum()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->sum('$field'));
        $this->assertSame(array('$sum' => '$field'), $expr->getExpression());
    }

    public function testToLower()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->toLower('$field'));
        $this->assertSame(array('$toLower' => '$field'), $expr->getExpression());
    }

    public function testToUpper()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->toUpper('$field'));
        $this->assertSame(array('$toUpper' => '$field'), $expr->getExpression());
    }

    public function testWeek()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->week('$dateField'));
        $this->assertSame(array('$week' => '$dateField'), $expr->getExpression());
    }

    public function testYear()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->year('$dateField'));
        $this->assertSame(array('$year' => '$dateField'), $expr->getExpression());
    }
}
