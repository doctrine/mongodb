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

        $out = ['inline' => true];

        $qb = $this->getTestQueryBuilder()
            ->mapReduce($map, $reduce, $out, ['finalize' => $finalize]);

        $expectedMapReduce = [
            'map' => $map,
            'reduce' => $reduce,
            'out' => ['inline' => true],
            'options' => ['finalize' => $finalize],
        ];

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

        $expectedQueryArray = ['username' => 'jwage'];
        $expectedMapReduce = [
            'map' => $map,
            'reduce' => $reduce,
            'options' => ['finalize' => $finalize],
            'out' => ['inline' => true],
        ];

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

        $this->assertEquals(['$or' => [
            ['firstName' => 'Kris'],
            ['firstName' => 'Chris']
        ]], $qb->getQueryArray());
    }

    public function testThatAndAcceptsAnotherQuery()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->addAnd($qb->expr()->field('hits')->gte(1));
        $qb->addAnd($qb->expr()->field('hits')->lt(5));

        $this->assertEquals([
            '$and' => [
                ['hits' => ['$gte' => 1]],
                ['hits' => ['$lt' => 5]],
            ],
        ], $qb->getQueryArray());
    }

    public function testThatNorAcceptsAnotherQuery()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->addNor($qb->expr()->field('firstName')->equals('Kris'));
        $qb->addNor($qb->expr()->field('firstName')->equals('Chris'));

        $this->assertEquals(['$nor' => [
            ['firstName' => 'Kris'],
            ['firstName' => 'Chris']
        ]], $qb->getQueryArray());
    }

    public function testAddElemMatch()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->field('phonenumbers')->elemMatch($qb->expr()->field('phonenumber')->equals('6155139185'));
        $expected = ['phonenumbers' => [
            '$elemMatch' => ['phonenumber' => '6155139185']
        ]];
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testAddNot()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->field('username')->not($qb->expr()->in(['boo']));
        $expected = [
            'username' => [
                '$not' => [
                    '$in' => ['boo']
                ]
            ]
        ];
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testFindQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->where("function() { return this.username == 'boo' }");
        $expected = [
            '$where' => "function() { return this.username == 'boo' }"
        ];
        $this->assertEquals($expected, $qb->getQueryArray());
    }

    public function testUpsertUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->upsert(true)
            ->field('username')->set('jwage');

        $expected = [
            '$set' => [
                'username' => 'jwage'
            ]
        ];
        $this->assertEquals($expected, $qb->getNewObj());
        $this->assertTrue($qb->debug('upsert'));
    }

    public function testMultipleUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->multiple(true)
            ->field('username')->set('jwage');

        $expected = [
            '$set' => [
                'username' => 'jwage'
            ]
        ];
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

        $expected = [
            'username' => 'boo'
        ];
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = ['$set' => [
            'username' => 'jwage'
        ]];
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testIncUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('hits')->inc(5)
            ->field('username')->equals('boo');

        $expected = [
            'username' => 'boo'
        ];
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = ['$inc' => [
            'hits' => 5
        ]];
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testUnsetField()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('hits')->unsetField()
            ->field('username')->equals('boo');

        $expected = [
            'username' => 'boo'
        ];
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = ['$unset' => [
            'hits' => 1
        ]];
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testSetOnInsert()
    {
        $createDate = new \MongoDate();
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->upsert()
            ->field('username')->equals('boo')
            ->field('createDate')->setOnInsert($createDate);

        $expected = [
            'username' => 'boo'
        ];
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = ['$setOnInsert' => [
            'createDate' => $createDate
        ]];
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testDateRange()
    {
        $start = new \MongoDate(strtotime('1985-09-01 01:00:00'));
        $end = new \MongoDate(strtotime('1985-09-04'));
        $qb = $this->getTestQueryBuilder();
        $qb->field('createdAt')->range($start, $end);

        $expected = [
            'createdAt' => [
                '$gte' => $start,
                '$lt' => $end
            ]
        ];
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
    public function testProxiedExprMethods($method, array $args = [])
    {
        $expr = $this->getMockExpr();
        $invocationMocker = $expr->expects($this->once())->method($method);
        call_user_func_array([$invocationMocker, 'with'], $args);

        $qb = $this->getStubQueryBuilder();
        $qb->setExpr($expr);

        $this->assertSame($qb, call_user_func_array([$qb, $method], $args));
    }

    public function provideProxiedExprMethods()
    {
        return [
            'field()' => ['field', ['fieldName']],
            'equals()' => ['equals', ['value']],
            'where()' => ['where', ['this.fieldName == 1']],
            'in()' => ['in', [['value1', 'value2']]],
            'notIn()' => ['notIn', [['value1', 'value2']]],
            'notEqual()' => ['notEqual', ['value']],
            'gt()' => ['gt', [1]],
            'gte()' => ['gte', [1]],
            'lt()' => ['gt', [1]],
            'lte()' => ['gte', [1]],
            'range()' => ['range', [0, 1]],
            'size()' => ['size', [1]],
            'exists()' => ['exists', [true]],
            'type()' => ['type', [7]],
            'all()' => ['all', [['value1', 'value2']]],
            'maxDistance' => ['maxDistance', [5]],
            'minDistance' => ['minDistance', [5]],
            'mod()' => ['mod', [2, 0]],
            'near()' => ['near', [1, 2]],
            'nearSphere()' => ['nearSphere', [1, 2]],
            'withinBox()' => ['withinBox', [1, 2, 3, 4]],
            'withinCenter()' => ['withinCenter', [1, 2, 3]],
            'withinCenterSphere()' => ['withinCenterSphere', [1, 2, 3]],
            'withinPolygon()' => ['withinPolygon', [[0, 0], [1, 1], [1, 0]]],
            'geoIntersects()' => ['geoIntersects', [$this->getMockGeometry()]],
            'geoWithin()' => ['geoWithin', [$this->getMockGeometry()]],
            'geoWithinBox()' => ['geoWithinBox', [1, 2, 3, 4]],
            'geoWithinCenter()' => ['geoWithinCenter', [1, 2, 3]],
            'geoWithinCenterSphere()' => ['geoWithinCenterSphere', [1, 2, 3]],
            'geoWithinPolygon()' => ['geoWithinPolygon', [[0, 0], [1, 1], [1, 0]]],
            'inc()' => ['inc', [1]],
            'mul()' => ['mul', [1]],
            'unsetField()' => ['unsetField'],
            'setOnInsert()' => ['setOnInsert', [1]],
            'push() with value' => ['push', ['value']],
            'push() with Expr' => ['push', [$this->getMockExpr()]],
            'pushAll()' => ['pushAll', [['value1', 'value2']]],
            'addToSet() with value' => ['addToSet', ['value']],
            'addToSet() with Expr' => ['addToSet', [$this->getMockExpr()]],
            'addManyToSet()' => ['addManyToSet', [['value1', 'value2']]],
            'popFirst()' => ['popFirst'],
            'popLast()' => ['popLast'],
            'pull()' => ['pull', ['value']],
            'pullAll()' => ['pullAll', [['value1', 'value2']]],
            'addAnd() array' => ['addAnd', [[]]],
            'addAnd() Expr' => ['addAnd', [$this->getMockExpr()]],
            'addOr() array' => ['addOr', [[]]],
            'addOr() Expr' => ['addOr', [$this->getMockExpr()]],
            'addNor() array' => ['addNor', [[]]],
            'addNor() Expr' => ['addNor', [$this->getMockExpr()]],
            'elemMatch() array' => ['elemMatch', [[]]],
            'elemMatch() Expr' => ['elemMatch', [$this->getMockExpr()]],
            'not()' => ['not', [$this->getMockExpr()]],
            'language()' => ['language', ['en']],
            'caseSensitive()' => ['caseSensitive', [true]],
            'diacriticSensitive()' => ['diacriticSensitive', [true]],
            'text()' => ['text', ['foo']],
            'max()' => ['max', [1]],
            'min()' => ['min', [1]],
            'comment()' => ['comment', ['A comment explaining what the query does']],
            'bitsAllClear()' => ['bitsAllClear', [5]],
            'bitsAllSet()' => ['bitsAllSet', [5]],
            'bitsAnyClear()' => ['bitsAnyClear', [5]],
            'bitsAnySet()' => ['bitsAnySet', [5]],
        ];
    }

    /**
     * @dataProvider providePoint
     */
    public function testGeoNearWithSingleArgument($point, array $near, $spherical)
    {
        $expected = [
            'near' => $near,
            'options' => ['spherical' => $spherical],
        ];

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear($point));
        $this->assertEquals(Query::TYPE_GEO_NEAR, $qb->getType());
        $this->assertEquals($expected, $qb->debug('geoNear'));
    }

    public function providePoint()
    {
        $coordinates = [0, 0];
        $json = ['type' => 'Point', 'coordinates' => $coordinates];

        return [
            'legacy array' => [$coordinates, $coordinates, false],
            'GeoJSON array' => [$json, $json, true],
            'GeoJSON object' => [$this->getMockPoint($json), $json, true],
        ];
    }

    public function testGeoNearWithBothArguments()
    {
        $expected = [
            'near' => [0, 0],
            'options' => ['spherical' => false],
        ];

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear([0, 0]));
        $this->assertEquals(Query::TYPE_GEO_NEAR, $qb->getType());
        $this->assertEquals($expected, $qb->debug('geoNear'));
    }

    public function testDistanceMultipler()
    {
        $expected = [
            'near' => [0, 0],
            'options' => ['spherical' => false, 'distanceMultiplier' => 1],
        ];

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
        $qb->mapReduceOptions([]);
    }

    public function testMaxDistanceWithGeoNearCommand()
    {
        $expected = [
            'near' => [0, 0],
            'options' => ['spherical' => false, 'maxDistance' => 5],
        ];

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear(0, 0)->maxDistance(5));
        $this->assertEquals($expected, $qb->debug('geoNear'));
    }

    public function testMinDistanceWithGeoNearCommand()
    {
        $expected = [
            'near' => [0, 0],
            'options' => ['spherical' => false, 'minDistance' => 5],
        ];

        $qb = $this->getTestQueryBuilder();

        $this->assertSame($qb, $qb->geoNear(0, 0)->minDistance(5));
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
        $expected = [
            'near' => [0, 0],
            'options' => ['spherical' => true],
        ];

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
        call_user_func_array([$qb, 'select'], $args);

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
        call_user_func_array([$qb, 'exclude'], $args);

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

        return [
            'multiple arguments' => [
                ['foo', 'bar'],
                ['foo' => $project, 'bar' => $project],
            ],
            'no arguments' => [
                [],
                [],
            ],
            'array argument' => [
                [['foo', 'bar']],
                ['foo' => $project, 'bar' => $project],
            ],
            'empty array' => [
                [[]],
                [],
            ],
        ];
    }

    public function testSelectSliceWithCount()
    {
        $qb = $this->getTestQueryBuilder()
            ->selectSlice('tags', 10);

        $expected = ['tags' => ['$slice' => 10]];

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function testSelectSliceWithSkipAndLimit()
    {
        $qb = $this->getTestQueryBuilder()
            ->selectSlice('tags', -5, 5);

        $expected = ['tags' => ['$slice' => [-5, 5]]];

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function testSelectElemMatchWithArray()
    {
        $qb = $this->getTestQueryBuilder()
            ->selectElemMatch('addresses', ['state' => 'ny']);

        $expected = ['addresses' => ['$elemMatch' => ['state' => 'ny']]];

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function testSelectElemMatchWithExpr()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->selectElemMatch('addresses', $qb->expr()->field('state')->equals('ny'));

        $expected = ['addresses' => ['$elemMatch' => ['state' => 'ny']]];

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function testSelectMeta()
    {
        $qb = $this->getTestQueryBuilder()
            ->selectMeta('score', 'textScore');

        $expected = ['score' => ['$meta' => 'textScore']];

        $this->assertEquals($expected, $qb->debug('select'));
    }

    public function testSetReadPreference()
    {
        $qb = $this->getTestQueryBuilder();
        $qb->setReadPreference('secondary', [['dc' => 'east']]);

        $this->assertEquals('secondary', $qb->debug('readPreference'));
        $this->assertEquals([['dc' => 'east']], $qb->debug('readPreferenceTags'));
    }

    public function testSortWithFieldNameAndDefaultOrder()
    {
        $qb = $this->getTestQueryBuilder()
            ->sort('foo');

        $this->assertEquals(['foo' => 1], $qb->debug('sort'));
    }

    /**
     * @dataProvider provideSortOrders
     */
    public function testSortWithFieldNameAndOrder($order, $expectedOrder)
    {
        $qb = $this->getTestQueryBuilder()
            ->sort('foo', $order);

        $this->assertEquals(['foo' => $expectedOrder], $qb->debug('sort'));
    }

    public function provideSortOrders()
    {
        return [
            [1, 1],
            [-1, -1],
            ['asc', 1],
            ['desc', -1],
            ['ASC', 1],
            ['DESC', -1],
        ];
    }

    public function testSortWithArrayOfFieldNameAndOrderPairs()
    {
        $qb = $this->getTestQueryBuilder()
            ->sort(['foo' => 1, 'bar' => -1]);

        $this->assertEquals(['foo' => 1, 'bar' => -1], $qb->debug('sort'));
    }

    public function testSortMetaDoesProjectMissingField()
    {
        $qb = $this->getTestQueryBuilder()
            ->select('score')
            ->sortMeta('score', 'textScore');

        /* This will likely yield a server error, but sortMeta() should only set
         * the projection if it doesn't already exist.
         */
        $this->assertEquals(['score' => 1], $qb->debug('select'));
        $this->assertEquals(['score' => ['$meta' => 'textScore']], $qb->debug('sort'));
    }

    public function testSortMetaDoesNotProjectExistingField()
    {
        $qb = $this->getTestQueryBuilder()
            ->sortMeta('score', 'textScore');

        $this->assertEquals(['score' => ['$meta' => 'textScore']], $qb->debug('select'));
        $this->assertEquals(['score' => ['$meta' => 'textScore']], $qb->debug('sort'));
    }

    /**
     * @dataProvider provideCurrentDateOptions
     */
    public function testCurrentDateUpdateQuery($type)
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('lastUpdated')->currentDate($type)
            ->field('username')->equals('boo');

        $expected = [
            'username' => 'boo'
        ];
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = ['$currentDate' => [
            'lastUpdated' => ['$type' => $type]
        ]];
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public static function provideCurrentDateOptions()
    {
        return [
            ['date'],
            ['timestamp']
        ];
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testCurrentDateInvalidType()
    {
        $this->getTestQueryBuilder()
            ->update()
            ->field('lastUpdated')->currentDate('notADate');
    }

    public function testBitAndUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('flags')->bitAnd(15)
            ->field('username')->equals('boo');

        $expected = [
            'username' => 'boo'
        ];
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = ['$bit' => [
            'flags' => ['and' => 15]
        ]];
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testBitOrUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('flags')->bitOr(15)
            ->field('username')->equals('boo');

        $expected = [
            'username' => 'boo'
        ];
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = ['$bit' => [
            'flags' => ['or' => 15]
        ]];
        $this->assertEquals($expected, $qb->getNewObj());
    }

    public function testBitXorUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('flags')->bitXor(15)
            ->field('username')->equals('boo');

        $expected = [
            'username' => 'boo'
        ];
        $this->assertEquals($expected, $qb->getQueryArray());

        $expected = ['$bit' => [
            'flags' => ['xor' => 15]
        ]];
        $this->assertEquals($expected, $qb->getNewObj());
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
