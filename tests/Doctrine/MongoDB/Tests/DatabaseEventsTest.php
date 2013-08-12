<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Events;
use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Event\MutableEventArgs;

class DatabaseEventsTest extends \PHPUnit_Framework_TestCase
{
    const databaseName = 'database';

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

    private function getMockDatabase(EventManager $em, array $methods)
    {
        $c = $this->getMockBuilder('Doctrine\MongoDB\Connection')
            ->disableOriginalConstructor()
            ->getMock();

        $db = $this->getMockBuilder('Doctrine\MongoDB\Database')
            ->setConstructorArgs(array($c, self::databaseName, $em, '$'))
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
}
