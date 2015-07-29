<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Project;

class ProjectTest extends \PHPUnit_Framework_TestCase
{
    public function testProjectStage()
    {
        $projectStage = new Project($this->getTestAggregationBuilder());
        $projectStage
            ->excludeIdField()
            ->includeFields(array('$field', '$otherField'))
            ->field('product')
            ->multiply('$field', 5);

        $this->assertSame(array('$project' => array('_id' => false, '$field' => true, '$otherField' => true, 'product' => array('$multiply' => array('$field', 5)))), $projectStage->getExpression());
    }

    public function testProjectFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->project()
            ->excludeIdField()
            ->includeFields(array('$field', '$otherField'))
            ->field('product')
            ->multiply('$field', 5);

        $this->assertSame(array(array('$project' => array('_id' => false, '$field' => true, '$otherField' => true, 'product' => array('$multiply' => array('$field', 5))))), $builder->getPipeline());
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
