<?php

namespace Doctrine\MongoDB\Tests;

class CursorFunctionalTest extends DatabaseTestCase
{
    public function testRecreate()
    {
        $db = $this->conn->selectDatabase(self::$dbName);
        $coll = $db->selectCollection('users');

        $doc1 = ['test' => 'test'];
        $coll->insert($doc1);

        $doc2 = ['test' => 'test'];
        $coll->insert($doc2);

        $cursor = $coll->find(['test' => 'test']);
        $cursor->limit(1);

        $this->assertEquals(1, $cursor->count(true));
        $this->assertEquals(2, $cursor->count());

        $cursor->recreate();
        $this->assertEquals(1, $cursor->count(true));
        $this->assertEquals(2, $cursor->count());
    }

    public function testGetSingleResult()
    {
        $db = $this->conn->selectDatabase(self::$dbName);
        $coll = $db->selectCollection('users');

        $doc1 = ['test' => 'test', 'doc' => 1];
        $coll->insert($doc1);

        $doc2 = ['test' => 'test', 'doc' => 2];
        $coll->insert($doc2);

        $cursor = $coll->find(['test' => 'test']);
        $doc = $cursor->getSingleResult();
        $this->assertEquals(['_id' => $doc1['_id'], 'test' => 'test', 'doc' => 1], $doc);
    }
}
