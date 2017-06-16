<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\CommandCursor;

class CommandCursorTest extends TestCase
{
    public function setUp()
    {
        if ( ! class_exists('MongoCommandCursor')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCommandCursor');
        }
    }

    public function testBatchSize()
    {
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('batchSize')
            ->with(10);

        $commandCursor = new CommandCursor($mongoCommandCursor);
        $this->assertSame($commandCursor, $commandCursor->batchSize(10));
    }

    public function testDead()
    {
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('dead')
            ->will($this->returnValue(true));

        $commandCursor = new CommandCursor($mongoCommandCursor);
        $this->assertTrue($commandCursor->dead());
    }

    public function testGetMongoCommandCursor()
    {
        $mongoCommandCursor = $this->getMockMongoCommandCursor();
        $commandCursor = new CommandCursor($mongoCommandCursor);
        $this->assertSame($mongoCommandCursor, $commandCursor->getMongoCommandCursor());
    }

    public function testInfo()
    {
        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('info')
            ->will($this->returnValue(['info']));

        $commandCursor = new CommandCursor($mongoCommandCursor);
        $this->assertEquals(['info'], $commandCursor->info());
    }

    public function testTimeout()
    {
        if ( ! method_exists('MongoCommandCursor', 'timeout')) {
            $this->markTestSkipped('This test is not applicable to drivers without MongoCommandCursor::timeout()');
        }

        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('timeout')
            ->with(1000);

        $commandCursor = new CommandCursor($mongoCommandCursor);
        $this->assertSame($commandCursor, $commandCursor->timeout(1000));
    }

    /**
     * @expectedException \BadMethodCallException
     */
    public function testTimeoutShouldThrowExceptionForOldDrivers()
    {
        if (method_exists('MongoCommandCursor', 'timeout')) {
            $this->markTestSkipped('This test is not applicable to drivers with MongoCommandCursor::timeout()');
        }

        $commandCursor = new CommandCursor($this->getMockMongoCommandCursor());
        $commandCursor->timeout(1000);
    }

    private function getMockMongoCommandCursor()
    {
        return $this->getMockBuilder('MongoCommandCursor')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
