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

use Doctrine\MongoDB\Aggregation\Builder;
use Doctrine\MongoDB\Aggregation\Expr;
use Doctrine\MongoDB\Aggregation\Stage;

/**
 * Fluent interface for adding operators to aggregation stages.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 */
abstract class Operator extends Stage
{
    /**
     * @var Expr
     */
    protected $expr;

    /**
     * {@inheritdoc}
     */
    public function __construct(Builder $builder)
    {
        $this->expr = new Expr();

        parent::__construct($builder);
    }

    /**
     * Adds numbers together or adds numbers and a date. If one of the arguments
     * is a date, $add treats the other arguments as milliseconds to add to the
     * date.
     *
     * The arguments can be any valid expression as long as they resolve to either all numbers or to numbers and a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/add/
     * @see Expr::add
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @param mixed|Expr $expression3,... Additional expressions
     * @return self
     */
    public function add($expression1, $expression2 /* , $expression3, ... */)
    {
        call_user_func_array(array($this->expr, 'add'), func_get_args());

        return $this;
    }

    /**
     * Add an $and clause to the current expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/and/
     * @see Expr::addAnd
     * @param array|Expr $expression
     * @return self
     */
    public function addAnd($expression)
    {
        $this->expr->addAnd($expression);

        return $this;
    }

    /**
     * Add an $or clause to the current expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/or/
     * @see Expr::addOr
     * @param array|Expr $expression
     * @return self
     */
    public function addOr($expression)
    {
        $this->expr->addOr($expression);

        return $this;
    }

    /**
     * Evaluates an array as a set and returns true if no element in the array
     * is false. Otherwise, returns false. An empty array returns true.
     *
     * The expression must resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/allElementsTrue/
     * @see Expr::allElementsTrue
     * @param mixed|Expr $expression
     * @return self
     */
    public function allElementsTrue($expression)
    {
        $this->expr->allElementsTrue($expression);

        return $this;
    }

    /**
     * Evaluates an array as a set and returns true if any of the elements are
     * true and false otherwise. An empty array returns false.
     *
     * The expression must resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/anyElementTrue/
     * @see Expr::anyElementTrue
     * @param array|Expr $expression
     * @return self
     */
    public function anyElementTrue($expression)
    {
        $this->expr->anyElementTrue($expression);

        return $this;
    }

    /**
     * Compares two values and returns:
     * -1 if the first value is less than the second.
     * 1 if the first value is greater than the second.
     * 0 if the two values are equivalent.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/cmp/
     * @see Expr::cmp
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function cmp($expression1, $expression2)
    {
        $this->expr->cmp($expression1, $expression2);

        return $this;
    }

    /**
     * Concatenates strings and returns the concatenated string.
     *
     * The arguments can be any valid expression as long as they resolve to
     * strings. If the argument resolves to a value of null or refers to a field
     * that is missing, $concat returns null.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/concat/
     * @see Expr::concat
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @param mixed|Expr $expression3,... Additional expressions
     * @return self
     */
    public function concat($expression1, $expression2 /* , $expression3, ... */)
    {
        call_user_func_array(array($this->expr, 'concat'), func_get_args());

        return $this;
    }

    /**
     * Evaluates a boolean expression to return one of the two specified return
     * expressions.
     *
     * The arguments can be any valid expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/cond/
     * @see Expr::cond
     * @param mixed|Expr $if
     * @param mixed|Expr $then
     * @param mixed|Expr $else
     * @return self
     */
    public function cond($if, $then, $else)
    {
        $this->expr->cond($if, $then, $else);

        return $this;
    }

    /**
     * Converts a date object to a string according to a user-specified format.
     *
     * The format string can be any string literal, containing 0 or more format
     * specifiers.
     * The date argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dateToString/
     * @see Expr::dateToString
     * @param string $format
     * @param mixed|Expr $expression
     * @return self
     */
    public function dateToString($format, $expression)
    {
        $this->expr->dateToString($format, $expression);

        return $this;
    }

