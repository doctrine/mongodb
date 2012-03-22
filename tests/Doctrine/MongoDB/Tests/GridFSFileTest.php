<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\GridFSFile;

class GridFSFileTest extends BaseTest
{
    public function testSetAndGetMongoGridFSFile()
    {
        $path = __DIR__.'/GridFSFileTest.php';
        $file = $this->getTestGridFSFile($path);
        $mockPHPGridFSFile = $this->getMockPHPGridFSFile();
        $file->setMongoGridFSFile($mockPHPGridFSFile);
        $this->assertEquals($mockPHPGridFSFile, $file->getMongoGridFSFile());
    }

    public function testIsDirty()
    {
        $file = $this->getTestGridFSFile();
        $this->assertFalse($file->isDirty());
        $file->isDirty(true);
        $this->assertTrue($file->isDirty());
        $file->isDirty(false);
        $this->assertFalse($file->isDirty());
    }

    public function testSetAndGetFilename()
    {
        $path = __DIR__.'/GridFSFileTest.php';
        $file = $this->getTestGridFSFile();
        $this->assertFalse($file->isDirty());
        $file->setFilename($path);
        $this->assertTrue($file->isDirty());
        $this->assertFalse($file->hasUnpersistedBytes());
        $this->assertTrue($file->hasUnpersistedFile());
        $this->assertEquals($path, $file->getFilename());
    }

    public function testSetBytes()
    {
        $file = $this->getTestGridFSFile();
        $file->setBytes('bytes');
        $this->assertTrue($file->isDirty());
        $this->assertTrue($file->hasUnpersistedBytes());
        $this->assertFalse($file->hasUnpersistedFile());
        $this->assertEquals('bytes', $file->getBytes());
    }

    public function testWriteWithSetBytes()
    {
        $file = $this->getTestGridFSFile();
        $file->setBytes('bytes');
        $path = realpath(sys_get_temp_dir()).'/doctrine_write_test';
        $file->write($path);
        $this->assertTrue(file_exists($path));
        $this->assertEquals('bytes', file_get_contents($path));
        unlink($path);
    }

    public function testWriteWithSetFilename()
    {
        $origPath = __DIR__.'/GridFSFileTest.php';
        $file = $this->getTestGridFSFile();
        $file->setFilename($origPath);
        $path = realpath(sys_get_temp_dir()).'/doctrine_write_test';
        $file->write($path);
        $this->assertTrue(file_exists($path));
        $this->assertEquals(file_get_contents($origPath), file_get_contents($path));
        unlink($path);
    }

    public function testGetSizeWithSetBytes()
    {
        $file = $this->getTestGridFSFile();
        $file->setBytes('bytes');
        $this->assertEquals(5, $file->getSize());
    }

    public function testGetSizeWithSetFilename()
    {
        $file = $this->getTestGridFSFile();
        $file->setFilename(__DIR__.'/file.txt');
        $this->assertEquals(22, $file->getSize());
    }

    public function testFunctional()
    {
        $db = $this->conn->selectDatabase(self::$dbName);

        $path = __DIR__.'/file.txt';
        $gridFS = $db->getGridFS();
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
        $db = $this->conn->selectDatabase(self::$dbName);
        $gridFS = $db->getGridFS();

        $metadata = array(
            'test' => 'file'
        );
        $file = $gridFS->storeFile(__DIR__.'/file.txt', $metadata);
        $this->assertInstanceOf('Doctrine\MongoDB\GridFSFile', $file);
        $this->assertTrue(isset($metadata['_id']));
    }

    private function getMockPHPGridFSFile()
    {
        return $this->getMock('MongoGridFSFile', array(), array(), '', false, false);
    }

    private function getTestGridFSFile($file = null)
    {
        return new GridFSFile($file);
    }
}