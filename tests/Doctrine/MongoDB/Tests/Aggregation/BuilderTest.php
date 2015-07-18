<?php

namespace Doctrine\MongoDB\Tests\Aggregation;

use Doctrine\MongoDB\Aggregation\Builder;

class BuilderTest extends \PHPUnit_Framework_TestCase
{
    public function testGetPipeline()
    {
        $point = array('type' => 'Point', 'coordinates' => array(0, 0));

        $expectedPipeline = array(
            array(
                '$geoNear' => array(
                    'near' => $point,
                    'spherical' => true,
                    'distanceField' => 'distance',
                    'query' => array(
                        'hasCoordinates' => array('$exists' => true),
                        'username' => 'foo',
                    ),
                    'num' => 10
                )
            ),
            array('$match' =>
                array(
                    '$or' => array(
                        array('username' => 'admin'),
                        array('username' => 'administrator')
                    ),
                    'group' => array('$in' => array('a', 'b'))
                )
            ),
            array('$unwind' => 'a'),
            array('$unwind' => 'b'),
            array('$redact' =>
                array(
                    '$cond' => array(
                        'if' => array('$lte' => array('$accessLevel', 3)),
                        'then' => '$$KEEP',
                        'else' => '$$REDACT',
                    )
                )
            ),
            array('$project' =>
                array(
                    '_id' => false,
                    'user' => true,
                    'amount' => true,
                    'invoiceAddress' => true,
                    'deliveryAddress' => array(
                        '$cond' => array(
                            'if' => array(
                                '$and' => array(
                                    array('$eq' => array('$useAlternateDeliveryAddress', true)),
                                    array('$ne' => array('$deliveryAddress', null))
                                )
                            ),
                            'then' => '$deliveryAddress',
                            'else' => '$invoiceAddress'
                        )
                    )
                )
            ),
            array('$group' =>
                array(
                    '_id' => '$user',
                    'numOrders' => array('$sum' => 1),
                    'amount' => array(
                        'total' => array('$sum' => '$amount'),
                        'avg' => array('$avg' => '$amount')
                    )
                )
            ),
            array('$sort' => array('totalAmount' => 0)),
            array('$sort' => array('numOrders' => -1, 'avgAmount' => 1)),
            array('$limit' => 5),
            array('$skip' => 2),
            array('$out' => 'collectionName')
        );

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
                ->in(array('a', 'b'))
                ->addOr($builder->matchExpr()->field('username')->equals('admin'))
                ->addOr($builder->matchExpr()->field('username')->equals('administrator'))
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
                ->includeFields(array('user', 'amount', 'invoiceAddress'))
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
            ->sort(array('numOrders' => 'desc', 'avgAmount' => 'asc')) // Multiple subsequent sorts are combined into a single stage
            ->limit(5)
            ->skip(2)
            ->out('collectionName');

        $this->assertEquals($expectedPipeline, $builder->getPipeline());
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
