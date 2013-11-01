<?php

namespace Doctrine\Tests\MongoDB\Util;

use Doctrine\MongoDB\Util\ReadPreference;

class ReadPreferenceTest extends \PHPUnit_Framework_TestCase
{
    protected function setUp()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->markTestSkipped('This test is not applicable to driver versions < 1.3.0');
        }
    }

    /**
     * @expectedException InvalidArgumentException
     */
    public function testConvertNumericTypeShouldThrowExceptionForInvalidType()
    {
        ReadPreference::convertNumericType(-1);
    }

    public function testConvertReadPreference()
    {
        $readPref = array(
            'type' => 0,
            'type_string' => \MongoClient::RP_PRIMARY,
            'tagsets' => array(array('dc:east')),
        );

        $expected = array(
            'type' => \MongoClient::RP_PRIMARY,
            'tagsets' => array(array('dc' => 'east')),
        );

        $this->assertEquals($expected, ReadPreference::convertReadPreference($readPref));
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
                ),
            ),
            array(
                array(array()),
                array(array()),
            ),
            /* This tag set is impractical, since an empty set matches anything,
             * but we want to test that elements beyond the first are converted.
             */
            array(
                array(
                    array(),
                    array('dc:west'),
                    array('dc:east', 'use:reporting'),
                ),
                array(
                    array(),
                    array('dc' => 'west'),
                    array('dc' => 'east', 'use' => 'reporting'),
                ),
            ),
        );
    }

    /**
     * @dataProvider provideTagSetsAcceptedBySetReadPreference
     */
    public function testConvertTagSetsShouldNotAlterTagSetsAcceptedBySetReadPreference($tagSet)
    {
        $this->assertEquals($tagSet, ReadPreference::convertTagSets($tagSet));
    }

    public function provideTagSetsAcceptedBySetReadPreference()
    {
        return array(
            array(
                array(
                    array('dc' => 'east', 'use' => 'reporting'),
                    array('dc' => 'west'),
                    array(),
                ),
            ),
            /* These numeric tag names are likely impractical, but they should
             * be accepted by setReadPreference() and thus not modified.
             */
            array(
                array(
                    array('0' => 'zero', '1' => 'one'),
                    array(),
                ),
            ),
        );
    }
}
