<?php

namespace Doctrine\MongoDB\Tests\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage\Operator;
use Doctrine\MongoDB\Aggregation\Expr;

class OperatorStub extends Operator
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
