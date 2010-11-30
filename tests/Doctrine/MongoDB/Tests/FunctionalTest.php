<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\GridFSFile;

class FunctionalTest extends BaseTest
{
    public function testFunctional()
    {
        $db = $this->conn->selectDatabase('doctrine_mongodb');

        $coll = $db->selectCollection('users');

        $document = array('test' => 'jwage');
        $coll->insert($document);

        $coll->update(array('_id' => $document['_id']), array('$set' => array('test' => 'jon')));

        $cursor = $coll->find();
        $this->assertInstanceOf('Doctrine\MongoDB\Cursor', $cursor);
    }

    public function testFunctionalGridFS()
    {
        $db = $this->conn->selectDatabase('doctrine_mongodb');
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