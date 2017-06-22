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
use Doctrine\MongoDB\Aggregation\Stage;

/**
 * Fluent interface for adding a $lookup stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.3
 */
class Lookup extends Stage
{
    /**
     * @var string
     */
    private $from;

    /**
     * @var string
     */
    private $localField;

    /**
     * @var string
     */
    private $foreignField;

    /**
     * @var string
     */
    private $as;

    /**
     * Lookup constructor.
     *
     * @param Builder $builder
     * @param string $from Specifies the collection in the same database to
     * perform the join with.
     */
    public function __construct(Builder $builder, $from)
    {
        parent::__construct($builder);

        $this->from($from);
    }

    /**
     * Specifies the collection in the same database to perform the join with.
     *
     * The from collection cannot be sharded.
     *
     * @param string $from
     *
     * @return $this
     */
    public function from($from)
    {
        $this->from = $from;

        return $this;
    }

    /**
     * Specifies the field from the documents input to the $lookup stage.
     *
     * $lookup performs an equality match on the localField to the foreignField
     * from the documents of the from collection. If an input document does not
     * contain the localField, the $lookup treats the field as having a value of
     * null for matching purposes.
     *
     * @param string $localField
     *
     * @return $this
     */
    public function localField($localField)
    {
        $this->localField = $localField;

        return $this;
    }

    /**
     * Specifies the field from the documents in the from collection.
     *
     * $lookup performs an equality match on the foreignField to the localField
     * from the input documents. If a document in the from collection does not
     * contain the foreignField, the $lookup treats the value as null for
     * matching purposes.
     *
     * @param string $foreignField
     *
     * @return $this
     */
    public function foreignField($foreignField)
    {
        $this->foreignField = $foreignField;

        return $this;
    }

    /**
     * Specifies the name of the new array field to add to the input documents.
     *
     * The new array field contains the matching documents from the from
     * collection. If the specified name already exists in the input document,
     * the existing field is overwritten.
     *
     * @param string $alias
     *
     * @return $this
     */
    public function alias($alias)
    {
        $this->as = $alias;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return [
            '$lookup' => [
                'from' => $this->from,
                'localField' => $this->localField,
                'foreignField' => $this->foreignField,
                'as' => $this->as,
            ]
        ];
    }
}
