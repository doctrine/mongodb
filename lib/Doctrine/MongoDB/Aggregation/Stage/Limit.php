<?php

namespace Doctrine\MongoDB\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage;

/**
 * Fluent interface for adding a $limit stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.2
 */
class Limit extends Stage
{
    /**
     * @var integer
     */
    private $limit;

    /**
     * @param Builder $builder
     * @param integer $limit
     */
    public function __construct(Builder $builder, $limit)
    {
        parent::__construct($builder);

        $this->limit = (integer) $limit;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return [
            '$limit' => $this->limit
        ];
    }
}
