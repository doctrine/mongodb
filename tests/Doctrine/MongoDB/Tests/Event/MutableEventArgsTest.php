<?php

namespace Doctrine\MongoDB\Tests\Event;

use Doctrine\MongoDB\Event\MutableEventArgs;

class MutableEventArgsTest extends \PHPUnit_Framework_TestCase
{
    public function testMutableEventArgs()
    {
        $invoker = new \stdClass();
        $data = array('ok' => 1);
        $options = array('w' => 1);

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
        return array(
            array(new \stdClass(), new \stdClass()),
            array(array('ok' => 1), array('ok' => 0)),
            array('foo', 'bar'),
            array(1, 1.0),
        );
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
