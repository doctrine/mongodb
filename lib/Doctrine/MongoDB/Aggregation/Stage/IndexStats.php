<?php

namespace Doctrine\MongoDB\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage;

/**
 * Fluent interface for adding a $indexStats stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.3
 */
class IndexStats extends Stage
{
    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return [
            '$indexStats' => new \stdClass()
        ];
    }
}
