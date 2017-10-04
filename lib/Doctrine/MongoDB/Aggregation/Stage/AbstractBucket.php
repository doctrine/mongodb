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
            $this->getStageName() => [
                'groupBy' => $this->convertExpression($this->groupBy),
            ] + $this->getExtraPipelineFields(),
        ];

        if ($this->output !== null) {
            $stage[$this->getStageName()]['output'] = $this->output->getExpression();
        }

        return $stage;
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

    /**
     * @return array
     */
    abstract protected function getExtraPipelineFields();

    /**
     * Returns the stage name with the dollar prefix
     *
     * @return string
     */
    abstract protected function getStageName();
}
