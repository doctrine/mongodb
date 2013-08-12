<?php

namespace Doctrine\MongoDB\Tests\Constraint;

class ArrayHasKeyAndValue extends \PHPUnit_Framework_Constraint
{
    private $key;
    private $constraint;

    /**
     * Constructor.
     *
     * If the $value is not a constraint, it will be used as the value for an
     * PHPUnit_Framework_Constraint_IsEqual instance.
     *
     * @param mixed $key   The array key, which must exist
     * @param mixed $value Expected value for the array key or a constraint
     */
    public function __construct($key, $value)
    {
        if ( ! $value instanceof \PHPUnit_Framework_Constraint) {
            $value = new \PHPUnit_Framework_Constraint_IsEqual($value);
        }

        $this->key = $key;
        $this->constraint = $value;
    }

    /**
     * @see \PHPUnit_Framework_Constraint::toString()
     */
    public function toString()
    {
        return sprintf(
            'has the key %s and its value %s',
            \PHPUnit_Util_Type::export($this->key),
            $this->constraint->toString()
        );
    }

    /**
     * @see \PHPUnit_Framework_Constraint::matches()
     */
    protected function matches($other)
    {
        if ( ! is_array($other)) {
            return false;
        }

        if ( ! array_key_exists($this->key, $other)) {
            return false;
        }

        if ( ! $this->constraint->evaluate($other[$this->key], '', true)) {
            return false;
        }

        return true;
    }
}
