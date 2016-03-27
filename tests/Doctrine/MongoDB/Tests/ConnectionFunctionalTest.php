<?php

namespace Doctrine\MongoDB\Tests;

class ConnectionFunctionalTest extends BaseTest
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
}
