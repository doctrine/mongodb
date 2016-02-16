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

namespace Doctrine\MongoDB\Aggregation;

use LogicException;

/**
 * Fluent interface for adding operators to aggregation stages.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 */
class Expr
{
    /**
     * @var array
     */
    private $expr = array();

    /**
     * The current field we are operating on.
     *
     * @var string
     */
    private $currentField;

    /**
     * Adds numbers together or adds numbers and a date. If one of the arguments
     * is a date, $add treats the other arguments as milliseconds to add to the
     * date.
     *
     * The arguments can be any valid expression as long as they resolve to
     * either all numbers or to numbers and a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/add/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @param mixed|self $expression3,... Additional expressions
     * @return self
     */
    public function add($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$add', func_get_args());
    }

    /**
     * Add an $and clause to the current expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/and/
     * @param array|self $expression
     * @return self
     */
    public function addAnd($expression)
    {
        if ($this->currentField) {
            $this->expr[$this->currentField]['$and'][] = $this->ensureArray($expression);
        } else {
            $this->expr['$and'][] = $this->ensureArray($expression);
        }

        return $this;
    }

    /**
     * Add an $or clause to the current expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/or/
     * @param array|self $expression
     * @return self
     */
    public function addOr($expression)
    {
        if ($this->currentField) {
            $this->expr[$this->currentField]['$or'][] = $this->ensureArray($expression);
        } else {
            $this->expr['$or'][] = $this->ensureArray($expression);
        }

        return $this;
    }

    /**
     * Returns an array of all unique values that results from applying an
     * expression to each document in a group of documents that share the same
     * group by key. Order of the elements in the output array is unspecified.
     *
     * AddToSet is an accumulator operation only available in the group stage.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/addToSet/
     * @param mixed|self $expression
     * @return self
     */
    public function addToSet($expression)
    {
        return $this->operator('$addToSet', $expression);
    }

    /**
     * Evaluates an array as a set and returns true if no element in the array
     * is false. Otherwise, returns false. An empty array returns true.
     *
     * The expression must resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/allElementsTrue/
     * @param mixed|self $expression
     * @return self
     */
    public function allElementsTrue($expression)
    {
        return $this->operator('$allElementsTrue', $expression);
    }

    /**
     * Evaluates an array as a set and returns true if any of the elements are
     * true and false otherwise. An empty array returns false.
     *
     * The expression must resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/anyElementTrue/
     * @param array|self $expression
     * @return self
     */
    public function anyElementTrue($expression)
    {
        return $this->operator('$anyElementTrue', $expression);
    }

    /**
     * Returns the average value of the numeric values that result from applying
     * a specified expression to each document in a group of documents that
     * share the same group by key. Ignores nun-numeric values.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/avg/
     * @param mixed|self $expression
     * @return self
     */
    public function avg($expression)
    {
        return $this->operator('$avg', $expression);
    }

    /**
     * Compares two values and returns:
     * -1 if the first value is less than the second.
     * 1 if the first value is greater than the second.
     * 0 if the two values are equivalent.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/cmp/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function cmp($expression1, $expression2)
    {
        return $this->operator('$cmp', array($expression1, $expression2));
    }

    /**
     * Concatenates strings and returns the concatenated string.
     *
     * The arguments can be any valid expression as long as they resolve to
     * strings. If the argument resolves to a value of null or refers to a field
     * that is missing, $concat returns null.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/concat/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @param mixed|self $expression3,... Additional expressions
     * @return self
     */
    public function concat($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$concat', func_get_args());
    }

    /**
     * Evaluates a boolean expression to return one of the two specified return
     * expressions.
     *
     * The arguments can be any valid expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/cond/
     * @param mixed|self $if
     * @param mixed|self $then
     * @param mixed|self $else
     * @return self
     */
    public function cond($if, $then, $else)
    {
        return $this->operator('$cond', array('if' => $if, 'then' => $then, 'else' => $else));
    }

    /**
     * Ensures an array or operator expression is converted to an array.
     *
     * @param mixed|self $expression
     * @return mixed
     */
    private function ensureArray($expression)
    {
        if (is_array($expression)) {
            $array = array();
            foreach ($expression as $index => $value) {
                $array[$index] = $this->ensureArray($value);
            }

            return $array;
        } elseif ($expression instanceof self) {
            return $expression->getExpression();
        }

        return $expression;
    }

    /**
     * Converts a date object to a string according to a user-specified format.
     *
     * The format string can be any string literal, containing 0 or more format
     * specifiers.
     * The date argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dateToString/
     * @param string $format
     * @param mixed|self $expression
     * @return self
     */
    public function dateToString($format, $expression)
    {
        return $this->operator('$dateToString', array('format' => $format, 'date' => $expression));
    }

    /**
     * Returns the day of the month for a date as a number between 1 and 31.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dayOfMonth/
     * @param mixed|self $expression
     * @return self
     */
    public function dayOfMonth($expression)
    {
        return $this->operator('$dayOfMonth', $expression);
    }

