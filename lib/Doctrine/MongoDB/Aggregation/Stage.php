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

use Doctrine\MongoDB\Iterator;
use GeoJson\Geometry\Point;

/**
 * Fluent interface for building aggregation pipelines.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.2
 */
abstract class Stage
{
    /**
     * @var Builder
     */
    protected $builder;

    /**
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        $this->builder = $builder;
    }

    /**
     * Assembles the aggregation stage
     *
     * @return array
     */
    abstract public function getExpression();

    /**
     * Executes the aggregation pipeline
     *
     * @param array $options
     * @return Iterator
     */
    public function execute($options = [])
    {
        return $this->builder->execute($options);
    }

    /**
     * Outputs documents in order of nearest to farthest from a specified point.
     * You can only use this as the first stage of a pipeline.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/geoNear/
     *
     * @param float|array|Point $x
     * @param float $y
     * @return Stage\GeoNear
     */
    public function geoNear($x, $y = null)
    {
        return $this->builder->geoNear($x, $y);
    }

    /**
     * Returns the assembled aggregation pipeline
     *
     * @return array
     */
    public function getPipeline()
    {
        return $this->builder->getPipeline();
    }

    /**
     * Groups documents by some specified expression and outputs to the next
     * stage a document for each distinct grouping.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/group/
     *
     * @return Stage\Group
     */
    public function group()
    {
        return $this->builder->group();
    }

    /**
     * Returns statistics regarding the use of each index for the collection.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/indexStats/
     *
     * @return Stage\IndexStats
     */
    public function indexStats()
    {
        return $this->builder->indexStats();
    }

    /**
     * Limits the number of documents passed to the next stage in the pipeline.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/limit/
     *
     * @param integer $limit
     * @return Stage\Limit
     */
    public function limit($limit)
    {
        return $this->builder->limit($limit);
    }

    /**
     * Performs a left outer join to an unsharded collection in the same
     * database to filter in documents from the “joined” collection for
     * processing.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/lookup/
     *
     * @param string $from
     * @return Stage\Lookup
     */
    public function lookup($from)
    {
        return $this->builder->lookup($from);
    }

    /**
     * Filters the documents to pass only the documents that match the specified
     * condition(s) to the next pipeline stage.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/match/
     *
     * @return Stage\Match
     */
    public function match()
    {
        return $this->builder->match();
    }

    /**
     * Takes the documents returned by the aggregation pipeline and writes them
     * to a specified collection. This must be the last stage in the pipeline.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/out/
     *
     * @param string $collection
     * @return Stage\Out
     */
    public function out($collection)
    {
        return $this->builder->out($collection);
    }

    /**
     * Passes along the documents with only the specified fields to the next
     * stage in the pipeline. The specified fields can be existing fields from
     * the input documents or newly computed fields.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/project/
     *
     * @return Stage\Project
     */
    public function project()
    {
        return $this->builder->project();
    }

    /**
     * Restricts the contents of the documents based on information stored in
     * the documents themselves.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/redact/
     *
     * @return Stage\Redact
     */
    public function redact()
    {
        return $this->builder->redact();
    }

    /**
     * Randomly selects the specified number of documents from its input.
     *
     * @see https://docs.mongodb.org/manual/reference/operator/aggregation/sample/
     *
     * @param integer $size
     * @return Stage\Sample
     */
    public function sample($size)
    {
        return $this->builder->sample($size);
    }

    /**
     * Skips over the specified number of documents that pass into the stage and
     * passes the remaining documents to the next stage in the pipeline.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/skip/
     *
     * @param integer $skip
     * @return Stage\Skip
     */
    public function skip($skip)
    {
        return $this->builder->skip($skip);
    }

    /**
     * Sorts all input documents and returns them to the pipeline in sorted order.
     *
     * If sorting by multiple fields, the first argument should be an array of
     * field name (key) and order (value) pairs.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/sort/
     *
     * @param array|string $fieldName Field name or array of field/order pairs
     * @param integer|string $order   Field order (if one field is specified)
     * @return Stage\Sort
     */
    public function sort($fieldName, $order = null)
    {
        return $this->builder->sort($fieldName, $order);
    }

    /**
     * Deconstructs an array field from the input documents to output a document
     * for each element. Each output document is the input document with the
     * value of the array field replaced by the element.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/aggregation/unwind/
     *
     * @param string $fieldName The field to unwind. It is automatically prefixed with the $ sign
     * @return Stage\Unwind
     */
    public function unwind($fieldName)
    {
        return $this->builder->unwind($fieldName);
    }
}
