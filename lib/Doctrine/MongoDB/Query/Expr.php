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

namespace Doctrine\MongoDB\Query;

use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\Point;
use BadMethodCallException;
use InvalidArgumentException;
use LogicException;

/**
 * Fluent interface for building query and update expressions.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Expr
{
    /**
     * The query criteria array.
     *
     * @var string
     */
    protected $query = array();

    /**
     * The "new object" array containing either a full document or a number of
     * atomic update operators.
     *
     * @see docs.mongodb.org/manual/reference/method/db.collection.update/#update-parameter
     * @var array
     */
    protected $newObj = array();

    /**
     * The current field we are operating on.
     *
     * @var string
     */
    protected $currentField;

    /**
     * Add an $and clause to the current query.
     *
     * @see Builder::addAnd()
     * @see http://docs.mongodb.org/manual/reference/operator/and/
     * @param array|Expr $expression
     * @return self
     */
    public function addAnd($expression)
    {
        $this->query['$and'][] = $expression instanceof Expr ? $expression->getQuery() : $expression;
        return $this;
    }

    /**
     * Append multiple values to the current array field only if they do not
     * already exist in the array.
     *
     * If the field does not exist, it will be set to an array containing the
     * unique values in the argument. If the field is not an array, the query
     * will yield an error.
     *
     * @deprecated 1.1 Use {@link Expr::addToSet()} with {@link Expr::each()}; Will be removed in 2.0
     * @see Builder::addManyToSet()
     * @see http://docs.mongodb.org/manual/reference/operator/addToSet/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @param array $values
     * @return self
     */
    public function addManyToSet(array $values)
    {
        $this->requiresCurrentField();
        $this->newObj['$addToSet'][$this->currentField] = array('$each' => $values);
        return $this;
    }

    /**
     * Add a $nor clause to the current query.
     *
     * @see Builder::addNor()
     * @see http://docs.mongodb.org/manual/reference/operator/nor/
     * @param array|Expr $expression
     * @return self
     */
    public function addNor($expression)
    {
        $this->query['$nor'][] = $expression instanceof Expr ? $expression->getQuery() : $expression;
        return $this;
    }

    /**
     * Add an $or clause to the current query.
     *
     * @see Builder::addOr()
     * @see http://docs.mongodb.org/manual/reference/operator/or/
     * @param array|Expr $expression
     * @return self
     */
    public function addOr($expression)
    {
        $this->query['$or'][] = $expression instanceof Expr ? $expression->getQuery() : $expression;
        return $this;
    }

    /**
     * Append one or more values to the current array field only if they do not
     * already exist in the array.
     *
     * If the field does not exist, it will be set to an array containing the
     * unique value(s) in the argument. If the field is not an array, the query
     * will yield an error.
     *
     * Multiple values may be specified by provided an Expr object and using
     * {@link Expr::each()}.
     *
     * @see Builder::addToSet()
     * @see http://docs.mongodb.org/manual/reference/operator/addToSet/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @param mixed|Expr $valueOrExpression
     * @return self
     */
    public function addToSet($valueOrExpression)
    {
        if ($valueOrExpression instanceof Expr) {
            $valueOrExpression = $valueOrExpression->getQuery();
        }

        $this->requiresCurrentField();
        $this->newObj['$addToSet'][$this->currentField] = $valueOrExpression;
        return $this;
    }

    /**
     * Specify $all criteria for the current field.
     *
     * @see Builder::all()
     * @see http://docs.mongodb.org/manual/reference/operator/all/
     * @param array $values
     * @return self
     */
    public function all(array $values)
    {
        return $this->operator('$all', (array) $values);
    }

    /**
     * Apply a bitwise operation on the current field
     *
     * @see http://docs.mongodb.org/manual/reference/operator/update/bit/
     * @param string $operator
     * @param int $value
     * @return self
     */
    protected function bit($operator, $value)
    {
        $this->requiresCurrentField();
        $this->newObj['$bit'][$this->currentField][$operator] = $value;
        return $this;
    }

    /**
     * Apply a bitwise and operation on the current field.
     *
     * @see Builder::bitAnd()
     * @see http://docs.mongodb.org/manual/reference/operator/update/bit/
     * @param int $value
     * @return self
     */
    public function bitAnd($value)
    {
        return $this->bit('and', $value);
    }

    /**
     * Apply a bitwise or operation on the current field.
     *
     * @see Builder::bitOr()
     * @see http://docs.mongodb.org/manual/reference/operator/update/bit/
     * @param int $value
     * @return self
     */
    public function bitOr($value)
    {
        return $this->bit('or', $value);
    }

    /**
     * Apply a bitwise xor operation on the current field.
     *
     * @see Builder::bitXor()
     * @see http://docs.mongodb.org/manual/reference/operator/update/bit/
     * @param int $value
     * @return self
     */
    public function bitXor($value)
    {
        return $this->bit('xor', $value);
    }

    /**
     * Sets the value of the current field to the current date, either as a date or a timestamp.
     *
     * @see Builder::currentDate()
     * @see http://docs.mongodb.org/manual/reference/operator/update/currentDate/
     * @param string $type
     * @return self
     * @throws InvalidArgumentException if an invalid type is given
     */
    public function currentDate($type = 'date')
    {
        if (!in_array($type, array('date', 'timestamp'))) {
            throw new InvalidArgumentException('Type for currentDate operator must be date or timestamp.');
        }

        $this->requiresCurrentField();
        $this->newObj['$currentDate'][$this->currentField]['$type'] = $type;
        return $this;
    }

    /**
     * Add $each criteria to the expression for a $push operation.
     *
     * @see Expr::push()
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @param array $values
     * @return self
     */
    public function each(array $values)
    {
        return $this->operator('$each', $values);
    }

    /**
     * Specify $elemMatch criteria for the current field.
     *
     * @see Builder::elemMatch()
     * @see http://docs.mongodb.org/manual/reference/operator/elemMatch/
     * @param array|Expr $expression
     * @return self
     */
    public function elemMatch($expression)
    {
        return $this->operator('$elemMatch', $expression instanceof Expr ? $expression->getQuery() : $expression);
    }

    /**
     * Specify an equality match for the current field.
     *
     * @see Builder::equals()
     * @param mixed $value
     * @return self
     */
    public function equals($value)
    {
        if ($this->currentField) {
            $this->query[$this->currentField] = $value;
        } else {
            $this->query = $value;
        }
        return $this;
    }

    /**
     * Specify $exists criteria for the current field.
     *
     * @see Builder::exists()
     * @see http://docs.mongodb.org/manual/reference/operator/exists/
     * @param boolean $bool
     * @return self
     */
    public function exists($bool)
    {
        return $this->operator('$exists', (boolean) $bool);
    }

    /**
     * Set the current field for building the expression.
     *
     * @see Builder::field()
     * @param string $field
     * @return self
     */
    public function field($field)
    {
        $this->currentField = (string) $field;
        return $this;
    }

    /**
     * Add $geoIntersects criteria with a GeoJSON geometry to the expression.
     *
     * The geometry parameter GeoJSON object or an array corresponding to the
     * geometry's JSON representation.
     *
     * @see Builder::geoIntersects()
     * @see http://docs.mongodb.org/manual/reference/operator/geoIntersects/
     * @param array|Geometry $geometry
     * @return self
     */
    public function geoIntersects($geometry)
    {
        if ($geometry instanceof Geometry) {
            $geometry = $geometry->jsonSerialize();
        }

        return $this->operator('$geoIntersects', array('$geometry' => $geometry));
    }

    /**
     * Add $geoWithin criteria with a GeoJSON geometry to the expression.
     *
     * The geometry parameter GeoJSON object or an array corresponding to the
     * geometry's JSON representation.
     *
     * @see Builder::geoWithin()
     * @see http://docs.mongodb.org/manual/reference/operator/geoIntersects/
     * @param array|Geometry $geometry
     * @return self
     */
    public function geoWithin($geometry)
    {
        if ($geometry instanceof Geometry) {
            $geometry = $geometry->jsonSerialize();
        }

        return $this->operator('$geoWithin', array('$geometry' => $geometry));
    }

    /**
     * Add $geoWithin criteria with a $box shape to the expression.
     *
     * A rectangular polygon will be constructed from a pair of coordinates
     * corresponding to the bottom left and top right corners.
     *
     * Note: the $box operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see Builder::geoWithinBox()
     * @see http://docs.mongodb.org/manual/reference/operator/box/
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return self
     */
    public function geoWithinBox($x1, $y1, $x2, $y2)
    {
        $shape = array('$box' => array(array($x1, $y1), array($x2, $y2)));

        return $this->operator('$geoWithin', $shape);
    }

    /**
     * Add $geoWithin criteria with a $center shape to the expression.
     *
     * Note: the $center operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see Builider::geoWithinCenter()
     * @see http://docs.mongodb.org/manual/reference/operator/center/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function geoWithinCenter($x, $y, $radius)
    {
        $shape = array('$center' => array(array($x, $y), $radius));

        return $this->operator('$geoWithin', $shape);
    }

    /**
     * Add $geoWithin criteria with a $centerSphere shape to the expression.
     *
     * Note: the $centerSphere operator supports both 2d and 2dsphere indexes.
     *
     * @see Builder::geoWithinCenterSphere()
     * @see http://docs.mongodb.org/manual/reference/operator/centerSphere/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function geoWithinCenterSphere($x, $y, $radius)
    {
        $shape = array('$centerSphere' => array(array($x, $y), $radius));

        return $this->operator('$geoWithin', $shape);
    }

    /**
     * Add $geoWithin criteria with a $polygon shape to the expression.
     *
     * Point coordinates are in x, y order (easting, northing for projected
     * coordinates, longitude, latitude for geographic coordinates).
     *
     * The last point coordinate is implicitly connected with the first.
     *
     * Note: the $polygon operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see Builder::geoWithinPolygon()
     * @see http://docs.mongodb.org/manual/reference/operator/polygon/
     * @param array $point,... Three or more point coordinate tuples
     * @return self
     * @throws InvalidArgumentException if less than three points are given
     */
    public function geoWithinPolygon(/* array($x1, $y1), ... */)
    {
        if (func_num_args() < 3) {
            throw new InvalidArgumentException('Polygon must be defined by three or more points.');
        }

        $shape = array('$polygon' => func_get_args());

        return $this->operator('$geoWithin', $shape);
    }

    /**
     * Return the current field.
     *
     * @return string
     */
    public function getCurrentField()
    {
        return $this->currentField;
    }

    /**
     * Return the "new object".
     *
     * @see Builder::getNewObj()
     * @return array
     */
    public function getNewObj()
    {
        return $this->newObj;
    }

    /**
     * Set the "new object".
     *
     * @see Builder::setNewObj()
     * @param array $newObj
     * @return self
     */
    public function setNewObj(array $newObj)
    {
        $this->newObj = $newObj;
    }

    /**
     * Return the query criteria.
     *
     * @see Builder::getQueryArray()
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Set the query criteria.
     *
     * @see Builder::setQueryArray()
     * @param array $query
     * @return self
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    /**
     * Specify $gt criteria for the current field.
     *
     * @see Builder::gt()
     * @see http://docs.mongodb.org/manual/reference/operator/gt/
     * @param mixed $value
     * @return self
     */
    public function gt($value)
    {
        return $this->operator('$gt', $value);
    }

    /**
     * Specify $gte criteria for the current field.
     *
     * @see Builder::gte()
     * @see http://docs.mongodb.org/manual/reference/operator/gte/
     * @param mixed $value
     * @return self
     */
    public function gte($value)
    {
        return $this->operator('$gte', $value);
    }

    /**
     * Specify $in criteria for the current field.
     *
     * @see Builder::in()
     * @see http://docs.mongodb.org/manual/reference/operator/in/
     * @param array $values
     * @return self
     */
    public function in(array $values)
    {
        return $this->operator('$in', array_values($values));
    }

    /**
     * Increment the current field.
     *
     * If the field does not exist, it will be set to this value.
     *
     * @see Builder::inc()
     * @see http://docs.mongodb.org/manual/reference/operator/inc/
     * @param float|integer $value
     * @return self
     */
    public function inc($value)
    {
        $this->requiresCurrentField();
        $this->newObj['$inc'][$this->currentField] = $value;
        return $this;
    }

    /**
     * Set the $language option for $text criteria.
     *
     * This method must be called after text().
     *
     * @see Builder::language()
     * @see http://docs.mongodb.org/manual/reference/operator/text/
     * @param string $language
     * @return self
     * @throws BadMethodCallException if the query does not already have $text criteria
     */
    public function language($language)
    {
        if ( ! isset($this->query['$text'])) {
            throw new BadMethodCallException('This method requires a $text operator (call text() first)');
        }

        $this->query['$text']['$language'] = (string) $language;

        return $this;
    }

    /**
     * Specify $lt criteria for the current field.
     *
     * @see Builder::lte()
     * @see http://docs.mongodb.org/manual/reference/operator/lte/
     * @param mixed $value
     * @return self
     */
    public function lt($value)
    {
        return $this->operator('$lt', $value);
    }

    /**
     * Specify $lte criteria for the current field.
     *
     * @see Builder::lte()
     * @see http://docs.mongodb.org/manual/reference/operator/lte/
     * @param mixed $value
     * @return self
     */
    public function lte($value)
    {
        return $this->operator('$lte', $value);
    }

    /**
     * Updates the value of the field to a specified value if the specified value is greater than the current value of the field.
     *
     * @see Builder::max()
     * @see http://docs.mongodb.org/manual/reference/operator/update/max/
     * @param mixed $value
     * @return self
     */
    public function max($value)
    {
        $this->requiresCurrentField();
        $this->newObj['$max'][$this->currentField] = $value;
        return $this;
    }

    /**
     * Set the $maxDistance option for $near or $nearSphere criteria.
     *
     * This method must be called after near() or nearSphere(), since placement
     * of the $maxDistance option depends on whether a GeoJSON point or legacy
     * coordinates were provided for $near/$nearSphere.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/maxDistance/
     * @param float $maxDistance
     * @return self
     * @throws BadMethodCallException if the query does not already have $near or $nearSphere criteria
     */
    public function maxDistance($maxDistance)
    {
        if ($this->currentField) {
            $query = &$this->query[$this->currentField];
        } else {
            $query = &$this->query;
        }

        if ( ! isset($query['$near']) && ! isset($query['$nearSphere'])) {
            throw new BadMethodCallException(
                'This method requires a $near or $nearSphere operator (call near() or nearSphere() first)'
            );
        }

        if (isset($query['$near']['$geometry'])) {
            $query['$near']['$maxDistance'] = $maxDistance;
        } elseif (isset($query['$nearSphere']['$geometry'])) {
            $query['$nearSphere']['$maxDistance'] = $maxDistance;
        } else {
            $query['$maxDistance'] = $maxDistance;
        }

        return $this;
    }

    /**
     * Updates the value of the field to a specified value if the specified value is less than the current value of the field.
     *
     * @see Builder::min()
     * @see http://docs.mongodb.org/manual/reference/operator/update/min/
     * @param mixed $value
     * @return self
     */
    public function min($value)
    {
        $this->requiresCurrentField();
        $this->newObj['$min'][$this->currentField] = $value;
        return $this;
    }

    /**
     * Set the $minDistance option for $near or $nearSphere criteria.
     *
     * This method must be called after near() or nearSphere(), since placement
     * of the $minDistance option depends on whether a GeoJSON point or legacy
     * coordinates were provided for $near/$nearSphere.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/minDistance/
     * @param float $minDistance
     * @return self
     * @throws BadMethodCallException if the query does not already have $near or $nearSphere criteria
     */
    public function minDistance($minDistance)
    {
        if ($this->currentField) {
            $query = &$this->query[$this->currentField];
        } else {
            $query = &$this->query;
        }

        if ( ! isset($query['$near']) && ! isset($query['$nearSphere'])) {
            throw new BadMethodCallException(
                'This method requires a $near or $nearSphere operator (call near() or nearSphere() first)'
            );
        }

        if (isset($query['$near']['$geometry'])) {
            $query['$near']['$minDistance'] = $minDistance;
        } elseif (isset($query['$nearSphere']['$geometry'])) {
            $query['$nearSphere']['$minDistance'] = $minDistance;
        } else {
            $query['$minDistance'] = $minDistance;
        }

        return $this;
    }

    /**
     * Specify $mod criteria for the current field.
     *
     * @see Builder::mod()
     * @see http://docs.mongodb.org/manual/reference/operator/mod/
     * @param float|integer $divisor
     * @param float|integer $remainder
     * @return self
     */
    public function mod($divisor, $remainder = 0)
    {
        return $this->operator('$mod', array($divisor, $remainder));
    }

    /**
     * Multiply the current field.
     *
     * If the field does not exist, it will be set to 0.
     *
     * @see Builder::mul()
     * @see http://docs.mongodb.org/manual/reference/operator/mul/
     * @param float|integer $value
     * @return self
     */
    public function mul($value)
    {
        $this->requiresCurrentField();
        $this->newObj['$mul'][$this->currentField] = $value;
        return $this;
    }

    /**
     * Add $near criteria to the expression.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation.
     *
     * @see Builder::near()
     * @see http://docs.mongodb.org/manual/reference/operator/near/
     * @param float|array|Point $x
     * @param float $y
     * @return self
     */
    public function near($x, $y = null)
    {
        if ($x instanceof Point) {
            $x = $x->jsonSerialize();
        }

        if (is_array($x)) {
            return $this->operator('$near', array('$geometry' => $x));
        }

        return $this->operator('$near', array($x, $y));
    }

    /**
     * Add $nearSphere criteria to the expression.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation.
     *
     * @see Builder::nearSphere()
     * @see http://docs.mongodb.org/manual/reference/operator/nearSphere/
     * @param float|array|Point $x
     * @param float $y
     * @return self
     */
    public function nearSphere($x, $y = null)
    {
        if ($x instanceof Point) {
            $x = $x->jsonSerialize();
        }

        if (is_array($x)) {
            return $this->operator('$nearSphere', array('$geometry' => $x));
        }

        return $this->operator('$nearSphere', array($x, $y));
    }

    /**
     * Negates an expression for the current field.
     *
     * @see Builder::not()
     * @see http://docs.mongodb.org/manual/reference/operator/not/
     * @param array|Expr $expression
     * @return self
     */
    public function not($expression)
    {
        return $this->operator('$not', $expression instanceof Expr ? $expression->getQuery() : $expression);
    }

    /**
     * Specify $ne criteria for the current field.
     *
     * @see Builder::notEqual()
     * @see http://docs.mongodb.org/manual/reference/operator/ne/
     * @param mixed $value
     * @return self
     */
    public function notEqual($value)
    {
        return $this->operator('$ne', $value);
    }

    /**
     * Specify $nin criteria for the current field.
     *
     * @see Builder::notIn()
     * @see http://docs.mongodb.org/manual/reference/operator/nin/
     * @param array $values
     * @return self
     */
    public function notIn(array $values)
    {
        return $this->operator('$nin', array_values($values));
    }

    /**
     * Defines an operator and value on the expression.
     *
     * If there is a current field, the operator will be set on it; otherwise,
     * the operator is set at the top level of the query.
     *
     * @param string $operator
     * @param mixed $value
     * @return self
     */
    public function operator($operator, $value)
    {
        $this->wrapEqualityCriteria();

        if ($this->currentField) {
            $this->query[$this->currentField][$operator] = $value;
        } else {
            $this->query[$operator] = $value;
        }
        return $this;
    }

    /**
     * Remove the first element from the current array field.
     *
     * @see Builder::popFirst()
     * @see http://docs.mongodb.org/manual/reference/operator/pop/
     * @return self
     */
    public function popFirst()
    {
        $this->requiresCurrentField();
        $this->newObj['$pop'][$this->currentField] = 1;
        return $this;
    }

    /**
     * Remove the last element from the current array field.
     *
     * @see Builder::popLast()
     * @see http://docs.mongodb.org/manual/reference/operator/pop/
     * @return self
     */
    public function popLast()
    {
        $this->requiresCurrentField();
        $this->newObj['$pop'][$this->currentField] = -1;
        return $this;
    }

    /**
     * Add $position criteria to the expression for a $push operation.
     *
     * This is useful in conjunction with {@link Expr::each()} for a
     * {@link Expr::push()} operation.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/update/position/
     * @param integer $position
     * @return self
     */
    public function position($position)
    {
        return $this->operator('$position', $position);
    }

    /**
     * Remove all elements matching the given value or expression from the
     * current array field.
     *
     * @see Builder::pull()
     * @see http://docs.mongodb.org/manual/reference/operator/pull/
     * @param mixed|Expr $valueOrExpression
     * @return self
     */
    public function pull($valueOrExpression)
    {
        if ($valueOrExpression instanceof Expr) {
            $valueOrExpression = $valueOrExpression->getQuery();
        }

        $this->requiresCurrentField();
        $this->newObj['$pull'][$this->currentField] = $valueOrExpression;
        return $this;
    }

    /**
     * Remove all elements matching any of the given values from the current
     * array field.
     *
     * @see Builder::pullAll()
     * @see http://docs.mongodb.org/manual/reference/operator/pullAll/
     * @param array $values
     * @return self
     */
    public function pullAll(array $values)
    {
        $this->requiresCurrentField();
        $this->newObj['$pullAll'][$this->currentField] = $values;
        return $this;
    }

    /**
     * Append one or more values to the current array field.
     *
     * If the field does not exist, it will be set to an array containing the
     * value(s) in the argument. If the field is not an array, the query
     * will yield an error.
     *
     * Multiple values may be specified by providing an Expr object and using
     * {@link Expr::each()}. {@link Expr::slice()} and {@link Expr::sort()} may
     * also be used to limit and order array elements, respectively.
     *
     * @see Builder::push()
     * @see http://docs.mongodb.org/manual/reference/operator/push/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @see http://docs.mongodb.org/manual/reference/operator/slice/
     * @see http://docs.mongodb.org/manual/reference/operator/sort/
     * @param mixed|Expr $valueOrExpression
     * @return self
     */
    public function push($valueOrExpression)
    {
        if ($valueOrExpression instanceof Expr) {
            $valueOrExpression = array_merge(
                array('$each' => array()),
                $valueOrExpression->getQuery()
            );
        }

        $this->requiresCurrentField();
        $this->newObj['$push'][$this->currentField] = $valueOrExpression;
        return $this;
    }

    /**
     * Append multiple values to the current array field.
     *
     * If the field does not exist, it will be set to an array containing the
     * values in the argument. If the field is not an array, the query will
     * yield an error.
     *
     * This operator is deprecated in MongoDB 2.4. {@link Expr::push()} and
     * {@link Expr::each()} should be used in its place.
     *
     * @see Builder::pushAll()
     * @see http://docs.mongodb.org/manual/reference/operator/pushAll/
     * @param array $values
     * @return self
     */
    public function pushAll(array $values)
    {
        $this->requiresCurrentField();
        $this->newObj['$pushAll'][$this->currentField] = $values;
        return $this;
    }

    /**
     * Specify $gte and $lt criteria for the current field.
     *
     * This method is shorthand for specifying $gte criteria on the lower bound
     * and $lt criteria on the upper bound. The upper bound is not inclusive.
     *
     * @see Builder::range()
     * @param mixed $start
     * @param mixed $end
     * @return self
     */
    public function range($start, $end)
    {
        return $this->operator('$gte', $start)->operator('$lt', $end);
    }

    /**
     * Rename the current field.
     *
     * @see Builder::rename()
     * @see http://docs.mongodb.org/manual/reference/operator/rename/
     * @param string $name
     * @return self
     */
    public function rename($name)
    {
        $this->requiresCurrentField();
        $this->newObj['$rename'][$this->currentField] = $name;
        return $this;
    }

    /**
     * Set the current field to a value.
     *
     * This is only relevant for insert, update, or findAndUpdate queries. For
     * update and findAndUpdate queries, the $atomic parameter will determine
     * whether or not a $set operator is used.
     *
     * @see Builder::set()
     * @see http://docs.mongodb.org/manual/reference/operator/set/
     * @param mixed $value
     * @param boolean $atomic
     * @return self
     */
    public function set($value, $atomic = true)
    {
        $this->requiresCurrentField();

        if ($atomic) {
            $this->newObj['$set'][$this->currentField] = $value;
            return $this;
        }

        if (strpos($this->currentField, '.') === false) {
            $this->newObj[$this->currentField] = $value;
            return $this;
        }

        $keys = explode('.', $this->currentField);
        $current = &$this->newObj;
        foreach ($keys as $key) {
            $current = &$current[$key];
        }
        $current = $value;

        return $this;
    }

    /**
     * Specify $size criteria for the current field.
     *
     * @see Builder::size()
     * @see http://docs.mongodb.org/manual/reference/operator/size/
     * @param integer $size
     * @return self
     */
    public function size($size)
    {
        return $this->operator('$size', (integer) $size);
    }

    /**
     * Add $slice criteria to the expression for a $push operation.
     *
     * This is useful in conjunction with {@link Expr::each()} for a
     * {@link Expr::push()} operation. {@link Builder::selectSlice()} should be
     * used for specifying $slice for a query projection.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/slice/
     * @param integer $slice
     * @return self
     */
    public function slice($slice)
    {
        return $this->operator('$slice', $slice);
    }

    /**
     * Add $sort criteria to the expression for a $push operation.
     *
     * If sorting by multiple fields, the first argument should be an array of
     * field name (key) and order (value) pairs.
     *
     * This is useful in conjunction with {@link Expr::each()} for a
     * {@link Expr::push()} operation. {@link Builder::sort()} should be used to
     * sort the results of a query.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/sort/
     * @param array|string $fieldName Field name or array of field/order pairs
     * @param int|string $order       Field order (if one field is specified)
     * @return self
     */
    public function sort($fieldName, $order = null)
    {
        $fields = is_array($fieldName) ? $fieldName : array($fieldName => $order);

        foreach ($fields as $fieldName => $order) {
            if (is_string($order)) {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }
            $sort[$fieldName] = (integer) $order;
        }

        return $this->operator('$sort', $sort);
    }

    /**
     * Specify $text criteria for the current query.
     *
     * The $language option may be set with {@link Expr::language()}.
     *
     * @see Builder::text()
     * @see http://docs.mongodb.org/master/reference/operator/query/text/
     * @param string $search
     * @return self
     */
    public function text($search)
    {
        $this->query['$text'] = array('$search' => (string) $search);
        return $this;
    }

    /**
     * Specify $type criteria for the current field.
     *
     * @todo Remove support for string $type argument in 2.0
     * @see Builder::type()
     * @see http://docs.mongodb.org/manual/reference/operator/type/
     * @param integer $type
     * @return self
     */
    public function type($type)
    {
        if (is_string($type)) {
            $map = array(
                'double' => 1,
                'string' => 2,
                'object' => 3,
                'array' => 4,
                'binary' => 5,
                'undefined' => 6,
                'objectid' => 7,
                'boolean' => 8,
                'date' => 9,
                'null' => 10,
                'regex' => 11,
                'jscode' => 13,
                'symbol' => 14,
                'jscodewithscope' => 15,
                'integer32' => 16,
                'timestamp' => 17,
                'integer64' => 18,
                'maxkey' => 127,
                'minkey' => 255,
            );

            $type = isset($map[$type]) ? $map[$type] : $type;
        }

        return $this->operator('$type', $type);
    }

    /**
     * Unset the current field.
     *
     * The field will be removed from the document (not set to null).
     *
     * @see Builder::unsetField()
     * @see http://docs.mongodb.org/manual/reference/operator/unset/
     * @return self
     */
    public function unsetField()
    {
        $this->requiresCurrentField();
        $this->newObj['$unset'][$this->currentField] = 1;
        return $this;
    }

    /**
     * Specify a JavaScript expression to use for matching documents.
     *
     * @see Builder::where()
     * @see http://docs.mongodb.org/manual/reference/operator/where/
     * @param string|\MongoCode $javascript
     * @return self
     */
    public function where($javascript)
    {
        $this->query['$where'] = $javascript;
        return $this;
    }

    /**
     * Add $within criteria with a $box shape to the expression.
     *
     * @deprecated 1.1 MongoDB 2.4 deprecated $within in favor of $geoWithin
     * @see Expr::geoWithinBox()
     * @see http://docs.mongodb.org/manual/reference/operator/box/
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return self
     */
    public function withinBox($x1, $y1, $x2, $y2)
    {
        $shape = array('$box' => array(array($x1, $y1), array($x2, $y2)));

        return $this->operator('$within', $shape);
    }

    /**
     * Add $within criteria with a $center shape to the expression.
     *
     * @deprecated 1.1 MongoDB 2.4 deprecated $within in favor of $geoWithin
     * @see Expr::geoWithinCenter()
     * @see http://docs.mongodb.org/manual/reference/operator/center/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function withinCenter($x, $y, $radius)
    {
        $shape = array('$center' => array(array($x, $y), $radius));

        return $this->operator('$within', $shape);
    }

    /**
     * Add $within criteria with a $centerSphere shape to the expression.
     *
     * @deprecated 1.1 MongoDB 2.4 deprecated $within in favor of $geoWithin
     * @see Expr::geoWithinCenterSphere()
     * @see http://docs.mongodb.org/manual/reference/operator/centerSphere/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function withinCenterSphere($x, $y, $radius)
    {
        $shape = array('$centerSphere' => array(array($x, $y), $radius));

        return $this->operator('$within', $shape);
    }

    /**
     * Add $within criteria with a $polygon shape to the expression.
     *
     * Point coordinates are in x, y order (easting, northing for projected
     * coordinates, longitude, latitude for geographic coordinates).
     *
     * The last point coordinate is implicitly connected with the first.
     *
     * @deprecated 1.1 MongoDB 2.4 deprecated $within in favor of $geoWithin
     * @see Expr::geoWithinPolygon()
     * @see http://docs.mongodb.org/manual/reference/operator/polygon/
     * @param array $point,... Three or more point coordinate tuples
     * @return self
     * @throws InvalidArgumentException if less than three points are given
     */
    public function withinPolygon(/* array($x1, $y1), array($x2, $y2), ... */)
    {
        if (func_num_args() < 3) {
            throw new InvalidArgumentException('Polygon must be defined by three or more points.');
        }

        $shape = array('$polygon' => func_get_args());

        return $this->operator('$within', $shape);
    }

    /**
     * Ensure that a current field has been set.
     *
     * @throws LogicException if a current field has not been set
     */
    private function requiresCurrentField()
    {
        if ( ! $this->currentField) {
            throw new LogicException('This method requires you set a current field using field().');
        }
    }

    /**
     * Wraps equality criteria with an operator.
     *
     * If equality criteria was previously specified for a field, it cannot be
     * merged with other operators without first being wrapped in an operator of
     * its own. Ideally, we would wrap it with $eq, but that is only available
     * in MongoDB 2.8. Using a single-element $in is backwards compatible.
     *
     * @see Expr::operator()
     */
    private function wrapEqualityCriteria()
    {
        /* If the current field has no criteria yet, do nothing. This ensures
         * that we do not inadvertently inject {"$in": null} into the query.
         */
        if ($this->currentField && ! isset($this->query[$this->currentField]) && ! array_key_exists($this->currentField, $this->query)) {
            return;
        }

        if ($this->currentField) {
            $query = &$this->query[$this->currentField];
        } else {
            $query = &$this->query;
        }

        /* If the query is an empty array, we'll assume that the user has not
         * specified criteria. Otherwise, check if the array includes a query
         * operator (checking the first key is sufficient). If neither of these
         * conditions are met, we'll wrap the query value with $in.
         */
        if (is_array($query) && (empty($query) || strpos(key($query), '$') === 0)) {
            return;
        }

        $query = array('$in' => array($query));
    }
}
