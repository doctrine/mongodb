<?php

namespace Doctrine\MongoDB\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Aggregation\Stage;

/**
 * Abstract class with common functionality for $bucket and $bucketAuto stages
 *
 * @internal
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.5
 */
abstract class AbstractBucket extends Stage
{
    /**
     * @var Bucket\BucketOutput|null
     */
    protected $output;

    /**
     * @var Expr
     */
    protected $groupBy;

    /**
     * An expression to group documents by. To specify a field path, prefix the
     * field name with a dollar sign $ and enclose it in quotes.
     *
     * @param array|Expr $expression
     * @return $this
     */
    public function groupBy($expression)
    {
        $this->groupBy = $expression;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        $stage = [
            '$bucket' => [
                'groupBy' => Expr::convertExpression($this->groupBy),
            ] + $this->getExtraPipelineFields(),
        ];

        if ($this->output !== null) {
            $stage['$bucket']['output'] = $this->output->getExpression();
        }

        return $stage;
    }

    /**
     * @return array
     */
    abstract protected function getExtraPipelineFields();
}