    /**
     * Returns the day of the week for a date as a number between 1 (Sunday) and
     * 7 (Saturday).
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/dayOfWeek/
     * @param mixed|self $expression
     * @return self
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
     * @param mixed|self $expression
     * @return self
     */
    public function dayOfYear($expression)
    {
        return $this->operator('$dayOfYear', $expression);
    }

    /**
     * Divides one number by another and returns the result. The first argument
     * is divided by the second argument.
     *
     * The arguments can be any valid expression as long as the resolve to numbers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/divide/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function divide($expression1, $expression2)
    {
        return $this->operator('$divide', array($expression1, $expression2));
    }

    /**
     * Compares two values and returns whether the are equivalent.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/eq/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function eq($expression1, $expression2)
    {
        return $this->operator('$eq', array($expression1, $expression2));
    }

    /**
     * Allows any expression to be used as a field value.
     *
     * @see http://docs.mongodb.org/manual/meta/aggregation-quick-reference/#aggregation-expressions
     * @param mixed|self $value
     * @return self
     */
    public function expression($value)
    {
        $this->requiresCurrentField(__METHOD__);
        $this->expr[$this->currentField] = $this->ensureArray($value);

        return $this;
    }

    /**
     * Set the current field for building the expression.
     *
     * @param string $fieldName
     * @return self
     */
    public function field($fieldName)
    {
        $this->currentField = (string) $fieldName;

        return $this;
    }

