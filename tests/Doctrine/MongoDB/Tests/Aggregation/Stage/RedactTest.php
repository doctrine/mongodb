<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Redact;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class RedactTest extends TestCase
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

        $this->assertSame(['$redact' => ['$cond' => ['if' => ['$lte' => ['$accessLevel', 3]], 'then' => '$$KEEP', 'else' => '$$REDACT']]], $redactStage->getExpression());
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

        $this->assertSame([['$redact' => ['$cond' => ['if' => ['$lte' => ['$accessLevel', 3]], 'then' => '$$KEEP', 'else' => '$$REDACT']]]], $builder->getPipeline());
    }
}
