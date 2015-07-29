<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Group;
use Doctrine\MongoDB\Aggregation\Expr;

class GroupStub extends Group
{
    public function setQuery(Expr $query)
    {
        $this->expr = $query;
    }

    public function getExpression()
    {
        return $this->expr->getExpression();
    }
}
