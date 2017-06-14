<?php

namespace Doctrine\MongoDB\Tests;


use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\LoggableCursor;

class LoggableCursorTest extends DatabaseTestCase
{
    /**
     * @dataProvider provideLoggedMethods
     */
    public function testLoggedMethod($method, $log, $argument = null)
    {
        $config = new Configuration();
        $config->setLoggerCallable(function(array $message) use ($log) {
            if (isset($message['find'])) {
                return;
            }

            $this->assertArraySubset($log, $message);
        });
        $conn = new Connection(null, [], $config);

        /** @var LoggableCursor $cursor */
        $cursor = $conn->selectCollection('foo', 'bar')->find();
        $this->assertInstanceOf(LoggableCursor::class, $cursor);

        $cursor->$method($argument);
    }

    public function provideLoggedMethods()
    {
        return [
            ['sort', ['sort' => true, 'sortFields' => ['foo' => true]], ['foo' => true]],
            ['skip', ['skip' => true, 'skipNum' => 5], 5],
            ['limit', ['limit' => true, 'limitNum' => 5], 5],
            ['hint',  ['hint' => true, 'keyPattern' => 'indexName'], 'indexName'],
            ['snapshot', ['snapshot' => true]],
            ['maxTimeMS', ['maxTimeMS' => true, 'maxTimeMSNum' => 10], 10]
        ];
    }
}
