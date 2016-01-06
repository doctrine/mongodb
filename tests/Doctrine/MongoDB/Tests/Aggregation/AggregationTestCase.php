<?php

namespace Doctrine\MongoDB\Tests\Aggregation;

use Doctrine\MongoDB\Aggregation\Builder;

trait AggregationTestCase
{
    /**
     * @param string $className
     * @return \PHPUnit_Framework_MockObject_MockBuilder
     */
    abstract protected function getMockBuilder($className);

    /**
     * @return Builder
     */
    protected function getTestAggregationBuilder()
    {
        return new Builder($this->getMockCollection());
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\MongoDB\Collection
     */
    protected function getMockCollection()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Collection')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\MongoDB\Aggregation\\Expr
     */
    protected function getMockAggregationExpr()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Aggregation\Expr')
            ->disableOriginalConstructor()
            ->getMock();
    }

    /**
     * @return \PHPUnit_Framework_MockObject_MockObject|\Doctrine\MongoDB\Query\\Expr
     */
    protected function getMockQueryExpr()
    {
        return $this->getMockBuilder('Doctrine\MongoDB\Query\Expr')
            ->disableOriginalConstructor()
            ->getMock();
    }
}
