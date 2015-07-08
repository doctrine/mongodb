<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\GeoNear;

class GeoNearTest extends \PHPUnit_Framework_TestCase
{
    public function testGeoNearStage()
    {
        $geoNearStage = new GeoNear($this->getTestAggregationBuilder(), 0, 0);
        $geoNearStage
            ->distanceField('distance')
            ->field('someField')
            ->equals('someValue');

        $stage = array('near' => array(0, 0), 'spherical' => false, 'distanceField' => 'distance', 'query' => array('someField' => 'someValue'));
        $this->assertSame(array('$geoNear' => $stage), $geoNearStage->getExpression());
    }

    public function testGeoNearFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->geoNear(0, 0)
            ->distanceField('distance')
            ->field('someField')
            ->equals('someValue');

        $stage = array('near' => array(0, 0), 'spherical' => false, 'distanceField' => 'distance', 'query' => array('someField' => 'someValue'));
        $this->assertSame(array(array('$geoNear' => $stage)), $builder->getPipeline());
    }

    /**
     * @dataProvider provideOptionalSettings
     */
    public function testOptionalSettings($field, $value)
    {
        $geoNearStage = new GeoNear($this->getTestAggregationBuilder(), 0, 0);

        $pipeline = $geoNearStage->getExpression();
        $this->assertArrayNotHasKey($field, $pipeline['$geoNear']);

        $geoNearStage->$field($value);
        $pipeline = $geoNearStage->getExpression();

        $this->assertSame($value, $pipeline['$geoNear'][$field]);
    }

    public static function provideOptionalSettings()
    {
        return array(
            'distanceMultiplier' => array('distanceMultiplier', 15.0),
            'includeLocs' => array('includeLocs', 'dist.location'),
            'maxDistance' => array('maxDistance', 15.0),
            'num' => array('num', 15),
            'uniqueDocs' => array('uniqueDocs', true),
        );
    }

    public function testLimitDoesNotCreateExtraStage()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->geoNear(0, 0)
            ->limit(1);

        $stage = array('near' => array(0, 0), 'spherical' => false, 'distanceField' => null, 'query' => array(), 'num' => 1);
        $this->assertSame(array(array('$geoNear' => $stage)), $builder->getPipeline());
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
}
