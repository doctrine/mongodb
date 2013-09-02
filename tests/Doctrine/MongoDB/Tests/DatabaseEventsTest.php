<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Events;
use Doctrine\MongoDB\Event\CreateCollectionEventArgs;
use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Event\MutableEventArgs;

class DatabaseEventsTest extends \PHPUnit_Framework_TestCase
{
    const databaseName = 'database';

    public function testCreateCollection()
    {
        $name = 'collection';
        $options = array('capped' => false, 'size' => 0, 'max' => 0);
        $result = $this->getMockCollection();

        $eventManager = $this->getMockEventManager();
        $db = $this->getMockDatabase($eventManager, array('doCreateCollection' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preCreateCollection, new CreateCollectionEventArgs($db, $name, $options)),
            array(Events::postCreateCollection, new EventArgs($db, $result)),
        ));

        $this->assertSame($result, $db->createCollection($name, $options));
    }

    public function testDrop()
    {
        $result = array('dropped' => self::databaseName, 'ok' => 1);

        $mongoDB = $this->getMockMongoDB();
        $mongoDB->expects($this->once())
            ->method('drop')
            ->will($this->returnValue($result));

        $eventManager = $this->getMockEventManager();
        $db = $this->getMockDatabase($eventManager, array('getMongoDB' => $mongoDB));

        $this->expectEvents($eventManager, array(
            array(Events::preDropDatabase, new EventArgs($db)),
            array(Events::postDropDatabase, new EventArgs($db)),
        ));

        $this->assertSame($result, $db->drop());
    }

    public function testGetDBRef()
    {
        $reference = array('$ref' => 'collection', '$id' => 1);
        $result = array('_id' => 1);

        $eventManager = $this->getMockEventManager();
        $db = $this->getMockDatabase($eventManager, array('doGetDBRef' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preGetDBRef, new EventArgs($db, $reference)),
            array(Events::postGetDBRef, new MutableEventArgs($db, $result)),
        ));

        $this->assertSame($result, $db->getDBRef($reference));
    }

    public function testGetGridFS()
    {
        $prefix = 'fs';
        $result = $this->getMockGridFS();

        $eventManager = $this->getMockEventManager();
        $db = $this->getMockDatabase($eventManager, array('doGetGridFS' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preGetGridFS, new EventArgs($db, $prefix)),
            array(Events::postGetGridFS, new EventArgs($db, $result)),
        ));

        $this->assertSame($result, $db->getGridFS());
    }

    public function testSelectCollection()
    {
        $name = 'collection';
        $result = $this->getMockCollection();

        $eventManager = $this->getMockEventManager();
        $db = $this->getMockDatabase($eventManager, array('doSelectCollection' => $result));

        $this->expectEvents($eventManager, array(
            array(Events::preSelectCollection, new EventArgs($db, $name)),
            array(Events::postSelectCollection, new EventArgs($db, $result)),
        ));

        $this->assertSame($result, $db->selectCollection($name));
    }

    /**
     * Expect events to be dispatched by the event manager in the given order.
     *
     * @param EventManager $em     EventManager mock
     * @param array        $events Tuple of event name and dispatch argument
     */
    private function expectEvents(EventManager $em, array $events)
    {
        /* Each event should be a tuple consisting of the event name and the
         * dispatched argument (e.g. EventArgs).
         *
         * For each event, expect a call to hasListeners() immediately followed
         * by a call to dispatchEvent(). The dispatch argument is passed as-is
         * to with(), so constraints may be used (e.g. callback).
         */
        foreach ($events as $i => $event) {
            $em->expects($this->at($i * 2))
                ->method('hasListeners')
                ->with($event[0])
                ->will($this->returnValue(true));

            $em->expects($this->at($i * 2 + 1))
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

    private function getMockDatabase(EventManager $em, array $methods)
    {
        $c = $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $db = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->setConstructorArgs(array($c, self::databaseName, $em))
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
