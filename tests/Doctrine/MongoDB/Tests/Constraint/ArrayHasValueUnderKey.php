<?php
namespace Doctrine\MongoDB\Tests\Constraint;

class ArrayHasValueUnderKey extends \PHPUnit_Framework_Constraint
{
    private $key;
    private $value;

    public function __construct($key, $value)
    {
        $this->key   = $key;
        $this->value = $value;
    }

    public function evaluate($other, $description = '', $returnResult = FALSE)
    {
        if (!isset($other[$this->key])) {
            return false;
        }

        if ($other[$this->key] != $this->value) {
            return false;
        }

        return true;
    }

    public function toString()
    {
        return sprintf('has the value %s under key %s',
            \PHPUnit_Util_Type::toString($this->value),
            \PHPUnit_Util_Type::toString($this->key)
        );
    }

    protected function customFailureDescription($other, $description, $not)
    {
        return sprintf(
            'Failed asserting that an array %s.',
            $this->toString()
        );
    }
}
