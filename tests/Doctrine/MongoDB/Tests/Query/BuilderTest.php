<?php

namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Tests\BaseTest;
use Doctrine\MongoDB\Query\Builder;

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
        $this->assertInstanceOf('Doctrine\MongoDB\Query\DistinctFieldQuery', $qb->getQuery());
        $this->assertEquals($expected, $qb->getQueryArray());
        $this->assertInstanceof('Doctrine\MongoDB\ArrayIterator', $qb->getQuery()->execute());
    }

    public function testFindAndRemoveQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->findAndRemove()
            ->field('username')->equals('jwage');

        $this->assertEquals(Builder::TYPE_FIND_AND_REMOVE, $qb->getType());
        $expected = array(
            'username' => 'jwage'
        );
        $this->assertEquals($expected, $qb->getQueryArray());
        $this->assertTrue(is_array($qb->getQuery()->execute()));
    }

    public function testFindAndUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->findAndRemove()
            ->field('username')->equals('jwage');

        $this->assertEquals(Builder::TYPE_FIND_AND_REMOVE, $qb->getType());
        $expected = array(
            'username' => 'jwage'
        );
        $this->assertEquals($expected, $qb->getQueryArray());
        $this->assertInstanceOf('Doctrine\MongoDB\Query\FindAndRemoveQuery', $qb->getQuery());
        $this->assertTrue(is_array($qb->getQuery()->execute()));
    }

    public function testGeoLocationQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->field('x')->near(1)
            ->field('y')->near(2)
            ->field('username')->equals('jwage');

        $this->assertEquals(Builder::TYPE_GEO_LOCATION, $qb->getType());
        $expected = array(
            'username' => 'jwage'
        );
        $this->assertEquals($expected, $qb->getQueryArray());
        $this->assertInstanceOf('Doctrine\MongoDB\Query\GeoLocationFindQuery', $qb->getQuery());
        $this->assertInstanceOf('Doctrine\MongoDB\ArrayIterator', $qb->getQuery()->execute());
    }

    public function testGroupQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->group(array(), array());

        $this->assertEquals(Builder::TYPE_GROUP, $qb->getType());
        $this->assertInstanceOf('Doctrine\MongoDB\Query\GroupQuery', $qb->getQuery());
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
        $this->assertEquals(Builder::TYPE_INSERT, $qb->getType());
        $this->assertInstanceOf('Doctrine\MongoDB\Query\InsertQuery', $qb->getQuery());
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
        $this->assertEquals(Builder::TYPE_UPDATE, $qb->getType());
        $this->assertInstanceOf('Doctrine\MongoDB\Query\UpdateQuery', $qb->getQuery());
        $this->assertTrue($qb->getQuery()->execute());
    }

    public function testRemoveQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->remove()
            ->field('username')->equals('jwage');

        $this->assertEquals(Builder::TYPE_REMOVE, $qb->getType());
        $this->assertInstanceOf('Doctrine\MongoDB\Query\RemoveQuery', $qb->getQuery());
        $this->assertTrue($qb->getQuery()->execute());
    }

    public function testThatOrAcceptsAnotherQuery()
    {
        $coll = $this->conn->selectCollection('db', 'users');

        $expression1 = array('firstName' => 'Kris');
        $expression2 = array('firstName' => 'Chris');

        $qb = $coll->createQueryBuilder();
        $qb->addOr($qb->expr()->field('firstName')->equals('Kris'));
        $qb->addOr($qb->expr()->field('firstName')->equals('Chris'));

        $this->assertEquals(array('$or' => array(
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

    public function testComplexUpdateQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->update()
            ->field('username')
            ->set('jwage')
            ->equals('boo');

        $this->assertInstanceOf('Doctrine\MongoDB\Query\UpdateQuery', $qb->getQuery());

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
        $query = $qb->getQuery();

        $this->assertInstanceOf('Doctrine\MongoDB\Query\UpdateQuery', $qb->getQuery());

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

        $expected = array('reduce' => 'function (obj, prev) { prev.count++; }');
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
        $this->assertInstanceOf('Iterator', $query);
        $this->assertInstanceOf('Doctrine\MongoDB\Iterator', $query);
    }

    private function getTestQueryBuilder()
    {
        return $this->conn->selectCollection('db', 'users')->createQueryBuilder();
    }
}