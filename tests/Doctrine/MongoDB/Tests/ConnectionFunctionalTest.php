<?php

namespace Doctrine\MongoDB\Tests;

class ConnectionFunctionalTest extends BaseTest
{
    public function testIsConnected()
    {
        $this->assertFalse($this->conn->isConnected());
        $this->conn->connect();
        $this->assertTrue($this->conn->isConnected());
        $this->conn->close();
        $this->assertFalse($this->conn->isConnected());
    }
}
