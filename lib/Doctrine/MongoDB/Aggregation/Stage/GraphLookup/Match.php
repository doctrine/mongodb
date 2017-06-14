<?php

namespace Doctrine\MongoDB\Aggregation\Stage\GraphLookup;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Aggregation\Stage\GraphLookup;
use Doctrine\MongoDB\Aggregation\Stage\Match as BaseMatch;

class Match extends BaseMatch
{
    /**
     * @var GraphLookup
     */
    private $graphLookup;

    /**
     * @param Builder $builder
     * @param GraphLookup $graphLookup
     */
    public function __construct(Builder $builder, GraphLookup $graphLookup)
    {
        parent::__construct($builder);

        $this->graphLookup = $graphLookup;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return $this->query->getQuery();
    }

    /**
     * @param string $from
     *
     * @return GraphLookup
     */
    public function from($from)
    {
        return $this->graphLookup->from($from);
    }

    /**
     * @param string|array|Expr $expression
     *
     * @return GraphLookup
     */
    public function startWith($expression)
    {
        return $this->graphLookup->startWith($expression);
    }

    /**
     * @param string $connectFromField
     *
     * @return GraphLookup
     */
    public function connectFromField($connectFromField)
    {
        return $this->graphLookup->connectFromField($connectFromField);
    }

    /**
     * @param string $connectToField
     *
     * @return GraphLookup
     */
    public function connectToField($connectToField)
    {
        return $this->graphLookup->connectToField($connectToField);
    }

    /**
     * @param string $alias
     *
     * @return GraphLookup
     */
    public function alias($alias)
    {
        return $this->graphLookup->alias($alias);
    }

    /**
     * @param int $maxDepth
     *
     * @return GraphLookup
     */
    public function maxDepth($maxDepth)
    {
        return $this->graphLookup->maxDepth($maxDepth);
    }

    /**
     * @param string $depthField
     *
     * @return GraphLookup
     */
    public function depthField($depthField)
    {
        return $this->graphLookup->depthField($depthField);
    }
}
