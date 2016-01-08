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
     * @param string $from
     */
    public function __construct(Builder $builder, $from)
    {
        parent::__construct($builder);

        $this->from($from);
    }

    /**
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
