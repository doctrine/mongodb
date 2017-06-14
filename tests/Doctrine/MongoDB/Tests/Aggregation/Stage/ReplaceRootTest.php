<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\ReplaceRoot;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

/**
 * @author Boris Guéry <guery.b@gmail.com>
 */
class ReplaceRootTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testReplaceRootStage()
    {
        $addFieldsStage = new ReplaceRoot($this->getTestAggregationBuilder(), 'newRootField');

        $this->assertSame(['$replaceRoot' => ['newRoot' => 'newRootField']], $addFieldsStage->getExpression());
    }

    public function testProjectFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->replaceRoot('newRootField')
        ;
        $this->assertSame([['$replaceRoot' => ['newRoot' => 'newRootField']]], $builder->getPipeline());
    }
}
