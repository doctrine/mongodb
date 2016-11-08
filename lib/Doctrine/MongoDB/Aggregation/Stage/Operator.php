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
 * @since 1.2
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
        parent::__construct($builder);

        $this->expr = $builder->expr();
    }

    /**
     * Returns the absolute value of a number.
     *
     * The <number> argument can be any valid expression as long as it resolves
     * to a number.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/abs/
     * @see Expr::abs
     * @param mixed|Expr $number
     * @return $this
     *
     * @since 1.3
     */
    public function abs($number)
    {
        $this->expr->abs($number);

        return $this;
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
     * @return $this
     */
    public function add($expression1, $expression2 /* , $expression3, ... */)
    {
        $this->expr->add(...func_get_args());

        return $this;
    }

    /**
     * Add one or more $and clauses to the current expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/and/
     * @see Expr::addAnd
     * @param array|Expr $expression
     * @return $this
     */
    public function addAnd($expression /* , $expression2, ... */)
    {
        $this->expr->addAnd(...func_get_args());

        return $this;
    }

    /**
     * Add one or more $or clauses to the current expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/or/
     * @see Expr::addOr
     * @param array|Expr $expression
     * @return $this
     */
    public function addOr($expression /* , $expression2, ... */)
    {
        $this->expr->addOr(...func_get_args());

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
     * @return $this
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
     * @return $this
     */
    public function anyElementTrue($expression)
    {
        $this->expr->anyElementTrue($expression);

        return $this;
    }

    /**
     * Returns the element at the specified array index.
     *
     * The <array> expression can be any valid expression as long as it resolves
     * to an array.
     * The <idx> expression can be any valid expression as long as it resolves
     * to an integer.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/arrayElemAt/
     * @see Expr::arrayElemAt
     * @param mixed|Expr $array
     * @param mixed|Expr $index
     * @return $this
     *
     * @since 1.3
     */
    public function arrayElemAt($array, $index)
    {
        $this->expr->arrayElemAt($array, $index);

        return $this;
    }

    /**
     * Returns the smallest integer greater than or equal to the specified number.
     *
     * The <number> expression can be any valid expression as long as it
     * resolves to a number.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/ceil/
     * @see Expr::ceil
     * @param mixed|Expr $number
     * @return $this
     *
     * @since 1.3
     */
    public function ceil($number)
    {
        $this->expr->ceil($number);

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
     * @return $this
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
     * @return $this
     */
    public function concat($expression1, $expression2 /* , $expression3, ... */)
    {
        $this->expr->concat(...func_get_args());

        return $this;
    }

    /**
     * Concatenates arrays to return the concatenated array.
     *
     * The <array> expressions can be any valid expression as long as they
     * resolve to an array.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/concatArrays/
     * @see Expr::concatArrays
     * @param mixed|Expr $array1
     * @param mixed|Expr $array2
     * @param mixed|Expr $array3, ... Additional expressions
     * @return $this
     *
     * @since 1.3
     */
    public function concatArrays($array1, $array2 /* , $array3, ... */)
    {
        $this->expr->concatArrays(...func_get_args());

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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function eq($expression1, $expression2)
    {
        $this->expr->eq($expression1, $expression2);

        return $this;
    }

    /**
     * Raises Euler’s number to the specified exponent and returns the result.
     *
     * The <exponent> expression can be any valid expression as long as it
     * resolves to a number.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/exp/
     * @see Expr::exp
     * @param mixed|Expr $exponent
     * @return $this
     *
     * @since 1.3
     */
    public function exp($exponent)
    {
        $this->expr->exp($exponent);

        return $this;
    }

    /**
     * Used to use an expression as field value. Can be any expression
     *
     * @see http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#aggregation-expressions
     * @see Expr::expression
     * @param mixed|Expr $value
     * @return $this
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
     * @return $this
     */
    public function field($fieldName)
    {
        $this->expr->field($fieldName);

        return $this;
    }

    /**
     * Selects a subset of the array to return based on the specified condition.
     *
     * Returns an array with only those elements that match the condition. The
     * returned elements are in the original order.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/filter/
     * @see Expr::filter
     * @param mixed|Expr $input
     * @param mixed|Expr $as
     * @param mixed|Expr $cond
     * @return $this
     *
     * @since 1.3
     */
    public function filter($input, $as, $cond)
    {
        $this->expr->filter($input, $as, $cond);

        return $this;
    }

    /**
     * Returns the largest integer less than or equal to the specified number.
     *
     * The <number> expression can be any valid expression as long as it
     * resolves to a number.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/floor/
     * @see Expr::floor
     * @param mixed|Expr $number
     * @return $this
     *
     * @since 1.3
     */
    public function floor($number)
    {
        $this->expr->floor($number);

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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function ifNull($expression, $replacementExpression)
    {
        $this->expr->ifNull($expression, $replacementExpression);

        return $this;
    }

    /**
     * Determines if the operand is an array. Returns a boolean.
     *
     * The <expression> can be any valid expression.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/isArray/
     * @see Expr::isArray
     * @param mixed|Expr $expression
     * @return $this
     *
     * @since 1.3
     */
    public function isArray($expression)
    {
        $this->expr->isArray($expression);

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
     * @return $this
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
     * @return $this
     */
    public function literal($value)
    {
        $this->expr->literal($value);

        return $this;
    }

    /**
     * Calculates the natural logarithm ln (i.e loge) of a number and returns
     * the result as a double.
     *
     * The <number> expression can be any valid expression as long as it
     * resolves to a non-negative number.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/log/
     * @see Expr::ln
     * @param mixed|Expr $number
     * @return $this
     *
     * @since 1.3
     */
    public function ln($number)
    {
        $this->expr->ln($number);

        return $this;
    }

    /**
     * Calculates the log of a number in the specified base and returns the
     * result as a double.
     *
     * The <number> expression can be any valid expression as long as it
     * resolves to a non-negative number.
     * The <base> expression can be any valid expression as long as it resolves
     * to a positive number greater than 1.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/log/
     * @see Expr::log
     * @param mixed|Expr $number
     * @param mixed|Expr $base
     * @return $this
     *
     * @since 1.3
     */
    public function log($number, $base)
    {
        $this->expr->log($number, $base);

        return $this;
    }

    /**
     * Calculates the log base 10 of a number and returns the result as a double.
     *
     * The <number> expression can be any valid expression as long as it
     * resolves to a non-negative number.
     * The <base> expression can be any valid expression as long as it resolves
     * to a positive number greater than 1.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/log/
     * @see Expr::log10
     * @param mixed|Expr $number
     * @return $this
     *
     * @since 1.3
     */
    public function log10($number)
    {
        $this->expr->log10($number);

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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function multiply($expression1, $expression2 /* , $expression3, ... */)
    {
        $this->expr->multiply(...func_get_args());

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
     * @return $this
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
     * @return $this
     */
    public function not($expression)
    {
        $this->expr->not($expression);

        return $this;
    }

    /**
     * Raises a number to the specified exponent and returns the result.
     *
     * The <number> expression can be any valid expression as long as it
     * resolves to a non-negative number.
     * The <exponent> expression can be any valid expression as long as it
     * resolves to a number.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/pow/
     * @see Expr::pow
     * @param mixed|Expr $number
     * @param mixed|Expr $exponent
     * @return $this
     *
     * @since 1.3
     */
    public function pow($number, $exponent)
    {
        $this->expr->pow($number, $exponent);

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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function setEquals($expression1, $expression2 /* , $expression3, ... */)
    {
        $this->expr->setEquals(...func_get_args());

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
     * @return $this
     */
    public function setIntersection($expression1, $expression2 /* , $expression3, ... */)
    {
        $this->expr->setIntersection(...func_get_args());

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
     * @return $this
     */
    public function setIsSubset($expression1, $expression2)
    {
        $this->expr->setIsSubset($expression1, $expression2);

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
     * @return $this
     */
    public function setUnion($expression1, $expression2 /* , $expression3, ... */)
    {
        $this->expr->setUnion(...func_get_args());

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
     * @return $this
     */
    public function size($expression)
    {
        $this->expr->size($expression);

        return $this;
    }

    /**
     * Returns a subset of an array.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/slice/
     * @see Expr::slice
     * @param mixed|Expr $array
     * @param mixed|Expr $n
     * @param mixed|Expr|null $position
     * @return $this
     *
     * @since 1.3
     */
    public function slice($array, $n, $position = null)
    {
        $this->expr->slice($array, $n, $position);

        return $this;
    }

    /**
     * Calculates the square root of a positive number and returns the result as
     * a double.
     *
     * The argument can be any valid expression as long as it resolves to a
     * non-negative number.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/sqrt/
     * @see Expr::sqrt
     * @param mixed|Expr $expression
     * @return $this
     */
    public function sqrt($expression)
    {
        $this->expr->sqrt($expression);

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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
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
     * @return $this
     */
    public function toUpper($expression)
    {
        $this->expr->toUpper($expression);

        return $this;
    }

    /**
     * Truncates a number to its integer.
     *
     * The <number> expression can be any valid expression as long as it
     * resolves to a number.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/trunc/
     * @see Expr::trunc
     * @param mixed|Expr $number
     * @return $this
     *
     * @since 1.3
     */
    public function trunc($number)
    {
        $this->expr->trunc($number);

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
     * @return $this
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
     * @return $this
     */
    public function year($expression)
    {
        $this->expr->year($expression);

        return $this;
    }
}
