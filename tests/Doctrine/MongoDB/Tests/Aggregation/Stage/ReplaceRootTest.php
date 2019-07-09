<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\ReplaceRoot;
use Doctrine\MongoDB\Tests\Aggregation\AggregationTestCase;
use Doctrine\MongoDB\Tests\TestCase;

class ReplaceRootTest extends TestCase
{
    use AggregationTestCase;

    public function testReplaceRootStage()
    {
        $replaceRootStage = new ReplaceRoot($this->getTestAggregationBuilder());
        $replaceRootStage
            ->field('product')
            ->multiply('$field', 5);

        $this->assertEquals(
            [
                '$replaceRoot' => [
                    'newRoot' => (object) [
                        'product' => ['$multiply' => ['$field', 5]],
                    ],
                ],
            ],
            $replaceRootStage->getExpression()
        );
    }

    public function testReplaceRootFromBuilder()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder
            ->replaceRoot()
                ->field('product')
                ->multiply('$field', 5);

        $this->assertEquals(
            [
                [
                    '$replaceRoot' => [
                        'newRoot' => (object) [
                            'product' => ['$multiply' => ['$field', 5]],
                        ],
                    ],
                ],
            ],
            $builder->getPipeline()
        );
    }

    public function testReplaceWithEmbeddedDocument()
    {
        $builder = $this->getTestAggregationBuilder();
        $builder->replaceRoot('$some.embedded.document');

        $this->assertEquals(
            [
                [
                    '$replaceRoot' => [
                        'newRoot' => '$some.embedded.document'
                    ]
                ]
            ],
            $builder->getPipeline()
        );
    }
}
