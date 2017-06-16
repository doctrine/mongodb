<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Database;
use Doctrine\MongoDB\Events;
use Doctrine\MongoDB\Event\CreateCollectionEventArgs;
use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Event\MutableEventArgs;

class DatabaseEventsTest extends TestCase
{
    private $connection;
    private $eventManager;
    private $mongoDB;

    public function setUp()
    {
        $this->connection = $this->getMockConnection();
        $this->eventManager = $this->getMockEventManager();
        $this->mongoDB = $this->getMockMongoDB();
    }

    public function testCreateCollection()
    {
        $name = 'collection';
        $options = ['capped' => false, 'size' => 0, 'max' => 0];
        $result = $this->getMockCollection();

        $db = $this->getMockDatabase(['doCreateCollection' => $result]);

        $this->expectEvents([
            [Events::preCreateCollection, new CreateCollectionEventArgs($db, $name, $options)],
            [Events::postCreateCollection, new EventArgs($db, $result)],
        ]);

        $this->assertSame($result, $db->createCollection($name, $options));
    }

    public function testDrop()
    {
        $result = ['dropped' => 'databaseName', 'ok' => 1];

        $this->mongoDB->expects($this->once())
            ->method('drop')
            ->will($this->returnValue($result));

        $db = new Database($this->connection, $this->mongoDB, $this->eventManager);

        $this->expectEvents([
            [Events::preDropDatabase, new EventArgs($db)],
            [Events::postDropDatabase, new EventArgs($db)],
        ]);

        $this->assertSame($result, $db->drop());
    }

    public function testGetDBRef()
    {
        $reference = ['$ref' => 'collection', '$id' => 1];
        $result = ['_id' => 1];

        $db = $this->getMockDatabase(['doGetDBRef' => $result]);

        $this->expectEvents([
            [Events::preGetDBRef, new EventArgs($db, $reference)],
            [Events::postGetDBRef, new MutableEventArgs($db, $result)],
        ]);

        $this->assertSame($result, $db->getDBRef($reference));
    }

    public function testGetGridFS()
    {
        $prefix = 'fs';
        $result = $this->getMockGridFS();

        $db = $this->getMockDatabase(['doGetGridFS' => $result]);

        $this->expectEvents([
            [Events::preGetGridFS, new EventArgs($db, $prefix)],
            [Events::postGetGridFS, new EventArgs($db, $result)],
        ]);

        $this->assertSame($result, $db->getGridFS());
    }

    public function testSelectCollection()
    {
        $name = 'collection';
        $result = $this->getMockCollection();

        $db = $this->getMockDatabase(['doSelectCollection' => $result]);

        $this->expectEvents([
            [Events::preSelectCollection, new EventArgs($db, $name)],
            [Events::postSelectCollection, new EventArgs($db, $result)],
        ]);

        $this->assertSame($result, $db->selectCollection($name));
    }

    /**
     * Expect events to be dispatched by the event manager in the given order.
     *
     * @param array $events Tuple of event name and dispatch argument
     */
    private function expectEvents(array $events)
    {
        /* Each event should be a tuple consisting of the event name and the
         * dispatched argument (e.g. EventArgs).
         *
         * For each event, expect a call to hasListeners() immediately followed
         * by a call to dispatchEvent(). The dispatch argument is passed as-is
         * to with(), so constraints may be used (e.g. callback).
         */
        foreach ($events as $i => $event) {
            $this->eventManager->expects($this->at($i * 2))
                ->method('hasListeners')
                ->with($event[0])
                ->will($this->returnValue(true));

            $this->eventManager->expects($this->at($i * 2 + 1))
                ->method('dispatchEvent')
                ->with($event[0], $event[1]);
        }
    }

    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockConnection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockDatabase(array $methods = [])
    {
        $db = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->setConstructorArgs([$this->connection, $this->mongoDB, $this->eventManager])
            ->setMethods(array_keys($methods))
            ->getMock();

        foreach ($methods as $method => $returnValue) {
            $db->expects($this->once())
                ->method($method)
                ->will($this->returnValue($returnValue));
        }

        return $db;
    }

    private function getMockEventManager()
    {
        return $this->getMockBuilder('Doctrine\Common\EventManager')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockGridFS()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\GridFS')
            ->disableOriginalConstructor()
            ->getMock();
    }

    private function getMockMongoDB()
    {
        return $this->getMockBuilder('MongoDB')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
