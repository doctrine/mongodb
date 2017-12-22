<?php

namespace Doctrine\MongoDB\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage;

/**
 * Fluent interface for adding a $out stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.2
 */
class Out extends Stage
{
    /**
     * @var string
     */
    private $collection;

    /**
     * @param Builder $builder
     * @param string $collection
     */
    public function __construct(Builder $builder, $collection)
    {
        parent::__construct($builder);

        $this->out($collection);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return [
            '$out' => $this->collection
        ];
    }

    /**
     * {@inheritdoc}
     */
    public function out($collection)
    {
        $this->collection = (string) $collection;

        return $this;
    }
}
