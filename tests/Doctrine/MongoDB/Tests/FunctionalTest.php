<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\GridFSFile;

class FunctionalTest extends DatabaseTestCase
{
    public function testUpsertSetsId()
    {
        $db = $this->conn->selectDatabase(self::$dbName);
        $coll = $db->createCollection('test');
        $criteria = [];
        $newObj = ['$set' => ['test' => 'test']];
        $coll->upsert($criteria, $newObj);

        $check = $coll->findOne();

        $this->assertNotNull($coll->findOne());

        $coll->upsert(['_id' => $check['_id']], ['$set' => ['boo' => 'test']]);
        $this->assertEquals(1, $coll->find()->count());
        $check = $coll->findOne();
        $this->assertTrue(isset($check['boo']));
    }

    public function testMapReduce()
    {
        $data = [
            [
                'username' => 'jones',
                'likes' => 20,
                'text' => 'Hello world!'
            ],
            [
                'username' => 'bob',
                'likes' => 100,
                'text' => 'Hello world!'
            ],
            [
                'username' => 'bob',
                'likes' => 100,
                'text' => 'Hello world!'
            ],
        ];

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
        $this->assertEquals(['count' => 2.0, 'likes' => 200.0, 'test' => 'test'], $result['value']);
    }

    public function testIsFieldIndexed()
    {
        $db = $this->conn->selectDatabase(self::$dbName);

        $coll = $db->selectCollection('users');
        $this->assertFalse($coll->isFieldIndexed('test'));
        $coll->ensureIndex(['test' => 1]);
        $this->assertTrue($coll->isFieldIndexed('test'));
    }

    public function testFunctional()
    {
        $db = $this->conn->selectDatabase(self::$dbName);

        $coll = $db->selectCollection('users');

        $document = ['test' => 'jwage'];
        $coll->insert($document);

        $coll->update(['_id' => $document['_id']], ['$set' => ['test' => 'jon']]);

        $cursor = $coll->find();
        $this->assertInstanceOf('Doctrine\MongoDB\Cursor', $cursor);
    }

    public function testFunctionalGridFS()
    {
        $db = $this->conn->selectDatabase(self::$dbName);
        $files = $db->getGridFS('files');
        $file = [
            'title' => 'test file',
            'testing' => 'ok',
            'file' => new GridFSFile(__DIR__.'/FunctionalTest.php')
        ];
        $files->insert($file);

        $this->assertTrue(isset($file['_id']));

        $path = __DIR__.'/TestCase.php';
        $files->update(['_id' => $file['_id']], ['$set' => ['title' => 'test', 'file' => new GridFSFile($path)]]);

        $file = $files->find()->getSingleResult();
        $this->assertInstanceOf('Doctrine\MongoDB\GridFSFile', $file['file']);
        $this->assertEquals(file_get_contents($path), $file['file']->getBytes());
    }
}
