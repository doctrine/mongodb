<?php

namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Tests\BaseTest;
use Doctrine\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Query;

class BuilderTest extends BaseTest
{
    public function testDistinctFieldQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->distinct('count')
            ->field('username')->equals('distinct_test');

        $expected = array(
            'username' => 'distinct_test'
        );
        $query = $qb->getQuery();
        $this->assertInstanceOf('Doctrine\MongoDB\Query\Query', $query);
        $this->assertEquals(Query::TYPE_DISTINCT_FIELD, $query->getType());
        $this->assertEquals($expected, $qb->getQueryArray());
        $this->assertInstanceof('Doctrine\MongoDB\ArrayIterator', $query->execute());
    }

    public function testFindAndRemoveQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->findAndRemove()
            ->field('username')->equals('jwage');

        $this->assertEquals(Query::TYPE_FIND_AND_REMOVE, $qb->getType());
        $expected = array(
            'username' => 'jwage'
        );
        $this->assertEquals($expected, $qb->getQueryArray());
        $this->assertNull($qb->getQuery()->execute());
    }

    public function testMapReduceQueryWithSingleMethod()
    {
        $map = 'function() {
            for(i = 0; i <= this.options.length; i++) {
                emit(this.name, { count: 1 });
            }
        }';

        $reduce = 'function(product, values) {
            var total = 0
            values.forEach(function(value){
                total+= value.count;
            });
            return {
                product: product,
                options: total,
                test: values
            };
        }';

        $finalize = 'function (key, value) { return value; }';

        $out = array('inline' => true);

        $qb = $this->getTestQueryBuilder()
            ->mapReduce($map, $reduce, $out, array('finalize' => $finalize));

        $expectedMapReduce = array(
            'map' => $map,
            'reduce' => $reduce,
            'out' => array('inline' => true),
            'options' => array('finalize' => $finalize),
        );

        $this->assertEquals(Query::TYPE_MAP_REDUCE, $qb->getType());
        $this->assertEquals($expectedMapReduce, $qb->debug('mapReduce'));
    }

    public function testMapReduceQueryWithMultipleMethodsAndQueryArray()
    {
        $map = 'function() {
            for(i = 0; i <= this.options.length; i++) {
                emit(this.name, { count: 1 });
            }
        }';

        $reduce = 'function(product, values) {
            var total = 0
            values.forEach(function(value){
                total+= value.count;
            });
            return {
                product: product,
                options: total,
                test: values
            };
        }';

        $finalize = 'function (key, value) { return value; }';

        $qb = $this->getTestQueryBuilder()
            ->map($map)
            ->reduce($reduce)
            ->finalize($finalize)
            ->field('username')->equals('jwage');

        $expectedQueryArray = array('username' => 'jwage');
        $expectedMapReduce = array(
            'map' => $map,
            'reduce' => $reduce,
            'options' => array('finalize' => $finalize),
        );

        $this->assertEquals(Query::TYPE_MAP_REDUCE, $qb->getType());
        $this->assertEquals($expectedQueryArray, $qb->getQueryArray());
        $this->assertEquals($expectedMapReduce, $qb->debug('mapReduce'));
    }

    public function testFindAndUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->findAndRemove()
            ->field('username')->equals('jwage');

        $this->assertEquals(Query::TYPE_FIND_AND_REMOVE, $qb->getType());
        $expected = array(
            'username' => 'jwage'
        );
        $this->assertEquals($expected, $qb->getQueryArray());

        $query = $qb->getQuery();
        $this->assertEquals(Query::TYPE_FIND_AND_REMOVE, $query->getType());
        $this->assertNull($query->execute());
    }

    public function testGroupQueryWithSingleMethod()
    {
        $keys = array();
        $initial = array('count' => 0, 'sum' => 0);
        $reduce = 'function(obj, prev) { prev.count++; prev.sum += obj.a; }';
        $finalize = 'function(obj) { if (obj.count) { obj.avg = obj.sum / obj.count; } else { obj.avg = 0; } }';

        $qb = $this->getTestQueryBuilder()
            ->group($keys, $initial, $reduce, array('finalize' => $finalize));

        $expected = array(
            'keys' => $keys,
            'initial' => $initial,
            'reduce' => $reduce,
            'options' => array('finalize' => $finalize),
        );

        $this->assertEquals(Query::TYPE_GROUP, $qb->getType());
        $this->assertEquals($expected, $qb->debug('group'));
        $this->assertInstanceOf('Doctrine\MongoDB\ArrayIterator', $qb->getQuery()->execute());
    }

    public function testGroupQueryWithMultipleMethods()
    {
        $keys = array();
        $initial = array('count' => 0, 'sum' => 0);
        $reduce = 'function(obj, prev) { prev.count++; prev.sum += obj.a; }';
        $finalize = 'function(obj) { if (obj.count) { obj.avg = obj.sum / obj.count; } else { obj.avg = 0; } }';

        $qb = $this->getTestQueryBuilder()
            ->group($keys, $initial)
            ->reduce($reduce)
            ->finalize($finalize);

        $expected = array(
            'keys' => $keys,
            'initial' => $initial,
            'reduce' => $reduce,
            'options' => array('finalize' => $finalize),
        );

        $this->assertEquals(Query::TYPE_GROUP, $qb->getType());
        $this->assertEquals($expected, $qb->debug('group'));
        $this->assertInstanceOf('Doctrine\MongoDB\ArrayIterator', $qb->getQuery()->execute());
    }

    public function testInsertQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->insert()
            ->field('username')->set('jwage');

        $expected = array(
            'username' => 'jwage'
        );
        $this->assertEquals($expected, $qb->getNewObj());
        $this->assertEquals(Query::TYPE_INSERT, $qb->getType());
        $this->assertArrayHasKeyValue(array('ok' => 1.0), $qb->getQuery()->execute());
    }

    public function testUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('username')->set('jwage');

        $expected = array(
            '$set' => array(
                'username' => 'jwage'
            )
        );
        $this->assertEquals($expected, $qb->getNewObj());
        $this->assertEquals(Query::TYPE_UPDATE, $qb->getType());

        $query = $qb->getQuery();
        $this->assertEquals(Query::TYPE_UPDATE, $query->getType());
        $this->assertArrayHasKeyValue(array('ok' => 1.0), $query->execute());
    }

    public function testRemoveQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->remove()
            ->field('username')->equals('jwage');

        $this->assertEquals(Query::TYPE_REMOVE, $qb->getType());
        $this->assertArrayHasKeyValue(array('ok' => 1.0), $qb->getQuery()->execute());
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testFinalizeShouldThrowExceptionForUnsupportedQueryType()
    {
        $qb = $this->getTestQueryBuilder()->finalize('function() { }');
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testReduceShouldThrowExceptionForUnsupportedQueryType()
    {
        $qb = $this->getTestQueryBuilder()->reduce('function() { }');
    }

    public function testThatOrAcceptsAnotherQuery()
    {
        $coll = $this->conn->selectCollection('db', 'users');

        $qb = $coll->createQueryBuilder();
        $qb->addOr($qb->expr()->field('firstName')->equals('Kris'));
        $qb->addOr($qb->expr()->field('firstName')->equals('Chris'));

        $this->assertEquals(array('$or' => array(
            array('firstName' => 'Kris'),
            array('firstName' => 'Chris')
        )), $qb->getQueryArray());
    }

    public function testThatAndAcceptsAnotherQuery()
    {
        $coll = $this->conn->selectCollection('db', 'users');

        $qb = $coll->createQueryBuilder();
        $qb->addAnd($qb->expr()->field('hits')->gte(1));
        $qb->addAnd($qb->expr()->field('hits')->lt(5));

        $this->assertEquals(array(
            '$and' => array(
                array('hits' => array('$gte' => 1)),
                array('hits' => array('$lt' => 5)),
            ),
        ), $qb->getQueryArray());
    }

    public function testAddElemMatch()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->field('phonenumbers')->elemMatch($qb->expr()->field('phonenumber')->equals('6155139185'));
        $expected = array('phonenumbers' => array(
            '$elemMatch' => array('phonenumber' => '6155139185')
        ));
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testAddNot()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->field('username')->not($qb->expr()->in(array('boo')));
        $expected = array(
            'username' => array(
                '$not' => array(
                    '$in' => array('boo')
                )
            )
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testFindQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->where("function() { return this.username == 'boo' }");
        $expected = array(
            '$where' => "function() { return this.username == 'boo' }"
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testUpsertUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->upsert(true)
            ->field('username')->set('jwage');

        $expected = array(
            '$set' => array(
                'username' => 'jwage'
            )
        );
        $this->assertEquals($expected, $qb->getNewObj());
        $this->assertTrue($qb->debug('upsert'));
    }

    public function testMultipleUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->multiple(true)
            ->field('username')->set('jwage');

        $expected = array(
            '$set' => array(
                'username' => 'jwage'
            )
        );
        $this->assertEquals($expected, $qb->getNewObj());
        $this->assertTrue($qb->debug('multiple'));
    }

    public function testComplexUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('username')
            ->set('jwage')
            ->equals('boo');

        $expected = array(
            'username' => 'boo'
        );
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = array('$set' => array(
            'username' => 'jwage'
        ));
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testIncUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('hits')->inc(5)
            ->field('username')->equals('boo');

        $expected = array(
            'username' => 'boo'
        );
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = array('$inc' => array(
            'hits' => 5
        ));
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testUnsetField()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('hits')->unsetField()
            ->field('username')->equals('boo');

        $expected = array(
            'username' => 'boo'
        );
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = array('$unset' => array(
            'hits' => 1
        ));
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testDateRange()
    {
        $start = new \MongoDate(strtotime('1985-09-01 01:00:00'));
        $end = new \MongoDate(strtotime('1985-09-04'));
        $qb = $this->getTestQueryBuilder();
        $qb->field('createdAt')->range($start, $end);

        $expected = array(
            'createdAt' => array(
                '$gte' => $start,
                '$lt' => $end
            )
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testQueryIsIterable()
    {
        $qb = $this->getTestQueryBuilder();
        $query = $qb->getQuery();
        $this->assertInstanceOf('IteratorAggregate', $query);
        $this->assertInstanceOf('Doctrine\MongoDB\IteratorAggregate', $query);
    }

    public function testDeepClone()
    {
        $qb = $this->getTestQueryBuilder();

        $qb->field('username')->equals('jwage');

        $this->assertCount(1, $qb->getQueryArray());

        $qb2 = clone $qb;
        $qb2->field('firstName')->equals('Jon');

        $this->assertCount(1, $qb->getQueryArray());
    }

    public function testGeoNearQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->geoNear(50, 50)
            ->distanceMultiplier(2.5)
            ->maxDistance(5)
            ->spherical(true)
            ->field('type')->equals('restaurant')
            ->limit(10);

        $this->assertEquals(Query::TYPE_GEO_LOCATION, $qb->getType());

        $expectedQuery = array('type' => 'restaurant');
        $this->assertEquals($expectedQuery, $qb->getQueryArray());

        $geoNear = $qb->debug('geoNear');
        $this->assertEquals(array(50, 50), $geoNear['near']);
        $this->assertEquals(2.5, $geoNear['distanceMultiplier']);
        $this->assertEquals(5, $geoNear['maxDistance']);
        $this->assertEquals(true, $geoNear['spherical']);
        $this->assertEquals(10, $qb->debug('limit'));
        $this->assertInstanceOf('Doctrine\MongoDB\ArrayIterator', $qb->getQuery()->execute());
    }

    public function testNear()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->near(50, 50)->maxDistance(25);

        $expected = array('loc' => array('$near' => array(50, 50), '$maxDistance' => 25));
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testWithinBox()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->withinBox(0, 0, 2, 2);

        $expected = array(
            'loc' => array(
                '$within' => array(
                    '$box' => array(array(0, 0), array(2, 2)),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testWithinCenter()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->withinCenter(0, 0, 1);

        $expected = array(
            'loc' => array(
                '$within' => array(
                    '$center' => array(array(0, 0), 1),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testWithinPolygon()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->withinPolygon(array(0, 0), array(2, 0), array(0, 2));

        $expected = array(
            'loc' => array(
                '$within' => array(
                    '$polygon' => array(array(0, 0), array(2, 0), array(0, 2)),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testWithinPolygonRequiresAtLeastThreePoints()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->withinPolygon(array(0, 0), array(1, 1));
    }

    private function getTestQueryBuilder()
    {
        return $this->conn->selectCollection('db', 'users')->createQueryBuilder();
    }

    private function assertArrayHasKeyValue($expected, $array, $message = '')
    {
        foreach ((array) $expected as $key => $value) {
            $this->assertArrayHasKey($key, $expected, $message);
            $this->assertEquals($value, $expected[$key], $message);
        }
    }
}
