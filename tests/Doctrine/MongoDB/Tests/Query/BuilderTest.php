<?php

namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Builder;
use Doctrine\MongoDB\Query\Query;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
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
        $qb = $this->getTestQueryBuilder();
        $qb->addOr($qb->expr()->field('firstName')->equals('Kris'));
        $qb->addOr($qb->expr()->field('firstName')->equals('Chris'));

        $this->assertEquals(array('$or' => array(
            array('firstName' => 'Kris'),
            array('firstName' => 'Chris')
        )), $qb->getQueryArray());
    }

    public function testThatAndAcceptsAnotherQuery()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->addAnd($qb->expr()->field('hits')->gte(1));
        $qb->addAnd($qb->expr()->field('hits')->lt(5));

        $this->assertEquals(array(
            '$and' => array(
                array('hits' => array('$gte' => 1)),
                array('hits' => array('$lt' => 5)),
            ),
        ), $qb->getQueryArray());
    }

    public function testThatNorAcceptsAnotherQuery()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->addNor($qb->expr()->field('firstName')->equals('Kris'));
        $qb->addNor($qb->expr()->field('firstName')->equals('Chris'));

        $this->assertEquals(array('$nor' => array(
            array('firstName' => 'Kris'),
            array('firstName' => 'Chris')
        )), $qb->getQueryArray());
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

    public function testGeoWithinPolygon()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoWithinPolygon(
                array(array(0, 0), array(0, 10), array(10, 10), array(10, 0), array(0, 0)),
                array(array(1, 1), array(1, 2), array(2, 2), array(2, 1), array(1, 1))
            );

        $expected = array(
            'loc' => array(
                '$geoWithin' => array(
                    '$geometry' => array(
                        'type' => 'Polygon',
                        'coordinates' => array(
                            array(array(0, 0), array(0, 10), array(10, 10), array(10, 0), array(0, 0)),
                            array(array(1, 1), array(1, 2), array(2, 2), array(2, 1), array(1, 1)),
                        ),
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGeoWithinPolygonRequiresAtLeastFourPoints()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoWithinPolygon(array(array(0, 0), array(1, 1), array(2, 2)));
    }

    public function testGeoWithinBox()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoWithinBox(0, 0, 2, 2);

        $expected = array(
            'loc' => array(
                '$geoWithin' => array(
                    '$geometry' => array(
                        'type' => 'Polygon',
                        'coordinates' => array(array(
                            array(0, 0),
                            array(0, 2),
                            array(2, 2),
                            array(2, 0),
                            array(0, 0),
                        )),
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testGeoIntersectsPoint()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoIntersectsPoint(0, 0);

        $expected = array(
            'loc' => array(
                '$geoIntersects' => array(
                    '$geometry' => array(
                        'type' => 'Point',
                        'coordinates' => array(0, 0),
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testGeoIntersectsLine()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoIntersectsLine(array(0, 0), array(2, 0));

        $expected = array(
            'loc' => array(
                '$geoIntersects' => array(
                    '$geometry' => array(
                        'type' => 'LineString',
                        'coordinates' => array(
                            array(0, 0),
                            array(2, 0),
                        ),
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGeoIntersectsLineRequiresAtLeastTwoPoints()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoIntersectsLine(array(0, 0));
    }

    public function testGeoIntersectsPolygon()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoIntersectsPolygon(
                array(array(0, 0), array(0, 10), array(10, 10), array(10, 0), array(0, 0)),
                array(array(1, 1), array(1, 2), array(2, 2), array(2, 1), array(1, 1))
            );

        $expected = array(
            'loc' => array(
                '$geoIntersects' => array(
                    '$geometry' => array(
                        'type' => 'Polygon',
                        'coordinates' => array(
                            array(array(0, 0), array(0, 10), array(10, 10), array(10, 0), array(0, 0)),
                            array(array(1, 1), array(1, 2), array(2, 2), array(2, 1), array(1, 1)),
                        ),
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testGeoIntersectsPolygonRequiresAtLeastFourPoints()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoIntersectsPolygon(array(array(0, 0), array(1, 1), array(2, 2)));
    }

    public function testGeoIntersectsBox()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('loc')->geoIntersectsBox(0, 0, 2, 2);

        $expected = array(
            'loc' => array(
                '$geoIntersects' => array(
                    '$geometry' => array(
                        'type' => 'Polygon',
                        'coordinates' => array(array(
                            array(0, 0),
                            array(0, 2),
                            array(2, 2),
                            array(2, 0),
                            array(0, 0),
                        )),
                    ),
                ),
            ),
        );
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    /**
     * @dataProvider provideSelectProjections
     */
    public function testSelect(array $args, array $expected)
    {
        $qb = $this->getTestQueryBuilder();
        call_user_func_array(array($qb, 'select'), $args);

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function provideSelectProjections()
    {
        return $this->provideProjections(true);
    }

    /**
     * @dataProvider provideExcludeProjections
     */
    public function testExclude(array $args, array $expected)
    {
        $qb = $this->getTestQueryBuilder();
        call_user_func_array(array($qb, 'exclude'), $args);

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function provideExcludeProjections()
    {
        return $this->provideProjections(false);
    }

    /**
     * Provide arguments for select() and exclude() tests.
     *
     * @param bool $include Whether the field should be included or excluded
     * @return array
     */
    private function provideProjections($include)
    {
        $project = $include ? 1 : 0;

        return array(
            'multiple arguments' => array(
                array('foo', 'bar'),
                array('foo' => $project, 'bar' => $project),
            ),
            'no arguments' => array(
                array(),
                array(),
            ),
            'array argument' => array(
                array(array('foo', 'bar')),
                array('foo' => $project, 'bar' => $project),
            ),
            'empty array' => array(
                array(array()),
                array(),
            ),
        );
    }

    public function testSelectSliceWithCount()
    {
        $qb = $this->getTestQueryBuilder()
            ->selectSlice('tags', 10);

        $expected = array('tags' => array('$slice' => 10));

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function testSelectSliceWithSkipAndLimit()
    {
        $qb = $this->getTestQueryBuilder()
            ->selectSlice('tags', -5, 5);

        $expected = array('tags' => array('$slice' => array(-5, 5)));

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function testSelectElemMatchWithArray()
    {
        $qb = $this->getTestQueryBuilder()
            ->selectElemMatch('addresses', array('state' => 'ny'));

        $expected = array('addresses' => array('$elemMatch' => array('state' => 'ny')));

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function testSelectElemMatchWithExpr()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->selectElemMatch('addresses', $qb->expr()->field('state')->equals('ny'));

        $expected = array('addresses' => array('$elemMatch' => array('state' => 'ny')));

        $this->assertEquals($expected, $qb->debug('select'));
    }

    private function getTestQueryBuilder()
    {
        return new Builder($this->getMockDatabase(), $this->getMockCollection(), '$');
    }

    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockDatabase()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
