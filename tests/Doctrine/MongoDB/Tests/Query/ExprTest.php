<?php

namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Expr;

class ExprTest extends \PHPUnit_Framework_TestCase
{
    public function testAddManyToSet()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->addManyToSet(array(1, 2)));
        $this->assertEquals(array('$addToSet' => array('a' => array('$each' => array(1, 2)))), $expr->getNewObj());
    }

    public function testAddToSetWithValue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->addToSet(1));
        $this->assertEquals(array('$addToSet' => array('a' => 1)), $expr->getNewObj());
    }

    public function testAddToSetWithExpression()
    {
        $expr = new Expr();
        $eachExpr = new Expr();
        $eachExpr->each(array(1, 2));

        $this->assertSame($expr, $expr->field('a')->addToSet($eachExpr));
        $this->assertEquals(array('$addToSet' => array('a' => array('$each' => array(1, 2)))), $expr->getNewObj());
    }

    public function testLanguageWithText()
    {
        $expr = new Expr();
        $expr->text('foo');

        $this->assertSame($expr, $expr->language('en'));
        $this->assertEquals(array('$text' => array('$search' => 'foo', '$language' => 'en')), $expr->getQuery());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testLanguageRequiresTextOperator()
    {
        $expr = new Expr();
        $expr->language('en');
    }

    public function testOperatorWithCurrentField()
    {
        $expr = new Expr();
        $expr->field('field');

        $this->assertSame($expr, $expr->operator('$op', 'value'));
        $this->assertEquals(array('field' => array('$op' => 'value')), $expr->getQuery());
    }

    public function testOperatorWithCurrentFieldWrapsEqualityCriteria()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->equals(1));
        $this->assertSame($expr, $expr->field('a')->lt(2));
        $this->assertSame($expr, $expr->field('b')->equals(null));
        $this->assertSame($expr, $expr->field('b')->lt(2));
        $this->assertSame($expr, $expr->field('c')->equals(array()));
        $this->assertSame($expr, $expr->field('c')->lt(2));
        $this->assertSame($expr, $expr->field('d')->equals(array('x' => 1)));
        $this->assertSame($expr, $expr->field('d')->lt(2));

        $expectedQuery = array(
            'a' => array('$in' => array(1), '$lt' => 2),
            'b' => array('$in' => array(null), '$lt' => 2),
            // Equality match on empty array cannot be distinguished from no criteria and will be overridden
            'c' => array('$lt' => 2),
            'd' => array('$in' => array(array('x' => 1)), '$lt' => 2),
        );

        $this->assertEquals($expectedQuery, $expr->getQuery());
    }

    public function testOperatorWithoutCurrentField()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->operator('$op', 'value'));
        $this->assertEquals(array('$op' => 'value'), $expr->getQuery());
    }

    public function testOperatorWithoutCurrentFieldWrapsEqualityCriteria()
    {
        $expr = new Expr();
        $this->assertSame($expr, $expr->equals(1));
        $this->assertSame($expr, $expr->lt(2));
        $this->assertEquals(array('$in' => array(1), '$lt' => 2), $expr->getQuery());

        $expr = new Expr();
        $this->assertSame($expr, $expr->equals(null));
        $this->assertSame($expr, $expr->lt(2));
        $this->assertEquals(array('$in' => array(null), '$lt' => 2), $expr->getQuery());

        $expr = new Expr();
        $this->assertSame($expr, $expr->equals(array()));
        $this->assertSame($expr, $expr->lt(2));
        // Equality match on empty array cannot be distinguished from no criteria and will be overridden
        $this->assertEquals(array('$lt' => 2), $expr->getQuery());

        $expr = new Expr();
        $this->assertSame($expr, $expr->equals(array('x' => 1)));
        $this->assertSame($expr, $expr->lt(2));
        $this->assertEquals(array('$in' => array(array('x' => 1)), '$lt' => 2), $expr->getQuery());
    }

    /**
     * @dataProvider provideGeoJsonPoint
     */
    public function testMaxDistanceWithNearAndGeoJsonPoint($point, array $expected)
    {
        $expr = new Expr();
        $expr->near($point);

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(array('$near' => $expected + array('$maxDistance' => 1)), $expr->getQuery());
    }

    public function provideGeoJsonPoint()
    {
        $json = array('type' => 'Point', 'coordinates' => array(1, 2));
        $expected = array('$geometry' => $json);

        return array(
            'array' => array($json, $expected),
            'object' => array($this->getMockPoint($json), $expected),
        );
    }

    public function testMaxDistanceWithNearAndLegacyCoordinates()
    {
        $expr = new Expr();
        $expr->near(1, 2);

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(array('$near' => array(1, 2), '$maxDistance' => 1), $expr->getQuery());
    }

    public function testMaxDistanceWithNearSphereAndGeoJsonPoint()
    {
        $json = array('type' => 'Point', 'coordinates' => array(1, 2));

        $expr = new Expr();
        $expr->nearSphere($this->getMockPoint($json));

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(array('$nearSphere' => array('$geometry' => $json, '$maxDistance' => 1)), $expr->getQuery());
    }

    public function testMaxDistanceWithNearSphereAndLegacyCoordinates()
    {
        $expr = new Expr();
        $expr->nearSphere(1, 2);

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(array('$nearSphere' => array(1, 2), '$maxDistance' => 1), $expr->getQuery());
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
        $this->assertEquals(array('$near' => $expected + array('$minDistance' => 1)), $expr->getQuery());
    }

    public function testMinDistanceWithNearAndLegacyCoordinates()
    {
        $expr = new Expr();
        $expr->near(1, 2);

        $this->assertSame($expr, $expr->minDistance(1));
        $this->assertEquals(array('$near' => array(1, 2), '$minDistance' => 1), $expr->getQuery());
    }

    public function testMinDistanceWithNearSphereAndGeoJsonPoint()
    {
        $json = array('type' => 'Point', 'coordinates' => array(1, 2));

        $expr = new Expr();
        $expr->nearSphere($this->getMockPoint($json));

        $this->assertSame($expr, $expr->minDistance(1));
        $this->assertEquals(array('$nearSphere' => array('$geometry' => $json, '$minDistance' => 1)), $expr->getQuery());
    }

    public function testMinDistanceWithNearSphereAndLegacyCoordinates()
    {
        $expr = new Expr();
        $expr->nearSphere(1, 2);

        $this->assertSame($expr, $expr->minDistance(1));
        $this->assertEquals(array('$nearSphere' => array(1, 2), '$minDistance' => 1), $expr->getQuery());
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
        $this->assertEquals(array('$near' => $expected), $expr->getQuery());
    }

    public function testNearWithLegacyCoordinates()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->near(1, 2));
        $this->assertEquals(array('$near' => array(1, 2)), $expr->getQuery());
    }

    /**
     * @dataProvider provideGeoJsonPoint
     */
    public function testNearSphereWithGeoJsonPoint($point, array $expected)
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->nearSphere($point));
        $this->assertEquals(array('$nearSphere' => $expected), $expr->getQuery());
    }

    public function testNearSphereWithLegacyCoordinates()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->nearSphere(1, 2));
        $this->assertEquals(array('$nearSphere' => array(1, 2)), $expr->getQuery());
    }

    public function testPullWithValue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->pull(1));
        $this->assertEquals(array('$pull' => array('a' => 1)), $expr->getNewObj());
    }

    public function testPullWithExpression()
    {
        $expr = new Expr();
        $nestedExpr = new Expr();
        $nestedExpr->gt(3);

        $this->assertSame($expr, $expr->field('a')->pull($nestedExpr));
        $this->assertEquals(array('$pull' => array('a' => array('$gt' => 3))), $expr->getNewObj());
    }

    public function testPushWithValue()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->push(1));
        $this->assertEquals(array('$push' => array('a' => 1)), $expr->getNewObj());
    }

    public function testPushWithExpression()
    {
        $expr = new Expr();
        $innerExpr = new Expr();
        $innerExpr
            ->each(array(array('x' => 1), array('x' => 2)))
            ->slice(-2)
            ->sort('x', 1);

        $expectedNewObj = array(
            '$push' => array('a' => array(
                '$each' => array(array('x' => 1), array('x' => 2)),
                '$slice' => -2,
                '$sort' => array('x' => 1),
            )),
        );

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
            ->each(array(array('x' => 1), array('x' => 2)));

        $expectedNewObj = array(
            '$push' => array('a' => array(
                '$each' => array(array('x' => 1), array('x' => 2)),
                '$sort' => array('x' => 1),
                '$slice' => -2,
            )),
        );

        $this->assertSame($expr, $expr->field('a')->push($innerExpr));
        $this->assertSame($expectedNewObj, $expr->getNewObj());
    }

    public function testPushWithPosition()
    {
        $expr = new Expr();
        $innerExpr = new Expr();
        $innerExpr
            ->each(array(20, 30))
            ->position(0);

        $expectedNewObj = array(
            '$push' => array('a' => array(
                '$each' => array(20, 30),
                '$position' => 0,
            )),
        );

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
        $this->assertEquals(array('$geoIntersects' => $expected), $expr->getQuery());
    }

    public function provideGeoJsonPolygon()
    {
        $json = array(
            'type' => 'Polygon',
            'coordinates' => array(array(array(0, 0), array(1, 1), array(1, 0), array(0, 0))),
        );

        $expected = array('$geometry' => $json);

        return array(
            'array' => array($json, $expected),
            'object' => array($this->getMockPolygon($json), $expected),
        );
    }

    /**
     * @dataProvider provideGeoJsonPolygon
     */
    public function testGeoWithin($geometry, array $expected)
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoWithin($geometry));
        $this->assertEquals(array('$geoWithin' => $expected), $expr->getQuery());
    }

    public function testGeoWithinBox()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoWithinBox(1, 2, 3, 4));
        $this->assertEquals(array('$geoWithin' => array('$box' => array(array(1, 2), array(3, 4)))), $expr->getQuery());
    }

    public function testGeoWithinCenter()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoWithinCenter(1, 2, 3));
        $this->assertEquals(array('$geoWithin' => array('$center' => array(array(1, 2), 3))), $expr->getQuery());
    }

    public function testGeoWithinCenterSphere()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->geoWithinCenterSphere(1, 2, 3));
        $this->assertEquals(array('$geoWithin' => array('$centerSphere' => array(array(1, 2), 3))), $expr->getQuery());
    }

    public function testGeoWithinPolygon()
    {
        $expr = new Expr();
        $expectedQuery = array('$geoWithin' => array('$polygon' => array(array(0, 0), array(1, 1), array(1, 0))));

        $this->assertSame($expr, $expr->geoWithinPolygon(array(0, 0), array(1, 1), array(1, 0)));
        $this->assertEquals($expectedQuery, $expr->getQuery());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGeoWithinPolygonRequiresAtLeastThreePoints()
    {
        $expr = new Expr();
        $expr->geoWithinPolygon(array(0, 0), array(1, 1));
    }

    public function testSetWithAtomic()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->set(1, true));
        $this->assertEquals(array('$set' => array('a' => 1)), $expr->getNewObj());
    }

    public function testSetWithoutAtomicWithTopLevelField()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a')->set(1, false));
        $this->assertEquals(array('a' => 1), $expr->getNewObj());
    }

    public function testSetWithoutAtomicWithNestedField()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->field('a.b.c')->set(1, false));
        $this->assertEquals(array('a' => array('b' => array('c' => 1))), $expr->getNewObj());
    }

    public function testText()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->text('foo'));
        $this->assertEquals(array('$text' => array('$search' => 'foo')), $expr->getQuery());
        $this->assertNull($expr->getCurrentField());
    }

    public function testWhere()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->where('javascript'));
        $this->assertEquals(array('$where' => 'javascript'), $expr->getQuery());
        $this->assertNull($expr->getCurrentField());
    }

    public function testWithinBox()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->withinBox(1, 2, 3, 4));
        $this->assertEquals(array('$within' => array('$box' => array(array(1, 2), array(3, 4)))), $expr->getQuery());
    }

    public function testWithinCenter()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->withinCenter(1, 2, 3));
        $this->assertEquals(array('$within' => array('$center' => array(array(1, 2), 3))), $expr->getQuery());
    }

    public function testWithinCenterSphere()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->withinCenterSphere(1, 2, 3));
        $this->assertEquals(array('$within' => array('$centerSphere' => array(array(1, 2), 3))), $expr->getQuery());
    }

    public function testWithinPolygon()
    {
        $expr = new Expr();
        $expectedQuery = array('$within' => array('$polygon' => array(array(0, 0), array(1, 1), array(1, 0))));

        $this->assertSame($expr, $expr->withinPolygon(array(0, 0), array(1, 1), array(1, 0)));
        $this->assertEquals($expectedQuery, $expr->getQuery());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithinPolygonRequiresAtLeastThreePoints()
    {
        $expr = new Expr();
        $expr->withinPolygon(array(0, 0), array(1, 1));
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

        $this->assertSame($expr, $expr->in(array('value1', 'value2')));
        $this->assertEquals(array('$in' => array('value1', 'value2')), $expr->getQuery());
    }

    public function testInWillStripKeysToYieldBsonArray()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->in(array(1 => 'value1', 'some' => 'value2')));
        $this->assertEquals(array('$in' => array('value1', 'value2')), $expr->getQuery());
    }

    public function testNotIn()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->notIn(array('value1', 'value2')));
        $this->assertEquals(array('$nin' => array('value1', 'value2')), $expr->getQuery());
    }

    public function testNotInWillStripKeysToYieldBsonArray()
    {
        $expr = new Expr();

        $this->assertSame($expr, $expr->notIn(array(1 => 'value1', 'some' => 'value2')));
        $this->assertEquals(array('$nin' => array('value1', 'value2')), $expr->getQuery());
    }
}
