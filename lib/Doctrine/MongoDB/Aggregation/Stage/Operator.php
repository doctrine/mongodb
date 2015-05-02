<?php
/*
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\MongoDB\Aggregation\Stage;

use Doctrine\MongoDB\Aggregation\Stage;
use LogicException;

/**
 * Fluent interface for adding operators to aggregation stages.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 */
class Operator extends Stage
{
    /**
     * @var array
     */
    protected $expr = array();

    /**
     * The current field we are operating on.
     *
     * @var string
     */
    protected $currentField;

    /**
     * Adds numbers together or adds numbers and a date. If one of the arguments is a date, $add treats the other arguments as milliseconds to add to the date.
     *
     * The arguments can be any valid expression as long as they resolve to either all numbers or to numbers and a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/add/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @param mixed|Operator $expression3,... Additional expressions
     * @return $this
     */
    public function add($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$add', func_get_args());
    }

    /**
     * Add an $and clause to the current expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/and/
     * @param array|Operator $expression
     * @return $this
     */
    public function addAnd($expression)
    {
        $this->expr['$and'][] = $this->ensureArray($expression);

        return $this;
    }

    /**
     * Add an $or clause to the current expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/or/
     * @param array|Operator $expression
     * @return $this
     */
    public function addOr($expression)
    {
        $this->expr['$or'][] = $this->ensureArray($expression);

        return $this;
    }

    /**
     * Evaluates an array as a set and returns true if no element in the array is false. Otherwise, returns false. An empty array returns true.
     *
     * The expression must resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/allElementsTrue/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function allElementsTrue($expression)
    {
        return $this->operator('$allElementsTrue', $expression);
    }

    /**
     * Evaluates an array as a set and returns true if any of the elements are true and false otherwise. An empty array returns false.
     *
     * The expression must resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/anyElementTrue/
     * @param array|Operator $expression
     * @return $this
     */
    public function anyElementTrue($expression)
    {
        return $this->operator('$anyElementTrue', $expression);
    }

    /**
     * Compares two values and returns:
     * -1 if the first value is less than the second.
     * 1 if the first value is greater than the second.
     * 0 if the two values are equivalent.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/cmp/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function cmp($expression1, $expression2)
    {
        return $this->operator('$cmp', array($expression1, $expression2));
    }

    /**
     * Concatenates strings and returns the concatenated string.
     *
     * The arguments can be any valid expression as long as they resolve to strings. If the argument resolves to a value of null or refers to a field that is missing, $concat returns null.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/concat/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @param mixed|Operator $expression3,... Additional expressions
     * @return $this
     */
    public function concat($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$concat', func_get_args());
    }

    /**
     * Evaluates a boolean expression to return one of the two specified return expressions.
     *
     * The arguments can be any valid expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/cond/
     * @param mixed|Operator $if
     * @param mixed|Operator $then
     * @param mixed|Operator $else
     * @return $this
     */
    public function cond($if, $then, $else)
    {
        return $this->operator('$cond', array('if' => $if, 'then' => $then, 'else' => $else));
    }

    /**
     * Ensures an array or operator expression is converted to an array.
     *
     * @param mixed|Operator $expression
     * @return mixed
     */
    protected function ensureArray($expression)
    {
        if (is_array($expression)) {
            $array = array();
            foreach ($expression as $index => $value) {
                $array[$index] = $this->ensureArray($value);
            }

            return $array;
        } elseif ($expression instanceof Operator) {
            return $expression->getExpression();
        }

        return $expression;
    }

    /**
     * Converts a date object to a string according to a user-specified format.
     *
     * The format string can be any string literal, containing 0 or more format specifiers.
     * The date argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dateToString/
     * @param string $format
     * @param mixed|Operator $expression
     * @return $this
     */
    public function dateToString($format, $expression)
    {
        return $this->operator('$dateToString', array($format, $expression));
    }

    /**
     * Returns the day of the month for a date as a number between 1 and 31.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dayOfMonth/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function dayOfMonth($expression)
    {
        return $this->operator('$dayOfMonth', $expression);
    }

    /**
     * Returns the day of the week for a date as a number between 1 (Sunday) and 7 (Saturday).
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dayOfWeek/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function dayOfWeek($expression)
    {
        return $this->operator('$dayOfWeek', $expression);
    }

    /**
     * Returns the day of the year for a date as a number between 1 and 366.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dayOfYear/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function dayOfYear($expression)
    {
        return $this->operator('$dayOfYear', $expression);
    }

    /**
     * Divides one number by another and returns the result. The first argument is divided by the second argument.
     *
     * The arguments can be any valid expression as long as the resolve to numbers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/divide/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function divide($expression1, $expression2)
    {
        return $this->operator('$divide', array($expression1, $expression2));
    }

    /**
     * Compares two values and returns:
     * true when the values are equivalent.
     * false when the values are not equivalent.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/eq/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function eq($expression1, $expression2)
    {
        return $this->operator('$eq', array($expression1, $expression2));
    }

    /**
     * Set the current field for building the expression.
     *
     * @param string $fieldName
     * @return $this
     */
    public function field($fieldName)
    {
        $this->currentField = (string) $fieldName;

        return $this;
    }

