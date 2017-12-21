<?php

namespace Doctrine\MongoDB\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage;

/**
 * Fluent interface for adding a $sortByCount stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.5
 */
class SortByCount extends Stage
{
    /**
     * @var string
     */
    private $fieldName;

    /**
     * @param Builder $builder
     * @param string $fieldName Expression to group by. To specify a field path,
     * prefix the field name with a dollar sign $ and enclose it in quotes.
     * The expression can not evaluate to an object.
     */
    public function __construct(Builder $builder, $fieldName)
    {
        parent::__construct($builder);

        $this->fieldName = (string) $fieldName;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return [
            '$sortByCount' => $this->fieldName
        ];
    }
}
