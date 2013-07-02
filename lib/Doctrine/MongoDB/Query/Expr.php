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

/**
 * Fluent interface for building query and update expressions.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Expr
{
    /**
     * Mongo command prefix
     *
     * @var string
     */
    protected $cmd;

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

    public function __construct($cmd)
    {
        $this->cmd = $cmd;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    public function getNewObj()
    {
        return $this->newObj;
    }

    public function setNewObj(array $newObj)
    {
        $this->newObj = $newObj;
    }

    public function getCurrentField()
    {
        return $this->currentField;
    }

    public function field($field)
    {
        $this->currentField = $field;
        return $this;
    }

    public function equals($value)
    {
        if ($this->currentField) {
            $this->query[$this->currentField] = $value;
        } else {
            $this->query = $value;
        }
        return $this;
    }

    public function where($javascript)
    {
        return $this->field($this->cmd . 'where')->equals($javascript);
    }

    public function operator($operator, $value)
    {
        if ($this->currentField) {
            $this->query[$this->currentField][$operator] = $value;
        } else {
            $this->query[$operator] = $value;
        }
        return $this;
    }

    public function in($values)
    {
        return $this->operator($this->cmd . 'in', $values);
    }

    public function notIn($values)
    {
        return $this->operator($this->cmd . 'nin', (array) $values);
    }

    public function notEqual($value)
    {
        return $this->operator($this->cmd . 'ne', $value);
    }

    public function gt($value)
    {
        return $this->operator($this->cmd . 'gt', $value);
    }

    public function gte($value)
    {
        return $this->operator($this->cmd . 'gte', $value);
    }

    public function lt($value)
    {
        return $this->operator($this->cmd . 'lt', $value);
    }

    public function lte($value)
    {
        return $this->operator($this->cmd . 'lte', $value);
    }

    public function range($start, $end)
    {
        return $this->operator($this->cmd . 'gte', $start)->operator($this->cmd . 'lt', $end);
    }

    public function size($size)
    {
        return $this->operator($this->cmd . 'size', $size);
    }

    public function exists($bool)
    {
        return $this->operator($this->cmd . 'exists', $bool);
    }

    public function type($type)
    {
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
            'minkey' => 255,
            'maxkey' => 127
        );
        if (is_string($type) && isset($map[$type])) {
            $type = $map[$type];
        }
        return $this->operator($this->cmd . 'type', $type);
    }

    public function all($values)
    {
        return $this->operator($this->cmd . 'all', (array) $values);
    }

    public function mod($mod)
    {
        return $this->operator($this->cmd . 'mod', $mod);
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

        if ( ! isset($query[$this->cmd . 'near']) && ! isset($query[$this->cmd . 'nearSphere'])) {
            throw new BadMethodCallException(
                'This method requires a $near or $nearSphere operator (call near() or nearSphere() first)'
            );
        }

        if (isset($query[$this->cmd . 'near'][$this->cmd . 'geometry'])) {
            $query[$this->cmd . 'near'][$this->cmd . 'maxDistance'] = $maxDistance;
        } elseif (isset($query[$this->cmd . 'nearSphere'][$this->cmd . 'geometry'])) {
            $query[$this->cmd . 'nearSphere'][$this->cmd . 'maxDistance'] = $maxDistance;
        } else {
            $query[$this->cmd . 'maxDistance'] = $maxDistance;
        }

        return $this;
    }

    /**
     * Add $near criteria to the expression.
     *
     * A GeoJSON Point may be provided as the first parameter for 2dsphere
     * queries.
     *
     * @see Expr::near()
     * @see http://docs.mongodb.org/manual/reference/operator/near/
     * @param float|Point $x
     * @param float $y
     * @return self
     */
    public function near($x, $y = null)
    {
        if ($x instanceof Point) {
            return $this->operator($this->cmd . 'near', array($this->cmd . 'geometry' => $x->jsonSerialize()));
        }

        return $this->operator($this->cmd . 'near', array($x, $y));
    }

    /**
     * Add $nearSphere criteria to the expression.
     *
     * A GeoJSON Point may be provided as the first parameter for 2dsphere
     * queries.
     *
     * @see Expr::nearSphere()
     * @see http://docs.mongodb.org/manual/reference/operator/nearSphere/
     * @param float|Point $x
     * @param float $y
     * @return self
     */
    public function nearSphere($x, $y = null)
    {
        if ($x instanceof Point) {
            return $this->operator($this->cmd . 'nearSphere', array($this->cmd . 'geometry' => $x->jsonSerialize()));
        }

        return $this->operator($this->cmd . 'nearSphere', array($x, $y));
    }

    /**
     * Add $geoIntersects criteria with a GeoJSON geometry to the expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/geoIntersects/
     * @param Geometry $geometry
     * @return self
     */
    public function geoIntersects(Geometry $geometry)
    {
        $shape = array($this->cmd . 'geometry' => $geometry->jsonSerialize());

        return $this->operator($this->cmd . 'geoIntersects', $shape);
    }

    /**
     * Add $geoWithin criteria with a GeoJSON geometry to the expression.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/geoIntersects/
     * @param Geometry $geometry
     * @return self
     */
    public function geoWithin(Geometry $geometry)
    {
        $shape = array($this->cmd . 'geometry' => $geometry->jsonSerialize());

        return $this->operator($this->cmd . 'geoWithin', $shape);
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
     * @see http://docs.mongodb.org/manual/reference/operator/box/
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return self
     */
    public function geoWithinBox($x1, $y1, $x2, $y2)
    {
        $shape = array($this->cmd . 'box' => array(array($x1, $y1), array($x2, $y2)));

        return $this->operator($this->cmd . 'geoWithin', $shape);
    }

    /**
     * Add $geoWithin criteria with a $center shape to the expression.
     *
     * Note: the $center operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see Expr::geoWithinCenter()
     * @see http://docs.mongodb.org/manual/reference/operator/center/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function geoWithinCenter($x, $y, $radius)
    {
        $shape = array($this->cmd . 'center' => array(array($x, $y), $radius));

        return $this->operator($this->cmd . 'geoWithin', $shape);
    }

    /**
     * Add $geoWithin criteria with a $centerSphere shape to the expression.
     *
     * Note: the $centerSphere operator supports both 2d and 2dsphere indexes.
     *
     * @see http://docs.mongodb.org/manual/reference/operator/centerSphere/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function geoWithinCenterSphere($x, $y, $radius)
    {
        $shape = array($this->cmd . 'centerSphere' => array(array($x, $y), $radius));

        return $this->operator($this->cmd . 'geoWithin', $shape);
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

        $shape = array($this->cmd . 'polygon' => func_get_args());

        return $this->operator($this->cmd . 'geoWithin', $shape);
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
        $shape = array($this->cmd . 'box' => array(array($x1, $y1), array($x2, $y2)));

        return $this->operator($this->cmd . 'within', $shape);
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
        $shape = array($this->cmd . 'center' => array(array($x, $y), $radius));

        return $this->operator($this->cmd . 'within', $shape);
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
        $shape = array($this->cmd . 'centerSphere' => array(array($x, $y), $radius));

        return $this->operator($this->cmd . 'within', $shape);
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

        $shape = array($this->cmd . 'polygon' => func_get_args());

        return $this->operator($this->cmd . 'within', $shape);
    }

    public function set($value, $atomic = true)
    {
        $this->requiresCurrentField();
        if ($atomic === true) {
            $this->newObj[$this->cmd . 'set'][$this->currentField] = $value;
        } else {
            if (strpos($this->currentField, '.') !== false) {
                $e = explode('.', $this->currentField);
                $current = &$this->newObj;
                foreach ($e as $v) {
                    $current = &$current[$v];
                }
                $current = $value;
            } else {
                $this->newObj[$this->currentField] = $value;
            }
        }
        return $this;
    }

    public function inc($value)
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'inc'][$this->currentField] = $value;
        return $this;
    }

    public function unsetField()
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'unset'][$this->currentField] = 1;
        return $this;
    }

    public function push($value)
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'push'][$this->currentField] = $value;
        return $this;
    }

    public function pushAll(array $valueArray)
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'pushAll'][$this->currentField] = $valueArray;
        return $this;
    }

    public function addToSet($value)
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'addToSet'][$this->currentField] = $value;
        return $this;
    }

    public function addManyToSet(array $values)
    {
        $this->requiresCurrentField();
        if ( ! isset($this->newObj[$this->cmd . 'addToSet'][$this->currentField])) {
            $this->newObj[$this->cmd . 'addToSet'][$this->currentField][$this->cmd . 'each'] = array();
        }
        if ( ! is_array($this->newObj[$this->cmd . 'addToSet'][$this->currentField])) {
            $this->newObj[$this->cmd . 'addToSet'][$this->currentField] = array($this->cmd . 'each' => array($this->newObj[$this->cmd . 'addToSet'][$this->currentField]));
        }
        $this->newObj[$this->cmd . 'addToSet'][$this->currentField][$this->cmd . 'each'] = array_merge_recursive($this->newObj[$this->cmd . 'addToSet'][$this->currentField][$this->cmd . 'each'], $values);
    }

    public function popFirst()
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'pop'][$this->currentField] = 1;
        return $this;
    }

    public function popLast()
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'pop'][$this->currentField] = -1;
        return $this;
    }

    public function pull($value)
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'pull'][$this->currentField] = $value;
        return $this;
    }

    public function pullAll(array $valueArray)
    {
        $this->requiresCurrentField();
        $this->newObj[$this->cmd . 'pullAll'][$this->currentField] = $valueArray;
        return $this;
    }

    public function addOr($expression)
    {
        if ($expression instanceof Expr) {
            $expression = $expression->getQuery();
        }
        $this->query[$this->cmd . 'or'][] = $expression;
        return $this;
    }

    public function addAnd($expression)
    {
        if ($expression instanceof Expr) {
            $expression = $expression->getQuery();
        }
        $this->query[$this->cmd . 'and'][] = $expression;
        return $this;
    }

    public function addNor($expression)
    {
        if ($expression instanceof Expr) {
            $expression = $expression->getQuery();
        }
        $this->query[$this->cmd . 'nor'][] = $expression;
        return $this;
    }

    public function elemMatch($expression)
    {
        if ($expression instanceof Expr) {
            $expression = $expression->getQuery();
        }
        return $this->operator($this->cmd . 'elemMatch', $expression);
    }

    public function not($expression)
    {
        if ($expression instanceof Expr) {
            $expression = $expression->getQuery();
        }
        return $this->operator($this->cmd . 'not', $expression);
    }

    private function requiresCurrentField()
    {
        if ( ! $this->currentField) {
            throw new \LogicException('This method requires you set a current field using field().');
        }
    }
}
