<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\CreateCollectionEventArgs;
use Doctrine\MongoDB\Tests\TestCase;

class CreateCollectionEventArgsTest extends TestCase
{
    public function testCreateCollectionEventArgs()
    {
        $invoker = new \stdClass();
        $name = 'foo';
        $options = [
            'capped' => true,
            'size' => 10485760,
            'autoIndexId' => false,
        ];

        $createCollectionEventArgs = new CreateCollectionEventArgs($invoker, $name, $options);

        $this->assertSame($invoker, $createCollectionEventArgs->getInvoker());
        $this->assertSame($name, $createCollectionEventArgs->getName());
        $this->assertSame($options, $createCollectionEventArgs->getOptions());
    }
}
