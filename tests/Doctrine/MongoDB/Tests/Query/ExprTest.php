<?php

namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Expr;
use Doctrine\MongoDB\Tests\TestCase;

class ExprTest extends TestCase
{
    public function testAddManyToSet()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->addManyToSet([1, 2]));
        $this->assertEquals(['$addToSet' => ['a' => ['$each' => [1, 2]]]], $expr->getNewObj());
    }

    public function testAddToSetWithValue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->addToSet(1));
        $this->assertEquals(['$addToSet' => ['a' => 1]], $expr->getNewObj());
    }

    public function testAddToSetWithExpression()
    {
        $expr = new Expr();
        $eachExpr = new Expr();
        $eachExpr->each([1, 2]);

        $this->assertSame($expr, $expr->field('a')->addToSet($eachExpr));
        $this->assertEquals(['$addToSet' => ['a' => ['$each' => [1, 2]]]], $expr->getNewObj());
    }

    public function testLanguageWithText()
    {
        $expr = new Expr();
        $expr->text('foo');

        $this->assertSame($expr, $expr->language('en'));
        $this->assertEquals(['$text' => ['$search' => 'foo', '$language' => 'en']], $expr->getQuery());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testLanguageRequiresTextOperator()
    {
        $expr = new Expr();
        $expr->language('en');
    }

    public function testCaseSensitiveWithText()
    {
        $expr = new Expr();
        $expr->text('foo');

        $this->assertSame($expr, $expr->caseSensitive(true));
        $this->assertEquals(['$text' => ['$search' => 'foo', '$caseSensitive' => true]], $expr->getQuery());
    }

    public function testCaseSensitiveFalseRemovesOption()
    {
        $expr = new Expr();
        $expr->text('foo');

        $expr->caseSensitive(true);
        $expr->caseSensitive(false);
        $this->assertEquals(['$text' => ['$search' => 'foo']], $expr->getQuery());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testCaseSensitiveRequiresTextOperator()
    {
        $expr = new Expr();
        $expr->caseSensitive('en');
    }

    public function testDiacriticSensitiveWithText()
    {
        $expr = new Expr();
        $expr->text('foo');

        $this->assertSame($expr, $expr->diacriticSensitive(true));
        $this->assertEquals(['$text' => ['$search' => 'foo', '$diacriticSensitive' => true]], $expr->getQuery());
    }

    public function testDiacriticSensitiveFalseRemovesOption()
    {
        $expr = new Expr();
        $expr->text('foo');

        $expr->diacriticSensitive(true);
        $expr->diacriticSensitive(false);
        $this->assertEquals(['$text' => ['$search' => 'foo']], $expr->getQuery());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testDiacriticSensitiveRequiresTextOperator()
    {
        $expr = new Expr();
        $expr->diacriticSensitive('en');
    }

    public function testOperatorWithCurrentField()
    {
        $expr = new Expr();
        $expr->field('field');

        $this->assertSame($expr, $expr->operator('$op', 'value'));
        $this->assertEquals(['field' => ['$op' => 'value']], $expr->getQuery());
    }

    public function testOperatorWithCurrentFieldWrapsEqualityCriteria()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->equals(1));
        $this->assertSame($expr, $expr->field('a')->lt(2));
        $this->assertSame($expr, $expr->field('b')->equals(null));
        $this->assertSame($expr, $expr->field('b')->lt(2));
        $this->assertSame($expr, $expr->field('c')->equals([]));
        $this->assertSame($expr, $expr->field('c')->lt(2));
        $this->assertSame($expr, $expr->field('d')->equals(['x' => 1]));
        $this->assertSame($expr, $expr->field('d')->lt(2));

        $expectedQuery = [
            'a' => ['$in' => [1], '$lt' => 2],
            'b' => ['$in' => [null], '$lt' => 2],
            // Equality match on empty array cannot be distinguished from no criteria and will be overridden
            'c' => ['$lt' => 2],
            'd' => ['$in' => [['x' => 1]], '$lt' => 2],
        ];

        $this->assertEquals($expectedQuery, $expr->getQuery());
    }

    public function testOperatorWithoutCurrentField()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->operator('$op', 'value'));
        $this->assertEquals(['$op' => 'value'], $expr->getQuery());
    }

    public function testOperatorWithoutCurrentFieldWrapsEqualityCriteria()
    {
        $expr = new Expr();
        $this->assertSame($expr, $expr->equals(1));
        $this->assertSame($expr, $expr->lt(2));
        $this->assertEquals(['$in' => [1], '$lt' => 2], $expr->getQuery());

        $expr = new Expr();
        $this->assertSame($expr, $expr->equals(null));
        $this->assertSame($expr, $expr->lt(2));
        $this->assertEquals(['$in' => [null], '$lt' => 2], $expr->getQuery());

        $expr = new Expr();
        $this->assertSame($expr, $expr->equals([]));
        $this->assertSame($expr, $expr->lt(2));
        // Equality match on empty array cannot be distinguished from no criteria and will be overridden
        $this->assertEquals(['$lt' => 2], $expr->getQuery());

        $expr = new Expr();
        $this->assertSame($expr, $expr->equals(['x' => 1]));
        $this->assertSame($expr, $expr->lt(2));
        $this->assertEquals(['$in' => [['x' => 1]], '$lt' => 2], $expr->getQuery());
    }

    /**
     * @dataProvider provideGeoJsonPoint
     */
    public function testMaxDistanceWithNearAndGeoJsonPoint($point, array $expected)
    {
        $expr = new Expr();
        $expr->near($point);

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(['$near' => $expected + ['$maxDistance' => 1]], $expr->getQuery());
    }

    public function provideGeoJsonPoint()
    {
        $json = ['type' => 'Point', 'coordinates' => [1, 2]];
        $expected = ['$geometry' => $json];

        return [
            'array' => [$json, $expected],
            'object' => [$this->getMockPoint($json), $expected],
        ];
    }

    public function testMaxDistanceWithNearAndLegacyCoordinates()
    {
        $expr = new Expr();
        $expr->near(1, 2);

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(['$near' => [1, 2], '$maxDistance' => 1], $expr->getQuery());
    }

    public function testMaxDistanceWithNearSphereAndGeoJsonPoint()
    {
        $json = ['type' => 'Point', 'coordinates' => [1, 2]];

        $expr = new Expr();
        $expr->nearSphere($this->getMockPoint($json));

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(['$nearSphere' => ['$geometry' => $json, '$maxDistance' => 1]], $expr->getQuery());
    }

    public function testMaxDistanceWithNearSphereAndLegacyCoordinates()
    {
        $expr = new Expr();
        $expr->nearSphere(1, 2);

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(['$nearSphere' => [1, 2], '$maxDistance' => 1], $expr->getQuery());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMaxDistanceRequiresNearOrNearSphereOperator()
    {
        $expr = new Expr();
        $expr->maxDistance(1);
    }

    /**
     * @dataProvider provideGeoJsonPoint
     */
    public function testMinDistanceWithNearAndGeoJsonPoint($point, array $expected)
    {
        $expr = new Expr();
        $expr->near($point);

        $this->assertSame($expr, $expr->minDistance(1));
        $this->assertEquals(['$near' => $expected + ['$minDistance' => 1]], $expr->getQuery());
    }

    public function testMinDistanceWithNearAndLegacyCoordinates()
    {
        $expr = new Expr();
        $expr->near(1, 2);

        $this->assertSame($expr, $expr->minDistance(1));
        $this->assertEquals(['$near' => [1, 2], '$minDistance' => 1], $expr->getQuery());
    }

    public function testMinDistanceWithNearSphereAndGeoJsonPoint()
    {
        $json = ['type' => 'Point', 'coordinates' => [1, 2]];

        $expr = new Expr();
        $expr->nearSphere($this->getMockPoint($json));

        $this->assertSame($expr, $expr->minDistance(1));
        $this->assertEquals(['$nearSphere' => ['$geometry' => $json, '$minDistance' => 1]], $expr->getQuery());
    }

    public function testMinDistanceWithNearSphereAndLegacyCoordinates()
    {
        $expr = new Expr();
        $expr->nearSphere(1, 2);

        $this->assertSame($expr, $expr->minDistance(1));
        $this->assertEquals(['$nearSphere' => [1, 2], '$minDistance' => 1], $expr->getQuery());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMinDistanceRequiresNearOrNearSphereOperator()
    {
        $expr = new Expr();
        $expr->minDistance(1);
    }

    /**
     * @dataProvider provideGeoJsonPoint
     */
    public function testNearWithGeoJsonPoint($point, array $expected)
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->near($point));
        $this->assertEquals(['$near' => $expected], $expr->getQuery());
    }

    public function testNearWithLegacyCoordinates()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->near(1, 2));
        $this->assertEquals(['$near' => [1, 2]], $expr->getQuery());
    }

    /**
     * @dataProvider provideGeoJsonPoint
     */
    public function testNearSphereWithGeoJsonPoint($point, array $expected)
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->nearSphere($point));
        $this->assertEquals(['$nearSphere' => $expected], $expr->getQuery());
    }

    public function testNearSphereWithLegacyCoordinates()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->nearSphere(1, 2));
        $this->assertEquals(['$nearSphere' => [1, 2]], $expr->getQuery());
    }

    public function testPullWithValue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->pull(1));
        $this->assertEquals(['$pull' => ['a' => 1]], $expr->getNewObj());
    }

    public function testPullWithExpression()
    {
        $expr = new Expr();
        $nestedExpr = new Expr();
        $nestedExpr->gt(3);

        $this->assertSame($expr, $expr->field('a')->pull($nestedExpr));
        $this->assertEquals(['$pull' => ['a' => ['$gt' => 3]]], $expr->getNewObj());
    }

    public function testPushWithValue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->push(1));
        $this->assertEquals(['$push' => ['a' => 1]], $expr->getNewObj());
    }

    public function testPushWithExpression()
    {
        $expr = new Expr();
        $innerExpr = new Expr();
        $innerExpr
            ->each([['x' => 1], ['x' => 2]])
            ->slice(-2)
            ->sort('x', 1);

        $expectedNewObj = [
            '$push' => ['a' => [
                '$each' => [['x' => 1], ['x' => 2]],
                '$slice' => -2,
                '$sort' => ['x' => 1],
            ]],
        ];

        $this->assertSame($expr, $expr->field('a')->push($innerExpr));
        $this->assertEquals($expectedNewObj, $expr->getNewObj());
    }

    public function testPushWithExpressionShouldEnsureEachOperatorAppearsFirst()
    {
        $expr = new Expr();
        $innerExpr = new Expr();
        $innerExpr
            ->sort('x', 1)
            ->slice(-2)
            ->each([['x' => 1], ['x' => 2]]);

        $expectedNewObj = [
            '$push' => ['a' => [
                '$each' => [['x' => 1], ['x' => 2]],
                '$sort' => ['x' => 1],
                '$slice' => -2,
            ]],
        ];

        $this->assertSame($expr, $expr->field('a')->push($innerExpr));
        $this->assertSame($expectedNewObj, $expr->getNewObj());
    }

    public function testPushWithPosition()
    {
        $expr = new Expr();
        $innerExpr = new Expr();
        $innerExpr
            ->each([20, 30])
            ->position(0);

        $expectedNewObj = [
            '$push' => ['a' => [
                '$each' => [20, 30],
                '$position' => 0,
            ]],
        ];

        $this->assertSame($expr, $expr->field('a')->push($innerExpr));
        $this->assertEquals($expectedNewObj, $expr->getNewObj());
    }

    /**
     * @dataProvider provideGeoJsonPolygon
     */
    public function testGeoIntersects($geometry, array $expected)
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoIntersects($geometry));
        $this->assertEquals(['$geoIntersects' => $expected], $expr->getQuery());
    }

    public function provideGeoJsonPolygon()
    {
        $json = [
            'type' => 'Polygon',
            'coordinates' => [[[0, 0], [1, 1], [1, 0], [0, 0]]],
        ];

        $expected = ['$geometry' => $json];

        return [
            'array' => [$json, $expected],
            'object' => [$this->getMockPolygon($json), $expected],
        ];
    }

    /**
     * @dataProvider provideGeoJsonPolygon
     */
    public function testGeoWithin($geometry, array $expected)
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoWithin($geometry));
        $this->assertEquals(['$geoWithin' => $expected], $expr->getQuery());
    }

    public function testGeoWithinBox()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoWithinBox(1, 2, 3, 4));
        $this->assertEquals(['$geoWithin' => ['$box' => [[1, 2], [3, 4]]]], $expr->getQuery());
    }

    public function testGeoWithinCenter()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoWithinCenter(1, 2, 3));
        $this->assertEquals(['$geoWithin' => ['$center' => [[1, 2], 3]]], $expr->getQuery());
    }

    public function testGeoWithinCenterSphere()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoWithinCenterSphere(1, 2, 3));
        $this->assertEquals(['$geoWithin' => ['$centerSphere' => [[1, 2], 3]]], $expr->getQuery());
    }

    public function testGeoWithinPolygon()
    {
        $expr = new Expr();
        $expectedQuery = ['$geoWithin' => ['$polygon' => [[0, 0], [1, 1], [1, 0]]]];

        $this->assertSame($expr, $expr->geoWithinPolygon([0, 0], [1, 1], [1, 0]));
        $this->assertEquals($expectedQuery, $expr->getQuery());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGeoWithinPolygonRequiresAtLeastThreePoints()
    {
        $expr = new Expr();
        $expr->geoWithinPolygon([0, 0], [1, 1]);
    }

    public function testSetWithAtomic()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->set(1, true));
        $this->assertEquals(['$set' => ['a' => 1]], $expr->getNewObj());
    }

    public function testSetWithoutAtomicWithTopLevelField()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->set(1, false));
        $this->assertEquals(['a' => 1], $expr->getNewObj());
    }

    public function testSetWithoutAtomicWithNestedField()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a.b.c')->set(1, false));
        $this->assertEquals(['a' => ['b' => ['c' => 1]]], $expr->getNewObj());
    }

    public function testText()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->text('foo'));
        $this->assertEquals(['$text' => ['$search' => 'foo']], $expr->getQuery());
        $this->assertNull($expr->getCurrentField());
    }

    public function testWhere()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->where('javascript'));
        $this->assertEquals(['$where' => 'javascript'], $expr->getQuery());
        $this->assertNull($expr->getCurrentField());
    }

    public function testWithinBox()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->withinBox(1, 2, 3, 4));
        $this->assertEquals(['$within' => ['$box' => [[1, 2], [3, 4]]]], $expr->getQuery());
    }

    public function testWithinCenter()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->withinCenter(1, 2, 3));
        $this->assertEquals(['$within' => ['$center' => [[1, 2], 3]]], $expr->getQuery());
    }

    public function testWithinCenterSphere()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->withinCenterSphere(1, 2, 3));
        $this->assertEquals(['$within' => ['$centerSphere' => [[1, 2], 3]]], $expr->getQuery());
    }

    public function testWithinPolygon()
    {
        $expr = new Expr();
        $expectedQuery = ['$within' => ['$polygon' => [[0, 0], [1, 1], [1, 0]]]];

        $this->assertSame($expr, $expr->withinPolygon([0, 0], [1, 1], [1, 0]));
        $this->assertEquals($expectedQuery, $expr->getQuery());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithinPolygonRequiresAtLeastThreePoints()
    {
        $expr = new Expr();
        $expr->withinPolygon([0, 0], [1, 1]);
    }

    private function getMockPoint($json)
    {
        $point = $this->getMockBuilder('GeoJson\Geometry\Point')
            ->disableOriginalConstructor()
            ->getMock();

        $point->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue($json));

        return $point;
    }

    private function getMockPolygon($json)
    {
        $point = $this->getMockBuilder('GeoJson\Geometry\Polygon')
            ->disableOriginalConstructor()
            ->getMock();

        $point->expects($this->once())
            ->method('jsonSerialize')
            ->will($this->returnValue($json));

        return $point;
    }

    public function testIn()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->in(['value1', 'value2']));
        $this->assertEquals(['$in' => ['value1', 'value2']], $expr->getQuery());
    }

    public function testInWillStripKeysToYieldBsonArray()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->in([1 => 'value1', 'some' => 'value2']));
        $this->assertEquals(['$in' => ['value1', 'value2']], $expr->getQuery());
    }

    public function testNotIn()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->notIn(['value1', 'value2']));
        $this->assertEquals(['$nin' => ['value1', 'value2']], $expr->getQuery());
    }

    public function testNotInWillStripKeysToYieldBsonArray()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->notIn([1 => 'value1', 'some' => 'value2']));
        $this->assertEquals(['$nin' => ['value1', 'value2']], $expr->getQuery());
    }
}
