<?php

namespace Doctrine\MongoDB\Tests;

use PHPUnit\Framework\Constraint\ArraySubset;
use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    public function arraySubset($value, $strict = false)
    {
        $className = class_exists(ArraySubset::class) ? ArraySubset::class : \PHPUnit_Framework_Constraint_ArraySubset::class;

        return new $className($value, $strict);
    }
}