    /**
     * Returns the day of the month for a date as a number between 1 and 31.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dayOfMonth/
     * @see Expr::dayOfMonth
     * @param mixed|Expr $expression
     * @return self
     */
    public function dayOfMonth($expression)
    {
        $this->expr->dayOfMonth($expression);

        return $this;
    }

    /**
     * Returns the day of the week for a date as a number between 1 (Sunday) and
     * 7 (Saturday).
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dayOfWeek/
     * @see Expr::dayOfWeek
     * @param mixed|Expr $expression
     * @return self
     */
    public function dayOfWeek($expression)
    {
        $this->expr->dayOfWeek($expression);

        return $this;
    }

    /**
     * Returns the day of the year for a date as a number between 1 and 366.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dayOfYear/
     * @see Expr::dayOfYear
     * @param mixed|Expr $expression
     * @return self
     */
    public function dayOfYear($expression)
    {
        $this->expr->dayOfYear($expression);

        return $this;
    }

    /**
     * Divides one number by another and returns the result. The first argument
     * is divided by the second argument.
     *
     * The arguments can be any valid expression as long as the resolve to numbers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/divide/
     * @see Expr::divide
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function divide($expression1, $expression2)
    {
        $this->expr->divide($expression1, $expression2);

        return $this;
    }

    /**
     * Compares two values and returns whether they are equivalent.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/eq/
     * @see Expr::eq
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function eq($expression1, $expression2)
    {
        $this->expr->eq($expression1, $expression2);

        return $this;
    }

    /**
     * Used to use an expression as field value. Can be any expression
     *
     * @see http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#aggregation-expressions
     * @see Expr::expression
     * @param mixed|Expr $value
     * @return self
     */
    public function expression($value)
    {
        $this->expr->expression($value);

        return $this;
    }

    /**
     * Set the current field for building the expression.
     *
     * @see Expr::field
     * @param string $fieldName
     * @return self
     */
    public function field($fieldName)
    {
        $this->expr->field($fieldName);

        return $this;
    }

    /**
     * Compares two values and returns:
     * true when the first value is greater than the second value.
     * false when the first value is less than or equivalent to the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/gt/
     * @see Expr::gt
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function gt($expression1, $expression2)
    {
        $this->expr->gt($expression1, $expression2);

        return $this;
    }

    /**
     * Compares two values and returns:
     * true when the first value is greater than or equivalent to the second value.
     * false when the first value is less than the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/gte/
     * @see Expr::gte
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function gte($expression1, $expression2)
    {
        $this->expr->gte($expression1, $expression2);

        return $this;
    }

    /**
     * Returns the hour portion of a date as a number between 0 and 23.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/hour/
     * @see Expr::hour
     * @param mixed|Expr $expression
     * @return self
     */
    public function hour($expression)
    {
        $this->expr->hour($expression);

        return $this;
    }

    /**
     * Evaluates an expression and returns the value of the expression if the
     * expression evaluates to a non-null value. If the expression evaluates to
     * a null value, including instances of undefined values or missing fields,
     * returns the value of the replacement expression.
     *
     * The arguments can be any valid expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/ifNull/
     * @see Expr::ifNull
     * @param mixed|Expr $expression
     * @param mixed|Expr $replacementExpression
     * @return self
     */
    public function ifNull($expression, $replacementExpression)
    {
        $this->expr->ifNull($expression, $replacementExpression);

        return $this;
    }

    /**
     * Binds variables for use in the specified expression, and returns the
     * result of the expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/let/
     * @see Expr::let
     * @param mixed|Expr $vars Assignment block for the variables accessible in the in expression. To assign a variable, specify a string for the variable name and assign a valid expression for the value.
     * @param mixed|Expr $in   The expression to evaluate.
     * @return self
     */
    public function let($vars, $in)
    {
        $this->expr->let($vars, $in);

        return $this;
    }

    /**
     * Returns a value without parsing. Use for values that the aggregation
     * pipeline may interpret as an expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/literal/
     * @see Expr::literal
     * @param mixed|Expr $value
     * @return self
     */
    public function literal($value)
    {
        $this->expr->literal($value);

        return $this;
    }

