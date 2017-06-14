<?php

namespace Doctrine\MongoDB\Tests;


class LoggableCursorTest extends DatabaseTestCase
{
    /**
     * @dataProvider provideLoggedMethods
     */
    public function testLoggedMethod($method, $argument = null)
    {
        $cursor = $this->conn->selectCollection('foo', 'bar')->find();
        $cursor->$method($argument);
    }

    public function provideLoggedMethods()
    {
        return [
            ['sort', []],
            ['skip'],
            ['limit'],
            ['hint', []],
            ['snapshot'],
            ['maxTimeMS', []]
        ];
    }
}
