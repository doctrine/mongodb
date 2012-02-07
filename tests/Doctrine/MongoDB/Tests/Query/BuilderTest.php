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

    public function testMapReduceQuery()
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
            ->map($map)->reduce($reduce)->finalize($finalize)
            ->field('username')->equals('jwage');

        $this->assertEquals(Query::TYPE_MAP_REDUCE, $qb->getType());
        $expected = array(
            'username' => 'jwage'
        );
        $this->assertEquals($expected, $qb->getQueryArray());
        $this->assertEquals(array('map' => $map, 'options' => array('finalize' => $finalize), 'reduce' => $reduce), $qb->debug('mapReduce'));
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

    public function testGeoLocationQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('x')->near(1)
            ->field('y')->near(2)
            ->field('username')->equals('jwage');

        $this->assertEquals(Query::TYPE_GEO_LOCATION, $qb->getType());
        $expected = array(
            'username' => 'jwage'
        );
        $this->assertEquals($expected, $qb->getQueryArray());
        $this->assertInstanceOf('Doctrine\MongoDB\ArrayIterator', $qb->getQuery()->execute());
    }

    public function testGroupQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->group(array(), array());

        $this->assertEquals(Query::TYPE_GROUP, $qb->getType());
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
        $this->assertTrue($qb->getQuery()->execute());
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
        $this->assertTrue($query->execute());
    }

    public function testRemoveQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->remove()
            ->field('username')->equals('jwage');

        $this->assertEquals(Query::TYPE_REMOVE, $qb->getType());
        $this->assertTrue($qb->getQuery()->execute());
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

    public function testGroup()
    {
        $qb = $this->getTestQueryBuilder()
            ->group(array(), array('count' => 0))
            ->reduce('function (obj, prev) { prev.count++; }');

        $expected = array(
            'initial' => array(
                'count' => 0
            ),
            'keys' => array()
        );
        $this->assertEquals($expected, $qb->debug('group'));

        $expected = array(
            'map' => null,
            'options' => array(),
            'reduce' => 'function (obj, prev) { prev.count++; }');
        $this->assertEquals($expected, $qb->debug('mapReduce'));
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

    private function getTestQueryBuilder()
    {
        return $this->conn->selectCollection('db', 'users')->createQueryBuilder();
    }
}