<?php

namespace Doctrine\MongoDB\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Stage;

/**
 * Fluent interface for adding a $bucketAuto stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.5
 */
class BucketAuto extends AbstractBucket
{
    /**
     * @var int
     */
    private $buckets;

    /**
     * @var string
     */
    private $granularity;

    /**
     * A positive 32-bit integer that specifies the number of buckets into which
     * input documents are grouped.
     *
     * @param int $buckets
     *
     * @return $this
     */
    public function buckets($buckets)
    {
        $this->buckets = $buckets;
        return $this;
    }

    /**
     * A string that specifies the preferred number series to use to ensure that
     * the calculated boundary edges end on preferred round numbers or their
     * powers of 10.
     *
     * @param string $granularity
     *
     * @return $this
     */
    public function granularity($granularity)
    {
        $this->granularity = $granularity;
        return $this;
    }

    /**
     * A document that specifies the fields to include in the output documents
     * in addition to the _id field. To specify the field to include, you must
     * use accumulator expressions.
     *
     * @return Bucket\BucketAutoOutput
     */
    public function output()
    {
        if (! $this->output) {
            $this->output = new Stage\Bucket\BucketAutoOutput($this->builder, $this);
        }

        return $this->output;
    }

    /**
     * {@inheritdoc}
     */
    protected function getExtraPipelineFields()
    {
        $fields = ['buckets' => $this->buckets];
        if ($this->granularity !== null) {
            $fields['granularity'] = $this->granularity;
        }

        return $fields;
    }

    protected function getStageName()
    {
        return '$bucketAuto';
    }
}
