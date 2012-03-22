<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\GridFSFile;

class FunctionalTest extends BaseTest
{
    public function testUpsertSetsId()
    {
        $db = $this->conn->selectDatabase(self::$dbName);
        $coll = $db->createCollection('test');
        $criteria = array();
        $newObj = array('$set' => array('test' => 'test'));
        $coll->upsert($criteria, $newObj);

        $check = $coll->findOne();

        $this->assertNotNull($coll->findOne());

        $coll->upsert(array('_id' => $check['_id']), array('$set' => array('boo' => 'test')));
        $this->assertEquals(1, $coll->find()->count());
        $check = $coll->findOne();
        $this->assertTrue(isset($check['boo']));
    }

    public function testMapReduce()
    {
        $data = array(
            array(
                'username' => 'jones',
                'likes' => 20,
                'text' => 'Hello world!'
            ),
            array(
                'username' => 'bob',
                'likes' => 100,
                'text' => 'Hello world!'
            ),
            array(
                'username' => 'bob',
                'likes' => 100,
                'text' => 'Hello world!'
            ),
        );

        $db = $this->conn->selectDatabase(self::$dbName);
        $coll = $db->createCollection('test');
        $coll->batchInsert($data);

        $map = 'function() {
            emit(this.username, { count: 1, likes: this.likes });
        }';

        $reduce = 'function(key, values) {
            var result = {count: 0, likes: 0};

            values.forEach(function(value) {
              result.count += value.count;
              result.likes += value.likes;
            });

            return result;
        }';

        $finalize = 'function (key, value) { value.test = "test"; return value; }';

        $db = $this->conn->selectDatabase(self::$dbName);
        $coll = $db->selectCollection('test');
        $qb = $coll->createQueryBuilder()
            ->map($map)->reduce($reduce)->finalize($finalize);
        $query = $qb->getQuery();
        $results = $query->execute();
        $this->assertEquals(2, $results->count());
        $result = $results->getSingleResult();
        $this->assertEquals(array('count' => 2.0, 'likes' => 200.0, 'test' => 'test'), $result['value']);
    }

    public function testIsFieldIndexed()
    {
        $db = $this->conn->selectDatabase(self::$dbName);

        $coll = $db->selectCollection('users');
        $this->assertFalse($coll->isFieldIndexed('test'));
        $coll->ensureIndex(array('test' => 1));
        $this->assertTrue($coll->isFieldIndexed('test'));
    }

    public function testFunctional()
    {
        $db = $this->conn->selectDatabase(self::$dbName);

        $coll = $db->selectCollection('users');

        $document = array('test' => 'jwage');
        $coll->insert($document);

        $coll->update(array('_id' => $document['_id']), array('$set' => array('test' => 'jon')));

        $cursor = $coll->find();
        $this->assertInstanceOf('Doctrine\MongoDB\Cursor', $cursor);
    }

    public function testFunctionalGridFS()
    {
        $db = $this->conn->selectDatabase(self::$dbName);
        $files = $db->getGridFS('files');
        $file = array(
            'title' => 'test file',
            'testing' => 'ok',
            'file' => new GridFSFile(__DIR__.'/FunctionalTest.php')
        );
        $files->insert($file, array('safe' => true));

        $this->assertTrue(isset($file['_id']));

        $path = __DIR__.'/BaseTest.php';
        $files->update(array('_id' => $file['_id']), array('$set' => array('title' => 'test', 'file' => new GridFSFile($path))));

        $file = $files->find()->getSingleResult();
        $this->assertInstanceOf('Doctrine\MongoDB\GridFSFile', $file['file']);
        $this->assertEquals(file_get_contents($path), $file['file']->getBytes());
    }
}