    /**
     * Compares two values and returns:
     * true when the first value is less than the second value.
     * false when the first value is greater than or equivalent to the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/lt/
     * @see Expr::lt
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function lt($expression1, $expression2)
    {
        $this->expr->lt($expression1, $expression2);

        return $this;
    }

    /**
     * Compares two values and returns:
     * true when the first value is less than or equivalent to the second value.
     * false when the first value is greater than the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/lte/
     * @see Expr::lte
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function lte($expression1, $expression2)
    {
        $this->expr->lte($expression1, $expression2);

        return $this;
    }

    /**
     * Applies an expression to each item in an array and returns an array with
     * the applied results.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/map/
     * @see Expr::map
     * @param mixed|Expr $input An expression that resolves to an array.
     * @param string $as        The variable name for the items in the input array. The in expression accesses each item in the input array by this variable.
     * @param mixed|Expr $in    The expression to apply to each item in the input array. The expression accesses the item by its variable name.
     * @return self
     */
    public function map($input, $as, $in)
    {
        $this->expr->map($input, $as, $in);

        return $this;
    }

    /**
     * Returns the metadata associated with a document in a pipeline operations.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/meta/
     * @see Expr::meta
     * @param $metaDataKeyword
     * @return self
     */
    public function meta($metaDataKeyword)
    {
        $this->expr->meta($metaDataKeyword);

        return $this;
    }

    /**
     * Returns the millisecond portion of a date as an integer between 0 and 999.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/millisecond/
     * @see Expr::millisecond
     * @param mixed|Expr $expression
     * @return self
     */
    public function millisecond($expression)
    {
        $this->expr->millisecond($expression);

        return $this;
    }

    /**
     * Returns the minute portion of a date as a number between 0 and 59.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/minute/
     * @see Expr::minute
     * @param mixed|Expr $expression
     * @return self
     */
    public function minute($expression)
    {
        $this->expr->minute($expression);

        return $this;
    }

    /**
     * Divides one number by another and returns the remainder. The first
     * argument is divided by the second argument.
     *
     * The arguments can be any valid expression as long as they resolve to numbers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/mod/
     * @see Expr::mod
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function mod($expression1, $expression2)
    {
        $this->expr->mod($expression1, $expression2);

        return $this;
    }

    /**
     * Returns the month of a date as a number between 1 and 12.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/month/
     * @see Expr::month
     * @param mixed|Expr $expression
     * @return self
     */
    public function month($expression)
    {
        $this->expr->month($expression);

        return $this;
    }

    /**
     * Multiplies numbers together and returns the result.
     *
     * The arguments can be any valid expression as long as they resolve to numbers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/multiply/
     * @see Expr::multiply
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @param mixed|Expr $expression3,... Additional expressions
     * @return self
     */
    public function multiply($expression1, $expression2 /* , $expression3, ... */)
    {
        call_user_func_array(array($this->expr, 'multiply'), func_get_args());

        return $this;
    }

    /**
     * Compares two values and returns:
     * true when the values are not equivalent.
     * false when the values are equivalent.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/ne/
     * @see Expr::ne
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function ne($expression1, $expression2)
    {
        $this->expr->ne($expression1, $expression2);

        return $this;
    }

    /**
     * Evaluates a boolean and returns the opposite boolean value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/not/
     * @see Expr::not
     * @param mixed|Expr $expression
     * @return self
     */
    public function not($expression)
    {
        $this->expr->not($expression);

        return $this;
    }

    /**
     * Returns the second portion of a date as a number between 0 and 59, but
     * can be 60 to account for leap seconds.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/second/
     * @see Expr::second
     * @param mixed|Expr $expression
     * @return self
     */
    public function second($expression)
    {
        $this->expr->second($expression);

        return $this;
    }

