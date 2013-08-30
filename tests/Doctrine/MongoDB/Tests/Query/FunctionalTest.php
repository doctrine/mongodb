<?php

namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Query;
use Doctrine\MongoDB\Tests\BaseTest;

/**
 * @group functional
 */
class FunctionalTest extends BaseTest
{
    public function testDistinctQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->distinct('count')
            ->field('username')->equals('distinct_test');

        $expected = array(
            'username' => 'distinct_test'
        );
        $query = $qb->getQuery();
        $this->assertInstanceOf('Doctrine\MongoDB\Query\Query', $query);
        $this->assertEquals(Query::TYPE_DISTINCT, $query->getType());
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
        $this->assertArrayHasKeyValue(array('ok' => 1), $qb->getQuery()->execute());
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
        $this->assertArrayHasKeyValue(array('ok' => 1), $query->execute());
    }

    public function testRemoveQuery()
    {
        $qb = $this->getTestQueryBuilder()
            ->remove()
            ->field('username')->equals('jwage');

        $this->assertEquals(Query::TYPE_REMOVE, $qb->getType());
        $this->assertArrayHasKeyValue(array('ok' => 1), $qb->getQuery()->execute());
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
