<?php

namespace Doctrine\Tests\MongoDB\Util;

use Doctrine\MongoDB\Util\ReadPreference;
use Doctrine\MongoDB\Tests\TestCase;

class ReadPreferenceTest extends TestCase
{
    /**
     * @expectedException InvalidArgumentException
     */
    public function testConvertNumericTypeShouldThrowExceptionForInvalidType()
    {
        ReadPreference::convertNumericType(-1);
    }

    public function testConvertReadPreference()
    {
        $readPref = [
            'type' => 0,
            'type_string' => \MongoClient::RP_PRIMARY,
            'tagsets' => [['dc:east']],
        ];

        $expected = [
            'type' => \MongoClient::RP_PRIMARY,
            'tagsets' => [['dc' => 'east']],
        ];

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
        return [
            [
                [
                    ['dc:east', 'use:reporting'],
                    ['dc:west'],
                    [],
                ],
                [
                    ['dc' => 'east', 'use' => 'reporting'],
                    ['dc' => 'west'],
                    [],
                ],
            ],
            [
                [[]],
                [[]],
            ],
            /* This tag set is impractical, since an empty set matches anything,
             * but we want to test that elements beyond the first are converted.
             */
            [
                [
                    [],
                    ['dc:west'],
                    ['dc:east', 'use:reporting'],
                ],
                [
                    [],
                    ['dc' => 'west'],
                    ['dc' => 'east', 'use' => 'reporting'],
                ],
            ],
        ];
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
        return [
            [
                [
                    ['dc' => 'east', 'use' => 'reporting'],
                    ['dc' => 'west'],
                    [],
                ],
            ],
            /* These numeric tag names are likely impractical, but they should
             * be accepted by setReadPreference() and thus not modified.
             */
            [
                [
                    ['0' => 'zero', '1' => 'one'],
                    [],
                ],
            ],
        ];
    }
}
