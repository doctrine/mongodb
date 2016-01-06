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
use Doctrine\MongoDB\Aggregation\Expr;

/**
 * Fluent interface for adding a $project stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.2
 */
class Project extends Operator
{
    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return array(
            '$project' => $this->expr->getExpression()
        );
    }

    /**
     * Returns the average value of the numeric values that result from applying
     * a specified expression to each document in a group of documents that
     * share the same group by key. Ignores nun-numeric values.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/avg/
     * @see Expr::avg
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2, ... Additional expressions
     * @return Operator
     *
     * @since 1.3
     */
    public function avg($expression1/* , $expression2, ... */)
    {
        $this->expr->avg(func_num_args() === 1 ? $expression1 : func_get_args());

        return $this;
    }

    /**
     * Shorthand method to exclude the _id field.
     * @param bool $exclude
     * @return self
     */
    public function excludeIdField($exclude = true)
    {
        return $this->field('_id')->expression( ! $exclude);
    }

    /**
     * Shorthand method to define which fields to be included.
     *
     * @param array $fields
     * @return self
     */
    public function includeFields(array $fields)
    {
        foreach ($fields as $fieldName) {
            $this->field($fieldName)->expression(true);
        }

        return $this;
    }

    /**
     * Returns the highest value that results from applying an expression to
     * each document in a group of documents that share the same group by key.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/max/
     * @see Expr::max
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2, ... Additional expressions
     * @return Operator
     *
     * @since 1.3
     */
    public function max($expression1/* , $expression2, ... */)
    {
        $this->expr->max(func_num_args() === 1 ? $expression1 : func_get_args());

        return $this;
    }

    /**
     * Returns the lowest value that results from applying an expression to each
     * document in a group of documents that share the same group by key.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/min/
     * @see Expr::min
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2, ... Additional expressions
     * @return Operator
     *
     * @since 1.3
     */
    public function min($expression1/* , $expression2, ... */)
    {
        $this->expr->min(func_num_args() === 1 ? $expression1 : func_get_args());

        return $this;
    }

    /**
     * Calculates the population standard deviation of the input values.
     *
     * The argument can be any expression as long as it resolves to an array.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/stdDevPop/
     * @see Expr::stdDevPop
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2, ... Additional expressions
     * @return self
     *
     * @since 1.3
     */
    public function stdDevPop($expression1/* , $expression2, ... */)
    {
        $this->expr->stdDevPop(func_num_args() === 1 ? $expression1 : func_get_args());

        return $this;
    }

    /**
     * Calculates the sample standard deviation of the input values.
     *
     * The argument can be any expression as long as it resolves to an array.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/stdDevSamp/
     * @see Expr::stdDevSamp
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2, ... Additional expressions
     * @return self
     *
     * @since 1.3
     */
    public function stdDevSamp($expression1/* , $expression2, ... */)
    {
        $this->expr->stdDevSamp(func_num_args() === 1 ? $expression1 : func_get_args());

        return $this;
    }

    /**
     * Calculates and returns the sum of all the numeric values that result from
     * applying a specified expression to each document in a group of documents
     * that share the same group by key. Ignores nun-numeric values.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/sum/
     * @see Expr::sum
     * @param mixed|Expr $expression1
     * @param mixed|Expr $expression2, ... Additional expressions
     * @return Operator
     *
     * @since 1.3
     */
    public function sum($expression1/* , $expression2, ... */)
    {
        $this->expr->sum(func_num_args() === 1 ? $expression1 : func_get_args());

        return $this;
    }
}
