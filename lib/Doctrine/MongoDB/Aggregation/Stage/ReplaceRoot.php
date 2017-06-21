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

/**
 * Fluent interface for adding a $replaceRoot stage to an aggregation pipeline.
 *
 * @author alcaeus <alcaeus@alcaeus.org>
 * @since 1.5
 */
class ReplaceRoot extends Operator
{
    /**
     * @var string|null
     */
    private $expression;

    /**
     * @param Builder $builder
     * @param string|null $expression Optional. A replacement expression that
     * resolves to a document.
     */
    public function __construct(Builder $builder, $expression = null)
    {
        parent::__construct($builder);

        $this->expression = $expression;
    }

    /**
     * {@inheritdoc}
     */
    public function getExpression()
    {
        return [
            '$replaceRoot' => $this->expression !== null ? Expr::convertExpression($this->expression) : $this->expr->getExpression()
        ];
    }
}
