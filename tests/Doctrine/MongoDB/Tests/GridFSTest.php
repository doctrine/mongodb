<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\GridFSFile;

class GridFSTest extends BaseTest
{
    public function testFunctional()
    {
        $gridFS = $this->getGridFS();

        $path = __DIR__.'/file.txt';
        $file = new GridFSFile($path);
        $document = array(
            'title' => 'Test Title',
            'file' => $file
        );
        $gridFS->insert($document);
        $id = $document['_id'];

        $document = $gridFS->findOne(array('_id' => $id));
        $file = $document['file'];

        $this->assertFalse($file->isDirty());
        $this->assertEquals($path, $file->getFilename());
        $this->assertEquals(file_get_contents($path), $file->getBytes());
        $this->assertEquals(22, $file->getSize());

        $tmpPath = realpath(sys_get_temp_dir()).'/doctrine_write_test';
        $file->write($tmpPath);
        $this->assertTrue(file_exists($path));
        $this->assertEquals(file_get_contents($path), file_get_contents($tmpPath));
        unlink($tmpPath);
    }

    public function testStoreFile()
    {
        $gridFS = $this->getGridFS();

        $metadata = array(
            'test' => 'file'
        );
        $file = $gridFS->storeFile(__DIR__.'/file.txt', $metadata);
        $this->assertInstanceOf('Doctrine\MongoDB\GridFSFile', $file);
        $this->assertTrue(isset($metadata['_id']));
    }

    public function testUpdate()
    {
        $gridFS = $this->getGridFS();

        $path = __DIR__.'/file.txt';
        $file = new GridFSFile($path);
        $document = array(
            'title' => 'Test Title',
            'file' => $file
        );
        $gridFS->insert($document);
        $id = $document['_id'];

        $document = $gridFS->findOne(array('_id' => $id));
        $file = $document['file'];

        $gridFS->update(array('_id' => $id), array('$pushAll' => array('test' => array(1, 2, 3))));
        $check = $gridFS->findOne(array('_id' => $id));
        $this->assertTrue(isset($check['test']));
        $this->assertEquals(3, count($check['test']));
        $this->assertEquals(array(1, 2, 3), $check['test']);

        $gridFS->update(array('_id' => $id), array('_id' => $id));
        $gridFS->update(array('_id' => $id), array('_id' => $id, 'boom' => true));
        $check = $gridFS->findOne(array('_id' => $id));
        $this->assertFalse(array_key_exists('test', $check));
        $this->assertTrue($check['boom']);
    }

    public function testUpsertDocumentWithoutFile()
    {
        $gridFS = $this->getGridFS();

        $gridFS->update(
            array('id' => 123),
            array('x' => 1),
            array('upsert' => true, 'multiple' => false)
        );

        $document = $gridFS->findOne();

        $this->assertNotNull($document);
        $this->assertNotEquals(123, $document['_id']);
        $this->assertEquals(1, $document['x']);
    }

    public function testUpsertDocumentWithoutFileWithId()
    {
        $gridFS = $this->getGridFS();

        $gridFS->update(
            array('x' => 1),
            array('_id' => 123),
            array('upsert' => true, 'multiple' => false)
        );

        $document = $gridFS->findOne(array('_id' => 123));

        $this->assertNotNull($document);
        $this->assertFalse(array_key_exists('x', $document));
    }

    public function testUpsertModifierWithoutFile()
    {
        $gridFS = $this->getGridFS();

        $gridFS->update(
            array('_id' => 123),
            array('$set' => array('x' => 1)),
            array('upsert' => true, 'multiple' => false)
        );

        $document = $gridFS->findOne(array('_id' => 123));

        $this->assertNotNull($document);
        $this->assertEquals(1, $document['x']);
    }

    public function testUpsert()
    {
        $gridFS = $this->getGridFS();

        $path = __DIR__.'/file.txt';
        $file = new GridFSFile($path);

        $newObj = array(
            '$set' => array(
                'title' => 'Test Title',
                'file' => $file,
            ),
        );
        $gridFS->update(array('_id' => 123), $newObj, array('upsert' => true, 'multiple' => false));

        $document = $gridFS->findOne(array('_id' => 123));

        $file = $document['file'];

        $this->assertFalse($file->isDirty());
        $this->assertEquals($path, $file->getFilename());
        $this->assertEquals(file_get_contents($path), $file->getBytes());
        $this->assertEquals(22, $file->getSize());
    }

    private function getGridFS()
    {
        return $this->conn->selectDatabase(self::$dbName)->getGridFS();
    }
}
