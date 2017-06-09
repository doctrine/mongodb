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
 * Fluent interface for adding a $bucket stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.5
 */
class Bucket extends Stage
{
    /**
     * @var Expr
     */
    private $groupBy;

    /**
     * @var array
     */
    private $boundaries;

    /**
     * @var mixed
     */
    private $default;

    /**
     * @var Bucket\Output|null
     */
    private $output;

    /**
     * @param Builder $builder
     */
    public function __construct(Builder $builder)
    {
        parent::__construct($builder);
    }

    /**
     * @param array|Expr $expression
     * @return $this
     */
    public function groupBy($expression)
    {
        if (is_string($expression)) {
            $this->groupBy = $expression;
        } elseif (is_array($expression)) {
            $this->groupBy = $this->ensureArrayExpression($expression);
        } elseif ($expression instanceof Expr) {
            $this->groupBy = $expression->getExpression();
        } else {
            throw new \InvalidArgumentException('Invalid expression given - must be a string, array or expression object');
        }

        return $this;
    }

    /**
     * @param array ...$boundaries
     *
     * @return $this
     */
    public function boundaries(...$boundaries)
    {
        $this->boundaries = $boundaries;
        return $this;
    }

    /**
     * @param mixed $default
     *
     * @return $this
     */
    public function defaultBucket($default)
    {
        $this->default = $default;
        return $this;
    }

    /**
     * @return Bucket\Output
     */
    public function output()
    {
        if (!$this->output) {
            $this->output = new Stage\Bucket\Output($this->builder, $this);
        }

        return $this->output;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        $stage = [
            '$bucket' => [
                'groupBy' => $this->groupBy instanceof Expr ? $this->groupBy->getExpression() : (string) $this->groupBy,
                'boundaries' => $this->boundaries,
            ],
        ];

        if ($this->default !== null) {
            $stage['$bucket']['default'] = $this->default;
        }

        if ($this->output !== null) {
            $stage['$bucket']['output'] = $this->output->getExpression();
        }

        return $stage;
    }

    private function ensureArrayExpression($expression)
    {
        if (is_array($expression)) {
            $array = [];
            foreach ($expression as $index => $value) {
                $array[$index] = $this->ensureArrayExpression($value);
            }

            return $array;
        } elseif ($expression instanceof self) {
            return $expression->getExpression();
        }

        return $expression;
    }
}
