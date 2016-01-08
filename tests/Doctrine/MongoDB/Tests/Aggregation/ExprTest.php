<?php

namespace Doctrine\MongoDB\Tests\Aggregation;

use Doctrine\MongoDB\Aggregation\Expr;

class ExprTest extends \PHPUnit_Framework_TestCase
{
    public function testAbs()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->abs('$field'));
        $this->assertSame(['$abs' => '$field'], $expr->getExpression());
    }

    public function testAdd()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->add(5, '$field', '$otherField'));
        $this->assertSame(['$add' => [5, '$field', '$otherField']], $expr->getExpression());
    }

    public function testAddToSet()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->addToSet('$field'));
        $this->assertSame(['$addToSet' => '$field'], $expr->getExpression());
    }

    public function testAllElementsTrue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->allElementsTrue('$field'));
        $this->assertSame(['$allElementsTrue' => '$field'], $expr->getExpression());
    }

    public function testAnyElementTrue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->anyElementTrue('$field'));
        $this->assertSame(['$anyElementTrue' => '$field'], $expr->getExpression());
    }

    public function testArrayElemAt()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->arrayElemAt('$array', '$index'));
        $this->assertSame(['$arrayElemAt' => ['$array', '$index']], $expr->getExpression());
    }

    public function testAvg()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->avg('$field'));
        $this->assertSame(['$avg' => '$field'], $expr->getExpression());
    }

    public function testCeil()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->ceil('$field'));
        $this->assertSame(['$ceil' => '$field'], $expr->getExpression());
    }

    public function testCmp()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->cmp('$field', '$otherField'));
        $this->assertSame(['$cmp' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testConcat()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->concat('foo', '$field', '$otherField'));
        $this->assertSame(['$concat' => ['foo', '$field', '$otherField']], $expr->getExpression());
    }

    public function testConcatArrays()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->concatArrays('$array1', '$array2', '$array3'));
        $this->assertSame(['$concatArrays' => ['$array1', '$array2', '$array3']], $expr->getExpression());
    }

    public function testCond()
    {
        $if = new Expr();
        $if->gte('$field', 5);

        $expr = new Expr();

        $this->assertSame($expr, $expr->cond($if, '$field', '$otherField'));
        $this->assertSame(
            ['$cond' => ['if' => ['$gte' => ['$field', 5]], 'then' => '$field', 'else' => '$otherField']],
            $expr->getExpression()
        );
    }

    public function testDateToString()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->dateToString('%Y-%m-%d', '$dateField'));
        $this->assertSame(['$dateToString' => ['format' => '%Y-%m-%d', 'date' => '$dateField']], $expr->getExpression());
    }

    public function testDayOfMonth()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->dayOfMonth('$dateField'));
        $this->assertSame(['$dayOfMonth' => '$dateField'], $expr->getExpression());
    }

    public function testDayOfWeek()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->dayOfWeek('$dateField'));
        $this->assertSame(['$dayOfWeek' => '$dateField'], $expr->getExpression());
    }

    public function testDayOfYear()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->dayOfYear('$dateField'));
        $this->assertSame(['$dayOfYear' => '$dateField'], $expr->getExpression());
    }

    public function testDivide()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->divide('$field', 5));
        $this->assertSame(['$divide' => ['$field', 5]], $expr->getExpression());
    }

    public function testEq()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->eq('$field', '$otherField'));
        $this->assertSame(['$eq' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testExp()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->exp('$field'));
        $this->assertSame(['$exp' => '$field'], $expr->getExpression());
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

    public function testFirst()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->first('$field'));
        $this->assertSame(['$first' => '$field'], $expr->getExpression());
    }

    public function testFilter()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->filter('$array', '$as', '$cond'));
        $this->assertSame(['$filter' => ['input' => '$array', 'as' => '$as', 'cond' => '$cond']], $expr->getExpression());
    }

    public function testFloor()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->floor('$field'));
        $this->assertSame(['$floor' => '$field'], $expr->getExpression());
    }

    public function testGt()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->gt('$field', '$otherField'));
        $this->assertSame(['$gt' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testGte()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->gte('$field', '$otherField'));
        $this->assertSame(['$gte' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testHour()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->hour('$dateField'));
        $this->assertSame(['$hour' => '$dateField'], $expr->getExpression());
    }

    public function testIfNull()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->ifNull('$field', '$otherField'));
        $this->assertSame(['$ifNull' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testIsArray()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->isArray('$field'));
        $this->assertSame(['$isArray' => '$field'], $expr->getExpression());
    }

    public function testLast()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->last('$field'));
        $this->assertSame(['$last' => '$field'], $expr->getExpression());
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
            ['finalTotal' => [
                '$let' => [
                    'vars' => [
                        'total' => ['$add' => ['$price', '$tax']],
                        'discounted' => ['$cond' => ['if' => '$applyDiscount', 'then' => 0.9, 'else' => 1]]
                    ],
                    'in' => ['$multiply' => ['$$total', '$$discounted']]
                ]
            ]],
            $expr->getExpression()
        );
    }

    public function testLiteral()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('field')->literal('$field'));
        $this->assertSame(['field' => ['$literal' => '$field']], $expr->getExpression());
    }

    public function testLn()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->ln('$field'));
        $this->assertSame(['$ln' => '$field'], $expr->getExpression());
    }

    public function testLog()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->log('$number', '$base'));
        $this->assertSame(['$log' => ['$number', '$base']], $expr->getExpression());
    }

    public function testLog10()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->log10('$number'));
        $this->assertSame(['$log10' => '$number'], $expr->getExpression());
    }

    public function testLt()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->lt('$field', '$otherField'));
        $this->assertSame(['$lt' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testLte()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->lte('$field', '$otherField'));
        $this->assertSame(['$lte' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testMap()
    {
        $in = new Expr();
        $in->add('$$grade', 2);

        $expr = new Expr();

        $this->assertSame($expr, $expr->field('adjustedGrades')->map('$quizzes', 'grade', $in));
        $this->assertSame(
            [
                'adjustedGrades' => [
                    '$map' => [
                        'input' => '$quizzes',
                        'as' => 'grade',
                        'in' => [
                            '$add' => ['$$grade', 2]
                        ]
                    ]
                ]
            ],
            $expr->getExpression()
        );
    }

    public function testMax()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->max('$field'));
        $this->assertSame(['$max' => '$field'], $expr->getExpression());
    }

    public function testMeta()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->meta('textScore'));
        $this->assertSame(['$meta' => 'textScore'], $expr->getExpression());
    }

    public function testMillisecond()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->millisecond('$dateField'));
        $this->assertSame(['$millisecond' => '$dateField'], $expr->getExpression());
    }

    public function testMin()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->min('$field'));
        $this->assertSame(['$min' => '$field'], $expr->getExpression());
    }

    public function testMinute()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->minute('$dateField'));
        $this->assertSame(['$minute' => '$dateField'], $expr->getExpression());
    }

    public function testMod()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->mod('$field', 5));
        $this->assertSame(['$mod' => ['$field', 5]], $expr->getExpression());
    }

    public function testMonth()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->month('$dateField'));
        $this->assertSame(['$month' => '$dateField'], $expr->getExpression());
    }

    public function testMultiply()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->multiply('$field', 5));
        $this->assertSame(['$multiply' => ['$field', 5]], $expr->getExpression());
    }

    public function testNe()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->ne('$field', '$otherField'));
        $this->assertSame(['$ne' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testNot()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->not('$field'));
        $this->assertSame(['$not' => '$field'], $expr->getExpression());
    }

    public function testPow()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->pow('$number', '$exponent'));
        $this->assertSame(['$pow' => ['$number', '$exponent']], $expr->getExpression());
    }

    public function testPush()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->push('$field'));
        $this->assertSame(['$push' => '$field'], $expr->getExpression());
    }

    public function testSecond()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->second('$dateField'));
        $this->assertSame(['$second' => '$dateField'], $expr->getExpression());
    }

    public function testSetDifference()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setDifference('$field', '$otherField'));
        $this->assertSame(['$setDifference' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testSetEquals()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setEquals('$field', '$otherField', '$anotherField'));
        $this->assertSame(
            ['$setEquals' => ['$field', '$otherField', '$anotherField']],
            $expr->getExpression()
        );
    }

    public function testSetIntersection()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setIntersection('$field', '$otherField', '$anotherField'));
        $this->assertSame(
            ['$setIntersection' => ['$field', '$otherField', '$anotherField']],
            $expr->getExpression()
        );
    }

    public function testSetIsSubset()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setIsSubset('$field', '$otherField'));
        $this->assertSame(['$setIsSubset' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testSetUnion()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->setUnion('$field', '$otherField', '$anotherField'));
        $this->assertSame(
            ['$setUnion' => ['$field', '$otherField', '$anotherField']],
            $expr->getExpression()
        );
    }

    public function testSize()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->size('$field'));
        $this->assertSame(['$size' => '$field'], $expr->getExpression());
    }

    public function testSliceWithoutPosition()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->slice('$array', '$n'));
        $this->assertSame(['$slice' => ['$array', '$n']], $expr->getExpression());
    }

    public function testSliceWithPosition()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->slice('$array', '$n', '$position'));
        $this->assertSame(['$slice' => ['$array', '$position', '$n']], $expr->getExpression());
    }

    public function testSqrt()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->sqrt('$field'));
        $this->assertSame(['$sqrt' => '$field'], $expr->getExpression());
    }

    public function testStdDevPop()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->stdDevPop('$array1', '$array2', '$array3'));
        $this->assertSame(['$stdDevPop' => ['$array1', '$array2', '$array3']], $expr->getExpression());
    }

    public function testStdDevSamp()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->stdDevSamp('$array1', '$array2', '$array3'));
        $this->assertSame(['$stdDevSamp' => ['$array1', '$array2', '$array3']], $expr->getExpression());
    }

    public function testStrcasecmp()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->strcasecmp('$field', '$otherField'));
        $this->assertSame(['$strcasecmp' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testSubstr()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->substr('$field', 0, '$length'));
        $this->assertSame(['$substr' => ['$field', 0, '$length']], $expr->getExpression());
    }

    public function testSubtract()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->subtract('$field', '$otherField'));
        $this->assertSame(['$subtract' => ['$field', '$otherField']], $expr->getExpression());
    }

    public function testSum()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->sum('$field'));
        $this->assertSame(['$sum' => '$field'], $expr->getExpression());
    }

    public function testToLower()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->toLower('$field'));
        $this->assertSame(['$toLower' => '$field'], $expr->getExpression());
    }

    public function testToUpper()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->toUpper('$field'));
        $this->assertSame(['$toUpper' => '$field'], $expr->getExpression());
    }

    public function testTrunc()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->trunc('$field'));
        $this->assertSame(['$trunc' => '$field'], $expr->getExpression());
    }

    public function testWeek()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->week('$dateField'));
        $this->assertSame(['$week' => '$dateField'], $expr->getExpression());
    }

    public function testYear()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->year('$dateField'));
        $this->assertSame(['$year' => '$dateField'], $expr->getExpression());
    }
}
