<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Sort;

class SortTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @dataProvider provideSortOptions
     */
    public function testSortStage($expectedSort, $field, $order = null)
    {
        $sortStage = new Sort($this->getTestAggregationBuilder(), $field, $order);

        $this->assertSame(array('$sort' => $expectedSort), $sortStage->getExpression());
    }

    /**
     * @dataProvider provideSortOptions
     */
    public function testSortFromBuilder($expectedSort, $field, $order = null)
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->sort($field, $order);

        $this->assertSame(array(array('$sort' => $expectedSort)), $builder->getPipeline());
    }

    public static function provideSortOptions()
    {
        return array(
            'singleFieldSeparated' => array(
                array('field' => -1),
                'field',
                'desc'
            ),
            'singleFieldCombined' => array(
                array('field' => -1),
                array('field' => 'desc')
            ),
            'multipleFields' => array(
                array('field' => -1, 'otherField' => 1),
                array('field' => 'desc', 'otherField' => 'asc')
            ),
            'sortMeta' => array(
                array('field' => array('$meta' => 'textScore'), 'invalidField' => -1),
                array('field' => 'textScore', 'invalidField' => 'nonExistingMetaField')
            )
        );
    }

    private function getTestAggregationBuilder()
    {
        return new Builder($this->getMockCollection());
    }

    private function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