    /**
     * @return array
     */
    public function getExpression()
    {
        return $this->expr;
    }

    /**
     * Compares two values and returns:
     * true when the first value is greater than the second value.
     * false when the first value is less than or equivalent to the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/gt/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function gt($expression1, $expression2)
    {
        return $this->operator('$gt', array($expression1, $expression2));
    }

    /**
     * Compares two values and returns:
     * true when the first value is greater than or equivalent to the second value.
     * false when the first value is less than the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/gte/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function gte($expression1, $expression2)
    {
        return $this->operator('$gte', array($expression1, $expression2));
    }

    /**
     * Returns the hour portion of a date as a number between 0 and 23.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/hour/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function hour($expression)
    {
        return $this->operator('$hour', $expression);
    }

    /**
     * Evaluates an expression and returns the value of the expression if the expression evaluates to a non-null value. If the expression evaluates to a null value, including instances of undefined values or missing fields, returns the value of the replacement expression.
     *
     * The arguments can be any valid expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/ifNull/
     * @param mixed|Operator $expression
     * @param mixed|Operator $replacementExpression
     * @return $this
     */
    public function ifNull($expression, $replacementExpression)
    {
        return $this->operator('$ifNull', array($expression, $replacementExpression));
    }

    /**
     * Binds variables for use in the specified expression, and returns the result of the expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/let/
     * @param mixed|Operator $vars Assignment block for the variables accessible in the in expression. To assign a variable, specify a string for the variable name and assign a valid expression for the value.
     * @param mixed|Operator $in   The expression to evaluate.
     * @return $this
     */
    public function let($vars, $in)
    {
        return $this->operator('$let', array('vars' => $vars, 'in' => $in));
    }

    /**
     * Returns a value without parsing. Use for values that the aggregation pipeline may interpret as an expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/literal/
     * @param mixed|Operator $value
     * @return $this
     */
    public function literal($value)
    {
        return $this->operator('$literal', $value);
    }

    /**
     * Compares two values and returns:
     * true when the first value is less than the second value.
     * false when the first value is greater than or equivalent to the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/lt/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function lt($expression1, $expression2)
    {
        return $this->operator('$lt', array($expression1, $expression2));
    }

    /**
     * Compares two values and returns:
     * true when the first value is less than or equivalent to the second value.
     * false when the first value is greater than the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/lte/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function lte($expression1, $expression2)
    {
        return $this->operator('$lte', array($expression1, $expression2));
    }

    /**
     * Applies an expression to each item in an array and returns an array with the applied results.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/map/
     * @param mixed|Operator $input An expression that resolves to an array.
     * @param string $as        The variable name for the items in the input array. The in expression accesses each item in the input array by this variable.
     * @param mixed|Operator $in    The expression to apply to each item in the input array. The expression accesses the item by its variable name.
     * @return $this
     */
    public function map($input, $as, $in)
    {
        return $this->operator('$map', array('input' => $input, 'as' => $as, 'in' => $in));
    }

    /**
     * Returns the metadata associated with a document in a pipeline operations.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/meta/
     * @param $metaDataKeyword
     * @return $this
     */
    public function meta($metaDataKeyword)
    {
        return $this->operator('$meta', $metaDataKeyword);
    }

    /**
     * Returns the millisecond portion of a date as an integer between 0 and 999.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/millisecond/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function millisecond($expression)
    {
        return $this->operator('$millisecond', $expression);
    }

    /**
     * Returns the minute portion of a date as a number between 0 and 59.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/minute/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function minute($expression)
    {
        return $this->operator('$minute', $expression);
    }

    /**
     * Divides one number by another and returns the remainder. The first argument is divided by the second argument.
     *
     * The arguments can be any valid expression as long as they resolve to numbers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/mod/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function mod($expression1, $expression2)
    {
        return $this->operator('$mod', array($expression1, $expression2));
    }

    /**
     * Returns the month of a date as a number between 1 and 12.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/month/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function month($expression)
    {
        return $this->operator('$month', $expression);
    }

    /**
     * Multiplies numbers together and returns the result.
     *
     * The arguments can be any valid expression as long as they resolve to numbers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/multiply/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @param mixed|Operator $expression3,... Additional expressions
     * @return $this
     */
    public function multiply($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$multiply', func_get_args());
    }

