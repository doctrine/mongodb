<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\GridFSFile;

class GridFSFileTest extends DatabaseTestCase
{
    public function testIsDirty()
    {
        $file = new GridFSFile();
        $this->assertFalse($file->isDirty());
        $file->isDirty(true);
        $this->assertTrue($file->isDirty());
        $file->isDirty(false);
        $this->assertFalse($file->isDirty());
    }

    public function testSetAndGetBytes()
    {
        $file = new GridFSFile();
        $file->setBytes('bytes');
        $this->assertTrue($file->isDirty());
        $this->assertTrue($file->hasUnpersistedBytes());
        $this->assertFalse($file->hasUnpersistedFile());
        $this->assertEquals('bytes', $file->getBytes());
    }

    public function testSetAndGetFilename()
    {
        $file = new GridFSFile();
        $this->assertFalse($file->isDirty());
        $file->setFilename(__FILE__);
        $this->assertTrue($file->isDirty());
        $this->assertFalse($file->hasUnpersistedBytes());
        $this->assertTrue($file->hasUnpersistedFile());
        $this->assertEquals(__FILE__, $file->getFilename());
    }

    public function testSetAndGetMongoGridFSFile()
    {
        $mongoGridFSFile = $this->getMockMongoGridFSFile();

        $file = new GridFSFile();
        $file->setMongoGridFSFile($mongoGridFSFile);
        $this->assertSame($mongoGridFSFile, $file->getMongoGridFSFile());
    }

    public function testGetBytesWithSetBytes()
    {
        $file = new GridFSFile();
        $file->setBytes('bytes');
        $this->assertEquals('bytes', $file->getBytes());
    }

    public function testGetBytesWithSetFilename()
    {
        $file = new GridFSFile();
        $file->setFilename(__FILE__);
        $this->assertStringEqualsFile(__FILE__, $file->getBytes());
    }

    public function testGetBytesWithSetMongoGridFSFile()
    {
        $mongoGridFSFile = $this->getMockMongoGridFSFile();
        $mongoGridFSFile->expects($this->once())
            ->method('getBytes')
            ->will($this->returnValue('bytes'));

        $file = new GridFSFile();
        $file->setMongoGridFSFile($mongoGridFSFile);
        $this->assertEquals('bytes', $file->getBytes());
    }

    public function testGetBytesWithEmptyState()
    {
        $file = new GridFSFile();
        $this->assertNull($file->getBytes());
    }

    public function testWriteWithSetBytes()
    {
        $path = tempnam(sys_get_temp_dir(), 'doctrine_write_test');
        $this->assertNotEquals(false, $path);

        $file = new GridFSFile();
        $file->setBytes('bytes');
        $file->write($path);
        $this->assertStringEqualsFile($path, 'bytes');
        unlink($path);
    }

    public function testWriteWithSetFilename()
    {
        $path = tempnam(sys_get_temp_dir(), 'doctrine_write_test');
        $this->assertNotEquals(false, $path);

        $file = new GridFSFile();
        $file->setFilename(__FILE__);
        $file->write($path);
        $this->assertFileEquals(__FILE__, $path);
        unlink($path);
    }

    public function testWriteWithSetMongoGridFSFile()
    {
        $mongoGridFSFile = $this->getMockMongoGridFSFile();
        $mongoGridFSFile->expects($this->once())
            ->method('write')
            ->with('filename');

        $file = new GridFSFile();
        $file->setMongoGridFSFile($mongoGridFSFile);
        $file->write('filename');
    }

    /**
     * @expectedException BadMethodCallException
     */
    public function testWriteWithEmptyState()
    {
        $file = new GridFSFile();
        $file->write('filename');
    }

    public function testGetSizeWithSetBytes()
    {
        $file = new GridFSFile();
        $file->setBytes('bytes');
        $this->assertEquals(5, $file->getSize());
    }

    public function testGetSizeWithSetFilename()
    {
        $file = new GridFSFile();
        $file->setFilename(__FILE__);
        $this->assertEquals(filesize(__FILE__), $file->getSize());
    }

    public function testGetSizeWithSetMongoGridFSFile()
    {
        $mongoGridFSFile = $this->getMockMongoGridFSFile();
        $mongoGridFSFile->expects($this->once())
            ->method('getSize')
            ->will($this->returnValue(200));

        $file = new GridFSFile();
        $file->setMongoGridFSFile($mongoGridFSFile);
        $this->assertEquals(200, $file->getSize());
    }

    public function testGetSizeWithEmptyState()
    {
        $file = new GridFSFile();
        $this->assertEquals(0, $file->getSize());
    }

    private function getMockMongoGridFSFile()
    {
        return $this->getMockBuilder('MongoGridFSFile')
                    ->disableOriginalConstructor()
                    ->getMock();
    }
}
