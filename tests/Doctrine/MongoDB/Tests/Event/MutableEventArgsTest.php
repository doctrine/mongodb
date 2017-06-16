<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\MutableEventArgs;
use Doctrine\MongoDB\Tests\TestCase;

class MutableEventArgsTest extends TestCase
{
    public function testMutableEventArgs()
    {
        $invoker = new \stdClass();
        $data = ['ok' => 1];
        $options = ['w' => 1];

        $mutableEventArgs = new MutableEventArgs($invoker, $data, $options);

        $this->assertSame($invoker, $mutableEventArgs->getInvoker());
        $this->assertSame($data, $mutableEventArgs->getData());
        $this->assertSame($options, $mutableEventArgs->getOptions());
        $this->assertFalse($mutableEventArgs->isDataChanged());
        $this->assertFalse($mutableEventArgs->isOptionsChanged());
    }

    /**
     * @dataProvider provideChangedData
     */
    public function testIsDataChanged($oldData, $newData)
    {
        $invoker = new \stdClass();

        $mutableEventArgs = new MutableEventArgs($invoker, $oldData);

        $this->assertFalse($mutableEventArgs->isDataChanged());
        $this->assertSame($oldData, $mutableEventArgs->getData());

        $mutableEventArgs->setData($oldData);

        $this->assertSame($oldData, $mutableEventArgs->getData());
        $this->assertFalse($mutableEventArgs->isDataChanged());

        $mutableEventArgs->setData($newData);

        $this->assertSame($newData, $mutableEventArgs->getData());
        $this->assertTrue($mutableEventArgs->isDataChanged());

        $mutableEventArgs->setData($newData);

        $this->assertSame($newData, $mutableEventArgs->getData());
        $this->assertTrue($mutableEventArgs->isDataChanged());

        $mutableEventArgs->setData($oldData);

        $this->assertSame($oldData, $mutableEventArgs->getData());
        $this->assertFalse($mutableEventArgs->isDataChanged());
    }

    public function provideChangedData()
    {
        return [
            [new \stdClass(), new \stdClass()],
            [['ok' => 1], ['ok' => 0]],
            ['foo', 'bar'],
            [1, 1.0],
        ];
    }

    /**
     * @dataProvider provideChangedOptions
     */
    public function testIsOptionsChanged($oldOptions, $newOptions)
    {
        $invoker = new \stdClass();

        $mutableEventArgs = new MutableEventArgs($invoker, [], $oldOptions);

        $this->assertFalse($mutableEventArgs->isOptionsChanged());
        $this->assertSame($oldOptions, $mutableEventArgs->getOptions());

        $mutableEventArgs->setOptions($oldOptions);

        $this->assertSame($oldOptions, $mutableEventArgs->getOptions());
        $this->assertFalse($mutableEventArgs->isOptionsChanged());

        $mutableEventArgs->setOptions($newOptions);

        $this->assertSame($newOptions, $mutableEventArgs->getOptions());
        $this->assertTrue($mutableEventArgs->isOptionsChanged());

        $mutableEventArgs->setOptions($newOptions);

        $this->assertSame($newOptions, $mutableEventArgs->getOptions());
        $this->assertTrue($mutableEventArgs->isOptionsChanged());

        $mutableEventArgs->setOptions($oldOptions);

        $this->assertSame($oldOptions, $mutableEventArgs->getOptions());
        $this->assertFalse($mutableEventArgs->isOptionsChanged());
    }

    public function provideChangedOptions()
    {
        return [
            [['foo' => 'bar'], ['foo' => 'baz']],
            [[], ['foo' => 'bar']],
            [['foo' => 'bar'], []],
        ];
    }
}
