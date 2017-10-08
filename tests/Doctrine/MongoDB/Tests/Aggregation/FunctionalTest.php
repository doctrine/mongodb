<?php

namespace Doctrine\MongoDB\Tests\Aggregation;

use Doctrine\MongoDB\Tests\DatabaseTestCase;

class FunctionalTest extends DatabaseTestCase
{
    public function testEmptyMatchStageDoesNotCauseError()
    {
        $result = $this->createAggregationBuilder()
            ->match()
            ->execute();

        $this->assertCount(0, $result);
    }

    public function testGeoNearWithEmptyQueryDoesNotCauseError()
    {
        if (version_compare($this->getServerVersion(), '3.4.0', '>=')) {
            $this->conn->selectDatabase('admin')->command(['setFeatureCompatibilityVersion' => '3.4']);
        }

        $this->getCollection()->ensureIndex(['location' => '2dsphere']);
        $result = $this->createAggregationBuilder()
            ->geoNear(0, 0)
                ->distanceField('distance')
                ->spherical()
            ->execute();

        $this->assertCount(0, $result);
    }

    public function testGraphLookupWithoutMatch()
    {
        if (version_compare($this->getServerVersion(), '3.4.0', '<')) {
            $this->markTestSkipped('$graphLookup is only available on server version 3.4 and later.');
        }

        $result = $this->createAggregationBuilder()
            ->graphLookup('employees')
                ->startWith('$reportsTo')
                ->connectFromField('reportsTo')
                ->connectToField('name')
                ->alias('reportingHierarchy')
            ->execute();

        $this->assertCount(0, $result);
    }

    /**
     * @return \Doctrine\MongoDB\Aggregation\Builder
     */
    protected function createAggregationBuilder()
    {
        return $this->getCollection()->createAggregationBuilder();
    }

    /**
     * @return \Doctrine\MongoDB\Collection
     */
    protected function getCollection()
    {
        $db = $this->conn->selectDatabase(self::$dbName);
        return $db->selectCollection('test');
    }
}
