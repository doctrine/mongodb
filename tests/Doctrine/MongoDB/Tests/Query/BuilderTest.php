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
            'out' => array('inline' => true),
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

    /**
     * @dataProvider provideProxiedExprMethods
     */
    public function testProxiedExprMethods($method, array $args = array())
    {
        $expr = $this->getMockExpr();
        $invocationMocker = $expr->expects($this->once())->method($method);
        call_user_func_array(array($invocationMocker, 'with'), $args);

        $qb = $this->getStubQueryBuilder();
        $qb->setExpr($expr);

        $this->assertSame($qb, call_user_func_array(array($qb, $method), $args));
    }

    public function provideProxiedExprMethods()
    {
        return array(
            'field()' => array('field', array('fieldName')),
            'equals()' => array('equals', array('value')),
            'where()' => array('where', array('this.fieldName == 1')),
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
            'mod()' => array('mod', array(2, 0)),
            'near()' => array('near', array(1, 2)),
            'nearSphere()' => array('nearSphere', array(1, 2)),
            'withinBox()' => array('withinBox', array(1, 2, 3, 4)),
            'withinCenter()' => array('withinCenter', array(1, 2, 3)),
            'withinCenterSphere()' => array('withinCenterSphere', array(1, 2, 3)),
            'withinPolygon()' => array('withinPolygon', array(array(0, 0), array(1, 1), array(1, 0))),
            'geoIntersects()' => array('geoIntersects', array($this->getMockGeometry())),
            'geoWithin()' => array('geoWithin', array($this->getMockGeometry())),
            'geoWithinBox()' => array('geoWithinBox', array(1, 2, 3, 4)),
            'geoWithinCenter()' => array('geoWithinCenter', array(1, 2, 3)),
            'geoWithinCenterSphere()' => array('geoWithinCenterSphere', array(1, 2, 3)),
            'geoWithinPolygon()' => array('geoWithinPolygon', array(array(0, 0), array(1, 1), array(1, 0))),
            'inc()' => array('inc', array(1)),
            'unsetField()' => array('unsetField'),
            'push() with value' => array('push', array('value')),
            'push() with Expr' => array('push', array($this->getMockExpr())),
            'pushAll()' => array('pushAll', array(array('value1', 'value2'))),
            'addToSet() with value' => array('addToSet', array('value')),
            'addToSet() with Expr' => array('addToSet', array($this->getMockExpr())),
            'addManyToSet()' => array('addManyToSet', array(array('value1', 'value2'))),
            'popFirst()' => array('popFirst'),
            'popLast()' => array('popLast'),
            'pull()' => array('pull', array('value')),
            'pullAll()' => array('pullAll', array(array('value1', 'value2'))),
            'addAnd() array' => array('addAnd', array(array())),
            'addAnd() Expr' => array('addAnd', array($this->getMockExpr())),
            'addOr() array' => array('addOr', array(array())),
            'addOr() Expr' => array('addOr', array($this->getMockExpr())),
            'addNor() array' => array('addNor', array(array())),
            'addNor() Expr' => array('addNor', array($this->getMockExpr())),
            'elemMatch() array' => array('elemMatch', array(array())),
            'elemMatch() Expr' => array('elemMatch', array($this->getMockExpr())),
            'not()' => array('not', array($this->getMockExpr())),
        );
    }

    /**
     * @dataProvider providePoint
     */
    public function testGeoNearWithSingleArgument($point, array $near, $spherical)
    {
        $expected = array(
            'near' => $near,
            'options' => array('spherical' => $spherical),
        );

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear($point));
        $this->assertEquals(Query::TYPE_GEO_NEAR, $qb->getType());
        $this->assertEquals($expected, $qb->debug('geoNear'));
    }

    public function providePoint()
    {
        $coordinates = array(0, 0);
        $json = array('type' => 'Point', 'coordinates' => $coordinates);

        return array(
            'legacy array' => array($coordinates, $coordinates, false),
            'GeoJSON array' => array($json, $json, true),
            'GeoJSON object' => array($this->getMockPoint($json), $json, true),
        );
    }

    public function testGeoNearWithBothArguments()
    {
        $expected = array(
            'near' => array(0, 0),
            'options' => array('spherical' => false),
        );

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear(array(0, 0)));
        $this->assertEquals(Query::TYPE_GEO_NEAR, $qb->getType());
        $this->assertEquals($expected, $qb->debug('geoNear'));
    }

    public function testDistanceMultipler()
    {
        $expected = array(
            'near' => array(0, 0),
            'options' => array('spherical' => false, 'distanceMultiplier' => 1),
        );

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear(0, 0)->distanceMultiplier(1));
        $this->assertEquals($expected, $qb->debug('geoNear'));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testDistanceMultiplerRequiresGeoNearCommand()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->distanceMultiplier(1);
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testMapReduceOptionsRequiresMapReduceCommand()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->mapReduceOptions(array());
    }

    public function testMaxDistanceWithGeoNearCommand()
    {
        $expected = array(
            'near' => array(0, 0),
            'options' => array('spherical' => false, 'maxDistance' => 5),
        );

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear(0, 0)->maxDistance(5));
        $this->assertEquals($expected, $qb->debug('geoNear'));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testOutRequiresMapReduceCommand()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->out('collection');
    }

    public function testSpherical()
    {
        $expected = array(
            'near' => array(0, 0),
            'options' => array('spherical' => true),
        );

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear(0, 0)->spherical(true));
        $this->assertEquals($expected, $qb->debug('geoNear'));
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testSphericalRequiresGeoNearCommand()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->spherical();
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

    public function testSetReadPreference()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->setReadPreference('secondary', array(array('dc' => 'east')));

        $this->assertEquals('secondary', $qb->debug('readPreference'));
        $this->assertEquals(array(array('dc' => 'east')), $qb->debug('readPreferenceTags'));
    }

    private function getStubQueryBuilder()
    {
        return new BuilderStub($this->getMockCollection());
    }

    private function getTestQueryBuilder()
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

    private function getMockGeometry()
    {
        return $this->getMockBuilder('GeoJson\Geometry\Geometry')
            ->disableOriginalConstructor()
            ->getMock();
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

    private function assertArrayHasKeyValue($expected, $array, $message = '')
    {
        foreach ((array) $expected as $key => $value) {
            $this->assertArrayHasKey($key, $expected, $message);
            $this->assertEquals($value, $expected[$key], $message);
        }
    }
}
