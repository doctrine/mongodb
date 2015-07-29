<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Match;

class MatchTest extends \PHPUnit_Framework_TestCase
{
    public function testMatchStage()
    {
        $matchStage = new Match($this->getTestAggregationBuilder());
        $matchStage
            ->field('someField')
            ->equals('someValue');

        $this->assertSame(array('$match' => array('someField' => 'someValue')), $matchStage->getExpression());
    }

    public function testMatchFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->match()
            ->field('someField')
            ->equals('someValue');

        $this->assertSame(array(array('$match' => array('someField' => 'someValue'))), $builder->getPipeline());
    }

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
        return array(
            'field()' => array('field', array('fieldName')),
            'equals()' => array('equals', array('value')),
            'in()' => array('in', array(array('value1', 'value2'))),
            'notIn()' => array('notIn', array(array('value1', 'value2'))),
            'notEqual()' => array('notEqual', array('value')),
            'gt()' => array('gt', array(1)),
            'gte()' => array('gte', array(1)),
            'lt()' => array('gt', array(1)),
            'lte()' => array('gte', array(1)),
            'range()' => array('range', array(0, 1)),
            'size()' => array('size', array(1)),
            'exists()' => array('exists', array(true)),
            'type()' => array('type', array(7)),
            'all()' => array('all', array(array('value1', 'value2'))),
            'maxDistance' => array('maxDistance', array(5)),
            'minDistance' => array('minDistance', array(5)),
            'mod()' => array('mod', array(2, 0)),
            'geoIntersects()' => array('geoIntersects', array($this->getMockGeometry())),
            'geoWithin()' => array('geoWithin', array($this->getMockGeometry())),
            'geoWithinBox()' => array('geoWithinBox', array(1, 2, 3, 4)),
            'geoWithinCenter()' => array('geoWithinCenter', array(1, 2, 3)),
            'geoWithinCenterSphere()' => array('geoWithinCenterSphere', array(1, 2, 3)),
            'geoWithinPolygon()' => array('geoWithinPolygon', array(array(0, 0), array(1, 1), array(1, 0))),
            'addAnd() array' => array('addAnd', array(array())),
            'addAnd() Expr' => array('addAnd', array($this->getMockExpr())),
            'addOr() array' => array('addOr', array(array())),
            'addOr() Expr' => array('addOr', array($this->getMockExpr())),
            'addNor() array' => array('addNor', array(array())),
            'addNor() Expr' => array('addNor', array($this->getMockExpr())),
            'not()' => array('not', array($this->getMockExpr())),
            'language()' => array('language', array('en')),
            'text()' => array('text', array('foo')),
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
        return $this->getMockBuilder('Doctrine\MongoDB\Query\Expr')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getStubStage()
    {
        return new MatchStub($this->getTestAggregationBuilder());
    }

    private function getMockGeometry()
    {
        return $this->getMockBuilder('GeoJson\Geometry\Geometry')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
