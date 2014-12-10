<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\CommandCursor;

class CommandCursorTest extends \PHPUnit_Framework_TestCase
{
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
            ->will($this->returnValue(array('info')));

        $commandCursor = new CommandCursor($mongoCommandCursor);
        $this->assertEquals(array('info'), $commandCursor->info());
    }

    public function testTimeout()
    {
        if (version_compare(phpversion('mongo'), '1.6.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.6.0');
        }

        $mongoCommandCursor = $this->getMockMongoCommandCursor();

        $mongoCommandCursor->expects($this->once())
            ->method('timeout')
            ->with(1000);

        $commandCursor = new CommandCursor($mongoCommandCursor);
        $this->assertSame($commandCursor, $commandCursor->timeout(1000));
    }

    private function getMockMongoCommandCursor()
    {
        return $this->getMockBuilder('MongoCommandCursor')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
