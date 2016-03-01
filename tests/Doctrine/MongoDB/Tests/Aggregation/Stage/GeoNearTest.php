<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\GeoNear;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class GeoNearTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testGeoNearStage()
    {
        $geoNearStage = new GeoNear($this->getTestAggregationBuilder(), 0, 0);
        $geoNearStage
            ->distanceField('distance')
            ->field('someField')
            ->equals('someValue');

        $stage = ['near' => [0, 0], 'spherical' => false, 'distanceField' => 'distance', 'query' => ['someField' => 'someValue']];
        $this->assertSame(['$geoNear' => $stage], $geoNearStage->getExpression());
    }

    public function testGeoNearFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->geoNear(0, 0)
            ->distanceField('distance')
            ->field('someField')
            ->equals('someValue');

        $stage = ['near' => [0, 0], 'spherical' => false, 'distanceField' => 'distance', 'query' => ['someField' => 'someValue']];
        $this->assertSame([['$geoNear' => $stage]], $builder->getPipeline());
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
        return [
            'distanceMultiplier' => ['distanceMultiplier', 15.0],
            'includeLocs' => ['includeLocs', 'dist.location'],
            'maxDistance' => ['maxDistance', 15.0],
            'minDistance' => ['minDistance', 15.0],
            'num' => ['num', 15],
            'uniqueDocs' => ['uniqueDocs', true],
        ];
    }

    public function testLimitDoesNotCreateExtraStage()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->geoNear(0, 0)
            ->limit(1);

        $stage = ['near' => [0, 0], 'spherical' => false, 'distanceField' => null, 'query' => [], 'num' => 1];
        $this->assertSame([['$geoNear' => $stage]], $builder->getPipeline());
    }
}
