<?php

namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Expr;

class ExprTest extends \PHPUnit_Framework_TestCase
{
    public function testOperatorWithCurrentField()
    {
        $expr = new Expr('$');
        $expr->field('field');

        $this->assertSame($expr, $expr->operator('$op', 'value'));
        $this->assertEquals(array('field' => array('$op' => 'value')), $expr->getQuery());
    }

    public function testOperatorWithoutCurrentField()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->operator('$op', 'value'));
        $this->assertEquals(array('$op' => 'value'), $expr->getQuery());
    }

    public function testMaxDistanceWithNearAndGeoJsonPoint()
    {
        $json = array('type' => 'Point', 'coordinates' => array(1, 2));

        $expr = new Expr('$');
        $expr->near($this->getMockPoint($json));

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(array('$near' => array('$geometry' => $json, '$maxDistance' => 1)), $expr->getQuery());
    }

    public function testMaxDistanceWithNearAndLegacyCoordinates()
    {
        $expr = new Expr('$');
        $expr->near(1, 2);

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(array('$near' => array(1, 2), '$maxDistance' => 1), $expr->getQuery());
    }

    public function testMaxDistanceWithNearSphereAndGeoJsonPoint()
    {
        $json = array('type' => 'Point', 'coordinates' => array(1, 2));

        $expr = new Expr('$');
        $expr->nearSphere($this->getMockPoint($json));

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(array('$nearSphere' => array('$geometry' => $json, '$maxDistance' => 1)), $expr->getQuery());
    }

    public function testMaxDistanceWithNearSphereAndLegacyCoordinates()
    {
        $expr = new Expr('$');
        $expr->nearSphere(1, 2);

        $this->assertSame($expr, $expr->maxDistance(1));
        $this->assertEquals(array('$nearSphere' => array(1, 2), '$maxDistance' => 1), $expr->getQuery());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMaxDistanceRequiresNearOrNearSphereOperator()
    {
        $expr = new Expr('$');
        $expr->maxDistance(1);
    }

    public function testNearWithGeoJsonPoint()
    {
        $json = array('type' => 'Point', 'coordinates' => array(1, 2));
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->near($this->getMockPoint($json)));
        $this->assertEquals(array('$near' => array('$geometry' => $json)), $expr->getQuery());
    }

    public function testNearWithLegacyCoordinates()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->near(1, 2));
        $this->assertEquals(array('$near' => array(1, 2)), $expr->getQuery());
    }

    public function testNearSphereWithGeoJsonPoint()
    {
        $json = array('type' => 'Point', 'coordinates' => array(1, 2));
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->nearSphere($this->getMockPoint($json)));
        $this->assertEquals(array('$nearSphere' => array('$geometry' => $json)), $expr->getQuery());
    }

    public function testNearSphereWithLegacyCoordinates()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->nearSphere(1, 2));
        $this->assertEquals(array('$nearSphere' => array(1, 2)), $expr->getQuery());
    }

    public function testGeoWithin()
    {
        $json = array(
            'type' => 'Polygon',
            'coordinates' => array(array(array(0, 0), array(1, 1), array(1, 0), array(0, 0))),
        );

        $expr = new Expr('$');

        $this->assertSame($expr, $expr->geoWithin($this->getMockPolygon($json)));
        $this->assertEquals(array('$geoWithin' => array('$geometry' => $json)), $expr->getQuery());
    }

    public function testGeoIntersects()
    {
        $json = array('type' => 'Point', 'coordinates' => array(1, 2));
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->geoIntersects($this->getMockPoint($json)));
        $this->assertEquals(array('$geoIntersects' => array('$geometry' => $json)), $expr->getQuery());
    }

    public function testGeoWithinBox()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->geoWithinBox(1, 2, 3, 4));
        $this->assertEquals(array('$geoWithin' => array('$box' => array(array(1, 2), array(3, 4)))), $expr->getQuery());
    }

    public function testGeoWithinCenter()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->geoWithinCenter(1, 2, 3));
        $this->assertEquals(array('$geoWithin' => array('$center' => array(array(1, 2), 3))), $expr->getQuery());
    }

    public function testGeoWithinCenterSphere()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->geoWithinCenterSphere(1, 2, 3));
        $this->assertEquals(array('$geoWithin' => array('$centerSphere' => array(array(1, 2), 3))), $expr->getQuery());
    }

    public function testGeoWithinPolygon()
    {
        $expr = new Expr('$');
        $expectedQuery = array('$geoWithin' => array('$polygon' => array(array(0, 0), array(1, 1), array(1, 0))));

        $this->assertSame($expr, $expr->geoWithinPolygon(array(0, 0), array(1, 1), array(1, 0)));
        $this->assertEquals($expectedQuery, $expr->getQuery());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGeoWithinPolygonRequiresAtLeastThreePoints()
    {
        $expr = new Expr('$');
        $expr->geoWithinPolygon(array(0, 0), array(1, 1));
    }

    public function testWithinBox()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->withinBox(1, 2, 3, 4));
        $this->assertEquals(array('$within' => array('$box' => array(array(1, 2), array(3, 4)))), $expr->getQuery());
    }

    public function testWithinCenter()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->withinCenter(1, 2, 3));
        $this->assertEquals(array('$within' => array('$center' => array(array(1, 2), 3))), $expr->getQuery());
    }

    public function testWithinCenterSphere()
    {
        $expr = new Expr('$');

        $this->assertSame($expr, $expr->withinCenterSphere(1, 2, 3));
        $this->assertEquals(array('$within' => array('$centerSphere' => array(array(1, 2), 3))), $expr->getQuery());
    }

    public function testWithinPolygon()
    {
        $expr = new Expr('$');
        $expectedQuery = array('$within' => array('$polygon' => array(array(0, 0), array(1, 1), array(1, 0))));

        $this->assertSame($expr, $expr->withinPolygon(array(0, 0), array(1, 1), array(1, 0)));
        $this->assertEquals($expectedQuery, $expr->getQuery());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithinPolygonRequiresAtLeastThreePoints()
    {
        $expr = new Expr('$');
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
}
