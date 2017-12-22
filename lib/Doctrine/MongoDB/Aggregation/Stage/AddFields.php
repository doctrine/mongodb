<?php

namespace Doctrine\MongoDB\Aggregation\Stage;

/**
 * Fluent interface for adding a $addFields stage to an aggregation pipeline.
 *
 * @author Boris Guéry <guery.b@gmail.com>
 */
class AddFields extends Operator
{
    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return [
            '$addFields' => $this->expr->getExpression()
        ];
    }
}