    /**
     * Compares two values and returns:
     * true when the values are not equivalent.
     * false when the values are equivalent.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/ne/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function ne($expression1, $expression2)
    {
        return $this->operator('$ne', array($expression1, $expression2));
    }

    /**
     * Evaluates a boolean and returns the opposite boolean value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/not/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function not($expression)
    {
        return $this->operator('$not', $expression);
    }

    /**
     * Defines an operator and value on the expression.
     *
     * If there is a current field, the operator will be set on it; otherwise,
     * the operator is set at the top level of the query.
     *
     * @param string $operator
     * @param array|Operator[]|Operator $expression
     * @return self
     */
    public function operator($operator, $expression)
    {
        if ($this->currentField) {
            $this->expr[$this->currentField][$operator] = $this->ensureArray($expression);
        } else {
            $this->expr[$operator] = $this->ensureArray($expression);
        }

        return $this;
    }

    /**
     * Ensure that a current field has been set.
     *
     * @throws LogicException if a current field has not been set
     */
    protected function requiresCurrentField()
    {
        if (!$this->currentField) {
            throw new LogicException('This method requires you set a current field using field().');
        }
    }

    /**
     * Returns the second portion of a date as a number between 0 and 59, but can be 60 to account for leap seconds.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/second/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function second($expression)
    {
        return $this->operator('$second', $expression);
    }

    /**
     * Takes two sets and returns an array containing the elements that only exist in the first set.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setDifference/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function setDifference($expression1, $expression2)
    {
        return $this->operator('$setDifference', array($expression1, $expression2));
    }

    /**
     * Compares two or more arrays and returns true if they have the same distinct elements and false otherwise.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setEquals/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @param mixed|Operator $expression3,...   Additional sets
     * @return $this
     */
    public function setEquals($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$setEquals', func_get_args());
    }

    /**
     * Takes two or more arrays and returns an array that contains the elements that appear in every input array.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setIntersection/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @param mixed|Operator $expression3,...   Additional sets
     * @return $this
     */
    public function setIntersection($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$setIntersection', func_get_args());
    }

    /**
     * Takes two arrays and returns true when the first array is a subset of the second, including when the first array equals the second array, and false otherwise.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setIsSubset/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function setIsSubset($expression1, $expression2)
    {
        return $this->operator('$setIsSubset', array($expression1, $expression2));
    }

    /**
     * Takes two or more arrays and returns an array containing the elements that appear in any input array.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setUnion/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @param mixed|Operator $expression3,...   Additional sets
     * @return $this
     */
    public function setUnion($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$setUnion', func_get_args());
    }

    /**
     * Counts and returns the total the number of items in an array.
     *
     * The argument can be any expression as long as it resolves to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/size/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function size($expression)
    {
        return $this->operator('$size', $expression);
    }

    /**
     * Performs case-insensitive comparison of two strings. Returns
     * 1 if first string is “greater than” the second string.
     * 0 if the two strings are equal.
     * -1 if the first string is “less than” the second string.
     *
     * The arguments can be any valid expression as long as they resolve to strings.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/strcasecmp/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function strcasecmp($expression1, $expression2)
    {
        return $this->operator('$strcasecmp', array($expression1, $expression2));
    }

    /**
     * Returns a substring of a string, starting at a specified index position and including the specified number of characters. The index is zero-based.
     *
     * The arguments can be any valid expression as long as long as the first argument resolves to a string, and the second and third arguments resolve to integers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/substr/
     * @param mixed|Operator $string
     * @param mixed|Operator $start
     * @param mixed|Operator $length
     * @return $this
     */
    public function substr($string, $start, $length)
    {
        return $this->operator('$substr', array($string, $start, $length));
    }

    /**
     * Subtracts two numbers to return the difference. The second argument is subtracted from the first argument.
     *
     * The arguments can be any valid expression as long as they resolve to numbers and/or dates.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/subtract/
     * @param mixed|Operator $expression1
     * @param mixed|Operator $expression2
     * @return $this
     */
    public function subtract($expression1, $expression2)
    {
        return $this->operator('$subtract', array($expression1, $expression2));
    }

    /**
     * Converts a string to lowercase, returning the result.
     *
     * The argument can be any expression as long as it resolves to a string.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/toLower/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function toLower($expression)
    {
        return $this->operator('$toLower', $expression);
    }

    /**
     * Converts a string to uppercase, returning the result.
     *
     * The argument can be any expression as long as it resolves to a string.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/toUpper/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function toUpper($expression)
    {
        return $this->operator('$toUpper', $expression);
    }

    /**
     * Used to set a value directly. Similar to $literal, except that the pipeline will parse any expressions contained.
     *
     * @param mixed|Operator $value
     * @return $this
     */
    public function value($value)
    {
        $this->requiresCurrentField();
        $this->expr[$this->currentField] = $this->ensureArray($value);

        return $this;
    }

    /**
     * Returns the week of the year for a date as a number between 0 and 53.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/week/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function week($expression)
    {
        return $this->operator('$week', $expression);
    }

    /**
     * Returns the year portion of a date.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/year/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function year($expression)
    {
        return $this->operator('$year', $expression);
    }
}
