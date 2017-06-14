<?php

namespace Doctrine\MongoDB\Tests\Aggregation;

use Doctrine\MongoDB\Tests\TestCase;

class BuilderTest extends TestCase
{
    use AggregationTestCase;

    public function testGetPipeline()
    {
        $point = ['type' => 'Point', 'coordinates' => [0, 0]];

        $expectedPipeline = [
            [
                '$geoNear' => [
                    'near' => $point,
                    'spherical' => true,
                    'distanceField' => 'distance',
                    'query' => [
                        'hasCoordinates' => ['$exists' => true],
                        'username' => 'foo',
                    ],
                    'num' => 10
                ]
            ],
            ['$match' =>
                [
                    '$or' => [
                        ['username' => 'admin'],
                        ['username' => 'administrator']
                    ],
                    'group' => ['$in' => ['a', 'b']]
                ]
            ],
            ['$sample' => ['size' => 10]],
            ['$lookup' =>
                [
                    'from' => 'orders',
                    'localField' => '_id',
                    'foreignField' => 'user.$id',
                    'as' => 'orders'
                ]
            ],
            ['$unwind' => 'a'],
            ['$unwind' => 'b'],
            ['$redact' =>
                [
                    '$cond' => [
                        'if' => ['$lte' => ['$accessLevel', 3]],
                        'then' => '$$KEEP',
                        'else' => '$$REDACT',
                    ]
                ]
            ],
            ['$project' =>
                [
                    '_id' => false,
                    'user' => true,
                    'amount' => true,
                    'invoiceAddress' => true,
                    'deliveryAddress' => [
                        '$cond' => [
                            'if' => [
                                '$and' => [
                                    ['$eq' => ['$useAlternateDeliveryAddress', true]],
                                    ['$ne' => ['$deliveryAddress', null]]
                                ]
                            ],
                            'then' => '$deliveryAddress',
                            'else' => '$invoiceAddress'
                        ]
                    ]
                ]
            ],
            ['$group' =>
                [
                    '_id' => '$user',
                    'numOrders' => ['$sum' => 1],
                    'amount' => [
                        'total' => ['$sum' => '$amount'],
                        'avg' => ['$avg' => '$amount']
                    ]
                ]
            ],
            ['$sort' => ['totalAmount' => 0]],
            ['$sort' => ['numOrders' => -1, 'avgAmount' => 1]],
            ['$limit' => 5],
            ['$skip' => 2],
            ['$out' => 'collectionName']
        ];

        $builder = $this->getTestAggregationBuilder();
        $builder
            ->geoNear($point)
                ->distanceField('distance')
                ->limit(10) // Limit is applied on $geoNear
                ->field('hasCoordinates')
                ->exists(true)
                ->field('username')
                ->equals('foo')
            ->match()
                ->field('group')
                ->in(['a', 'b'])
                ->addOr($builder->matchExpr()->field('username')->equals('admin'))
                ->addOr($builder->matchExpr()->field('username')->equals('administrator'))
            ->sample(10)
            ->lookup('orders')
                ->localField('_id')
                ->foreignField('user.$id')
                ->alias('orders')
            ->unwind('a')
            ->unwind('b')
            ->redact()
                ->cond(
                    $builder->expr()->lte('$accessLevel', 3),
                    '$$KEEP',
                    '$$REDACT'
                )
            ->project()
                ->excludeIdField()
                ->includeFields(['user', 'amount', 'invoiceAddress'])
                ->field('deliveryAddress')
                ->cond(
                    $builder->expr()
                        ->addAnd($builder->expr()->eq('$useAlternateDeliveryAddress', true))
                        ->addAnd($builder->expr()->ne('$deliveryAddress', null)),
                    '$deliveryAddress',
                    '$invoiceAddress'
                )
            ->group()
                ->field('_id')
                ->expression('$user')
                ->field('numOrders')
                ->sum(1)
                ->field('amount')
                ->expression(
                    $builder->expr()
                        ->field('total')
                        ->sum('$amount')
                        ->field('avg')
                        ->avg('$amount')
                )
            ->sort('totalAmount')
            ->sort(['numOrders' => 'desc', 'avgAmount' => 'asc']) // Multiple subsequent sorts are combined into a single stage
            ->limit(5)
            ->skip(2)
            ->out('collectionName');

        $this->assertEquals($expectedPipeline, $builder->getPipeline());
    }
}