    /**
     * Returns the value that results from applying an expression to the first
     * document in a group of documents that share the same group by key. Only
     * meaningful when documents are in a defined order.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/first/
     * @param mixed|self $expression
     * @return self
     */
    public function first($expression)
    {
        return $this->operator('$first', $expression);
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
     * false when the first value is less than or equivalent to the second
     * value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/gt/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function gt($expression1, $expression2)
    {
        return $this->operator('$gt', array($expression1, $expression2));
    }

    /**
     * Compares two values and returns:
     * true when the first value is greater than or equivalent to the second
     * value.
     * false when the first value is less than the second value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/gte/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
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
     * @param mixed|self $expression
     * @return self
     */
    public function hour($expression)
    {
        return $this->operator('$hour', $expression);
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
     * @param mixed|self $expression
     * @param mixed|self $replacementExpression
     * @return self
     */
    public function ifNull($expression, $replacementExpression)
    {
        return $this->operator('$ifNull', array($expression, $replacementExpression));
    }

    /**
     * Returns the value that results from applying an expression to the last
     * document in a group of documents that share the same group by a field.
     * Only meaningful when documents are in a defined order.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/last/
     * @param mixed|self $expression
     * @return self
     */
    public function last($expression)
    {
        return $this->operator('$last', $expression);
    }

    /**
     * Binds variables for use in the specified expression, and returns the
     * result of the expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/let/
     * @param mixed|self $vars Assignment block for the variables accessible in the in expression. To assign a variable, specify a string for the variable name and assign a valid expression for the value.
     * @param mixed|self $in   The expression to evaluate.
     * @return self
     */
    public function let($vars, $in)
    {
        return $this->operator('$let', array('vars' => $vars, 'in' => $in));
    }

    /**
     * Returns a value without parsing. Use for values that the aggregation
     * pipeline may interpret as an expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/literal/
     * @param mixed|self $value
     * @return self
     */
    public function literal($value)
    {
        return $this->operator('$literal', $value);
    }

    /**
     * Compares two values and returns:
     * true when the first value is less than the second value.
     * false when the first value is greater than or equivalent to the second
     * value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/lt/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
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
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function lte($expression1, $expression2)
    {
        return $this->operator('$lte', array($expression1, $expression2));
    }

    /**
     * Applies an expression to each item in an array and returns an array with
     * the applied results.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/map/
     * @param mixed|self $input An expression that resolves to an array.
     * @param string $as        The variable name for the items in the input array. The in expression accesses each item in the input array by this variable.
     * @param mixed|self $in    The expression to apply to each item in the input array. The expression accesses the item by its variable name.
     * @return self
     */
    public function map($input, $as, $in)
    {
        return $this->operator('$map', array('input' => $input, 'as' => $as, 'in' => $in));
    }

    /**
     * Returns the highest value that results from applying an expression to
     * each document in a group of documents that share the same group by key.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/max/
     * @param mixed|self $expression
     * @return self
     */
    public function max($expression)
    {
        return $this->operator('$max', $expression);
    }

    /**
     * Returns the metadata associated with a document in a pipeline operations.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/meta/
     * @param $metaDataKeyword
     * @return self
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
     * @param mixed|self $expression
     * @return self
     */
    public function millisecond($expression)
    {
        return $this->operator('$millisecond', $expression);
    }

    /**
     * Returns the lowest value that results from applying an expression to each
     * document in a group of documents that share the same group by key.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/min/
     * @param mixed|self $expression
     * @return self
     */
    public function min($expression)
    {
        return $this->operator('$min', $expression);
    }

    /**
     * Returns the minute portion of a date as a number between 0 and 59.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/minute/
     * @param mixed|self $expression
     * @return self
     */
    public function minute($expression)
    {
        return $this->operator('$minute', $expression);
    }

    /**
     * Divides one number by another and returns the remainder. The first
     * argument is divided by the second argument.
     *
     * The arguments can be any valid expression as long as they resolve to numbers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/mod/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
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
     * @param mixed|self $expression
     * @return self
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
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @param mixed|self $expression3,... Additional expressions
     * @return self
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
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function ne($expression1, $expression2)
    {
        return $this->operator('$ne', array($expression1, $expression2));
    }

    /**
     * Evaluates a boolean and returns the opposite boolean value.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/not/
     * @param mixed|self $expression
     * @return self
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
     * @param array|self[]|self $expression
     * @return self
     */
    private function operator($operator, $expression)
    {
        if ($this->currentField) {
            $this->expr[$this->currentField][$operator] = $this->ensureArray($expression);
        } else {
            $this->expr[$operator] = $this->ensureArray($expression);
        }

        return $this;
    }

    /**
     * Returns an array of all values that result from applying an expression to
     * each document in a group of documents that share the same group by key.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/push/
     * @param mixed|self $expression
     * @return self
     */
    public function push($expression)
    {
        return $this->operator('$push', $expression);
    }

    /**
     * Ensure that a current field has been set.
     *
     * @param string $method
     *
     * @throws LogicException if a current field has not been set
     */
    private function requiresCurrentField($method = null)
    {
        if ( ! $this->currentField) {
            throw new LogicException(($method ?: 'This method') . ' requires you set a current field using field().');
        }
    }

    /**
     * Returns the second portion of a date as a number between 0 and 59, but
     * can be 60 to account for leap seconds.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/second/
     * @param mixed|self $expression
     * @return self
     */
    public function second($expression)
    {
        return $this->operator('$second', $expression);
    }

    /**
     * Takes two sets and returns an array containing the elements that only
     * exist in the first set.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setDifference/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function setDifference($expression1, $expression2)
    {
        return $this->operator('$setDifference', array($expression1, $expression2));
    }

    /**
     * Compares two or more arrays and returns true if they have the same
     * distinct elements and false otherwise.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setEquals/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @param mixed|self $expression3,...   Additional sets
     * @return self
     */
    public function setEquals($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$setEquals', func_get_args());
    }

    /**
     * Takes two or more arrays and returns an array that contains the elements
     * that appear in every input array.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setIntersection/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @param mixed|self $expression3,...   Additional sets
     * @return self
     */
    public function setIntersection($expression1, $expression2 /* , $expression3, ... */)
    {
        return $this->operator('$setIntersection', func_get_args());
    }

    /**
     * Takes two arrays and returns true when the first array is a subset of the
     * second, including when the first array equals the second array, and false otherwise.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setIsSubset/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function setIsSubset($expression1, $expression2)
    {
        return $this->operator('$setIsSubset', array($expression1, $expression2));
    }

    /**
     * Takes two or more arrays and returns an array containing the elements
     * that appear in any input array.
     *
     * The arguments can be any valid expression as long as they each resolve to an array.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/setUnion/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @param mixed|self $expression3,...   Additional sets
     * @return self
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
     * @param mixed|self $expression
     * @return self
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
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function strcasecmp($expression1, $expression2)
    {
        return $this->operator('$strcasecmp', array($expression1, $expression2));
    }

    /**
     * Returns a substring of a string, starting at a specified index position
     * and including the specified number of characters. The index is zero-based.
     *
     * The arguments can be any valid expression as long as long as the first argument resolves to a string, and the second and third arguments resolve to integers.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/substr/
     * @param mixed|self $string
     * @param mixed|self $start
     * @param mixed|self $length
     * @return self
     */
    public function substr($string, $start, $length)
    {
        return $this->operator('$substr', array($string, $start, $length));
    }

    /**
     * Subtracts two numbers to return the difference. The second argument is
     * subtracted from the first argument.
     *
     * The arguments can be any valid expression as long as they resolve to numbers and/or dates.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/subtract/
     * @param mixed|self $expression1
     * @param mixed|self $expression2
     * @return self
     */
    public function subtract($expression1, $expression2)
    {
        return $this->operator('$subtract', array($expression1, $expression2));
    }

    /**
     * Calculates and returns the sum of all the numeric values that result from
     * applying a specified expression to each document in a group of documents
     * that share the same group by key. Ignores nun-numeric values.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/sum/
     * @param mixed|self $expression
     * @return self
     */
    public function sum($expression)
    {
        return $this->operator('$sum', $expression);
    }

    /**
     * Converts a string to lowercase, returning the result.
     *
     * The argument can be any expression as long as it resolves to a string.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/toLower/
     * @param mixed|self $expression
     * @return self
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
     * @param mixed|self $expression
     * @return self
     */
    public function toUpper($expression)
    {
        return $this->operator('$toUpper', $expression);
    }

    /**
     * Returns the week of the year for a date as a number between 0 and 53.
     *
     * The argument can be any expression as long as it resolves to a date.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/week/
     * @param mixed|self $expression
     * @return self
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
     * @param mixed|self $expression
     * @return self
     */
    public function year($expression)
    {
        return $this->operator('$year', $expression);
    }
}