    /**
     * Takes two sets and returns an array containing the elements that only
     * exist in the first set.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setDifference/
     * @see Expr::setDifference
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function setDifference($expression1, $expression2)
    {
        $this->expr->setDifference($expression1, $expression2);

        return $this;
    }

    /**
     * Compares two or more arrays and returns true if they have the same
     * distinct elements and false otherwise.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setEquals/
     * @see Expr::setEquals
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @param mixed|Expr $expression3,...   Additional sets
     * @return self
     */
    public function setEquals($expression1, $expression2 /* , $expression3, ... */)
    {
        call_user_func_array(array($this->expr, 'setEquals'), func_get_args());

        return $this;
    }

    /**
     * Takes two or more arrays and returns an array that contains the elements
     * that appear in every input array.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setIntersection/
     * @see Expr::setIntersection
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @param mixed|Expr $expression3,...   Additional sets
     * @return self
     */
    public function setIntersection($expression1, $expression2 /* , $expression3, ... */)
    {
        call_user_func_array(array($this->expr, 'setIntersection'), func_get_args());

        return $this;
    }

    /**
     * Takes two arrays and returns true when the first array is a subset of the
     * second, including when the first array equals the second array, and false
     * otherwise.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setIsSubset/
     * @see Expr::setIsSubset
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function setIsSubset($expression1, $expression2)
    {
        call_user_func_array(array($this->expr, 'setIsSubset'), func_get_args());

        return $this;
    }

    /**
     * Takes two or more arrays and returns an array containing the elements
     * that appear in any input array.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setUnion/
     * @see Expr::setUnion
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @param mixed|Expr $expression3,...   Additional sets
     * @return self
     */
    public function setUnion($expression1, $expression2 /* , $expression3, ... */)
    {
        call_user_func_array(array($this->expr, 'setUnion'), func_get_args());

        return $this;
    }

    /**
     * Counts and returns the total the number of items in an array.
     *
     * The argument can be any expression as long as it resolves to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/size/
     * @see Expr::size
     * @param mixed|Expr $expression
     * @return self
     */
    public function size($expression)
    {
        $this->expr->size($expression);

        return $this;
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
     * @see Expr::strcasecmp
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function strcasecmp($expression1, $expression2)
    {
        $this->expr->strcasecmp($expression1, $expression2);

        return $this;
    }

    /**
     * Returns a substring of a string, starting at a specified index position
     * and including the specified number of characters. The index is zero-based.
     *
     * The arguments can be any valid expression as long as long as the first argument resolves to a string, and the second and third arguments resolve to integers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/substr/
     * @see Expr::substr
     * @param mixed|Expr $string
     * @param mixed|Expr $start
     * @param mixed|Expr $length
     * @return self
     */
    public function substr($string, $start, $length)
    {
        $this->expr->substr($string, $start, $length);

        return $this;
    }

    /**
     * Subtracts two numbers to return the difference. The second argument is
     * subtracted from the first argument.
     *
     * The arguments can be any valid expression as long as they resolve to numbers and/or dates.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/subtract/
     * @see Expr::subtract
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2
     * @return self
     */
    public function subtract($expression1, $expression2)
    {
        $this->expr->subtract($expression1, $expression2);

        return $this;
    }

    /**
     * Converts a string to lowercase, returning the result.
     *
     * The argument can be any expression as long as it resolves to a string.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/toLower/
     * @see Expr::toLower
     * @param mixed|Expr $expression
     * @return self
     */
    public function toLower($expression)
    {
        $this->expr->toLower($expression);

        return $this;
    }

    /**
     * Converts a string to uppercase, returning the result.
     *
     * The argument can be any expression as long as it resolves to a string.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/toUpper/
     * @see Expr::toUpper
     * @param mixed|Expr $expression
     * @return self
     */
    public function toUpper($expression)
    {
        $this->expr->toUpper($expression);

        return $this;
    }

    /**
     * Returns the week of the year for a date as a number between 0 and 53.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/week/
     * @see Expr::week
     * @param mixed|Expr $expression
     * @return self
     */
    public function week($expression)
    {
        $this->expr->week($expression);

        return $this;
    }

    /**
     * Returns the year portion of a date.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/year/
     * @see Expr::year
     * @param mixed|Expr $expression
     * @return self
     */
    public function year($expression)
    {
        $this->expr->year($expression);

        return $this;
    }
}
