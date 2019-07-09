<?php

namespace Doctrine\MongoDB\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Expr;
use function is_array;

/**
 * Fluent interface for adding a $replaceRoot stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.5
 */
class ReplaceRoot extends Operator
{
    /**
     * @var string|null
     */
    private $expression;

    /**
     * @param Builder $builder
     * @param string|null $expression Optional. A replacement expression that
     * resolves to a document.
     */
    public function __construct(Builder $builder, $expression = null)
    {
        parent::__construct($builder);

        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        $expression = $this->expression !== null ? $this->convertExpression($this->expression) : $this->expr->getExpression();

        return [
            '$replaceRoot' => [
                'newRoot' => is_array($expression) ? (object) $expression : $expression,
            ],
        ];
    }

    /**
     * Converts an expression object into an array, recursing into nested items
     *
     * This method is meant to be overwritten by extending classes to apply
     * custom conversions (e.g. field name translation in MongoDB ODM) to the
     * expression object.
     *
     * @param mixed|self $expression
     * @return string|array
     */
    protected function convertExpression($expression)
    {
        return Expr::convertExpression($expression);
    }
}
