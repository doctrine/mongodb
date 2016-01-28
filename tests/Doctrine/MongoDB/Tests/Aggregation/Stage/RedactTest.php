<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage\Redact;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;

class RedactTest extends \PHPUnit_Framework_TestCase
{
    use AggregationTestCase;

    public function testRedactStage()
    {
        $builder = $this->getTestAggregationBuilder();

        $redactStage = new Redact($builder, 'someCollection');
        $redactStage
            ->cond(
                $builder->expr()->lte('$accessLevel', 3),
                '$$KEEP',
                '$$REDACT'
            );

        $this->assertSame(array('$redact' => array('$cond' => array('if' => array('$lte' => array('$accessLevel', 3)), 'then' => '$$KEEP', 'else' => '$$REDACT'))), $redactStage->getExpression());
    }

    public function testRedactFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->redact()
            ->cond(
                $builder->expr()->lte('$accessLevel', 3),
                '$$KEEP',
                '$$REDACT'
            );

        $this->assertSame(array(array('$redact' => array('$cond' => array('if' => array('$lte' => array('$accessLevel', 3)), 'then' => '$$KEEP', 'else' => '$$REDACT')))), $builder->getPipeline());
    }
}
