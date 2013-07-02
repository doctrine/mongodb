<?php

namespace Doctrine\MongoDB\Tests\Query;

use Doctrine\MongoDB\Query\Builder;

class BuilderStub extends Builder
{
    public function setExpr($expr)
    {
        $this->expr = $expr;
    }
}
