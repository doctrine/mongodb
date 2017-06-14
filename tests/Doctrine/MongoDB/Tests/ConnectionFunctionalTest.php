<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Connection;

class ConnectionFunctionalTest extends DatabaseTestCase
{
    public function testIsConnected()
    {
        if (! extension_loaded('mongo')) {
            $this->markTestSkipped('Test will not work with polyfills for ext-mongo');
        }

        $this->assertFalse($this->conn->isConnected());
        $this->conn->connect();
        $this->assertTrue($this->conn->isConnected());
        $this->conn->close();
        $this->assertFalse($this->conn->isConnected());
    }

    public function testDriverOptions()
    {
        if (! extension_loaded('mongo')) {
            $this->markTestSkipped('Test will not work with polyfills for ext-mongo');
        }

        $callCount = 0;
        $streamContext = stream_context_create([
            'mongodb' => [
                'log_cmd_delete' => function () use (&$callCount) {
                    $callCount++;
                },
            ],
        ]);

        $connection = new Connection(null, [], null, null, ['context' => $streamContext]);

        $connection->selectCollection('test', 'collection')->remove([]);

        $this->assertSame(1, $callCount);
    }
}
