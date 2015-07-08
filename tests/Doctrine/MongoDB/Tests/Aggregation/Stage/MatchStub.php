<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Match;
use Doctrine\MongoDB\Query\Expr;

class MatchStub extends Match
{
    public function setQuery(Expr $query)
    {
        $this->query = $query;
    }
}
