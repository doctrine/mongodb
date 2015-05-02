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

/**
 * Fluent interface for adding a $group stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 */
class Group extends Operator
{
    /**
     * Returns an array of all unique values that results from applying an expression to each document in a group of documents that share the same group by key. Order of the elements in the output array is unspecified.
     *
     * AddToSet is an accumulator operation only available in the group stage.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/addToSet/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function addToSet($expression)
    {
        return $this->operator('$addToSet', $expression);
    }

    /**
     * Returns the average value of the numeric values that result from applying a specified expression to each document in a group of documents that share the same group by key. Ignores nun-numeric values.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/avg/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function avg($expression)
    {
        return $this->operator('$avg', $expression);
    }

    /**
     * Returns the value that results from applying an expression to the first document in a group of documents that share the same group by key. Only meaningful when documents are in a defined order.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/first/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function first($expression)
    {
        return $this->operator('$first', $expression);
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return array(
            '$group' => $this->expr
        );
    }

    /**
     * Returns the value that results from applying an expression to the last document in a group of documents that share the same group by a field. Only meaningful when documents are in a defined order.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/last/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function last($expression)
    {
        return $this->operator('$last', $expression);
    }

    /**
     * Returns the highest value that results from applying an expression to each document in a group of documents that share the same group by key.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/max/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function max($expression)
    {
        return $this->operator('$max', $expression);
    }

    /**
     * Returns the lowest value that results from applying an expression to each document in a group of documents that share the same group by key.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/min/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function min($expression)
    {
        return $this->operator('$min', $expression);
    }

    /**
     * Returns an array of all values that result from applying an expression to each document in a group of documents that share the same group by key.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/push/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function push($expression)
    {
        return $this->operator('$push', $expression);
    }

    /**
     * Calculates and returns the sum of all the numeric values that result from applying a specified expression to each document in a group of documents that share the same group by key. Ignores nun-numeric values.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/sum/
     * @param mixed|Operator $expression
     * @return $this
     */
    public function sum($expression)
    {
        return $this->operator('$sum', $expression);
    }
}
