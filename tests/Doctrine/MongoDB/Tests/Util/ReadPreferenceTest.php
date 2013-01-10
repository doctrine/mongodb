<?php

namespace Doctrine\Tests\MongoDB\Util;

use Doctrine\MongoDB\Util\ReadPreference;

class ReadPreferenceTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConvertNumericTypeShouldThrowExceptionForInvalidType()
    {
        ReadPreference::convertNumericType(-1);
    }

    /**
     * @dataProvider provideTagSets
     */
    public function testConvertTagSets($tagSet, $expected)
    {
        $this->assertEquals($expected, ReadPreference::convertTagSets($tagSet));
    }

    public function provideTagSets()
    {
        return array(
            array(
                array(
                    array('dc:east', 'use:reporting'),
                    array('dc:west'),
                    array(),
                ),
                array(
                    array('dc' => 'east', 'use' => 'reporting'),
                    array('dc' => 'west'),
                    array(),
                )
            ),
            array(
                array(array()),
                array(array()),
            ),
        );
    }
}
