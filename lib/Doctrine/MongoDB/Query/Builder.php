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

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Database;
use GeoJson\Geometry\Geometry;
use GeoJson\Geometry\Point;
use BadMethodCallException;

/**
 * Fluent interface for building Query objects.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Builder
{
    /**
     * The Collection instance.
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Array containing the query data.
     *
     * @var array
     */
    protected $query = array('type' => Query::TYPE_FIND);

    /**
     * The Expr instance used for building this query.
     *
     * This object includes the query criteria and the "new object" used for
     * insert and update queries.
     *
     * @var Expr $expr
     */
    protected $expr;

    /**
     * Create a new query builder.
     *
     * @param Collection $collection
     */
    public function __construct(Collection $collection)
    {
        $this->collection = $collection;
        $this->expr = new Expr();
    }

    /**
     * Add an $and clause to the current query.
     *
     * You can create a new expression using the {@link Builder::expr()} method.
     *
     * @see Expr::addAnd()
     * @see http://docs.mongodb.org/manual/reference/operator/and/
     * @param array|Expr $expression
     * @return self
     */
    public function addAnd($expression)
    {
        $this->expr->addAnd($expression);
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
     * @deprecated 1.1 Use {@link Builder::addToSet()} with {@link Expr::each()}; Will be removed in 2.0
     * @see Expr::addManyToSet()
     * @see http://docs.mongodb.org/manual/reference/operator/addToSet/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @param array $values
     * @return self
     */
    public function addManyToSet(array $values)
    {
        $this->expr->addManyToSet($values);
        return $this;
    }

    /**
     * Add a $nor clause to the current query.
     *
     * You can create a new expression using the {@link Builder::expr()} method.
     *
     * @see Expr::addNor()
     * @see http://docs.mongodb.org/manual/reference/operator/nor/
     * @param array|Expr $expression
     * @return self
     */
    public function addNor($expression)
    {
        $this->expr->addNor($expression);
        return $this;
    }

    /**
     * Add an $or clause to the current query.
     *
     * You can create a new expression using the {@link Builder::expr()} method.
     *
     * @see Expr::addOr()
     * @see http://docs.mongodb.org/manual/reference/operator/or/
     * @param array|Expr $expression
     * @return self
     */
    public function addOr($expression)
    {
        $this->expr->addOr($expression);
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
     * @see Expr::addToSet()
     * @see http://docs.mongodb.org/manual/reference/operator/addToSet/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @param mixed|Expr $valueOrExpression
     * @return self
     */
    public function addToSet($valueOrExpression)
    {
        $this->expr->addToSet($valueOrExpression);
        return $this;
    }

    /**
     * Specify $all criteria for the current field.
     *
     * @see Expr::all()
     * @see http://docs.mongodb.org/manual/reference/operator/all/
     * @param array $values
     * @return self
     */
    public function all(array $values)
    {
        $this->expr->all($values);
        return $this;
    }

    /**
     * Change the query type to count.
     *
     * @return self
     */
    public function count()
    {
        $this->query['type'] = Query::TYPE_COUNT;
        return $this;
    }

    /**
     * Return an array of information about the Builder state for debugging.
     *
     * The $name parameter may be used to return a specific key from the
     * internal $query array property. If omitted, the entire array will be
     * returned.
     *
     * @param string $name
     * @return mixed
     */
    public function debug($name = null)
    {
        return $name !== null ? $this->query[$name] : $this->query;
    }

    /**
     * Set the "distanceMultiplier" option for a geoNear command query.
     *
     * @param float $distanceMultiplier
     * @return self
     * @throws BadMethodCallException if the query is not a geoNear command
     */
    public function distanceMultiplier($distanceMultiplier)
    {
        if ($this->query['type'] !== Query::TYPE_GEO_NEAR) {
            throw new BadMethodCallException('This method requires a geoNear command (call geoNear() first)');
        }

        $this->query['geoNear']['options']['distanceMultiplier'] = $distanceMultiplier;
        return $this;
    }

    /**
     * Change the query type to a distinct command.
     *
     * @see http://docs.mongodb.org/manual/reference/command/distinct/
     * @param string $field
     * @return self
     */
    public function distinct($field)
    {
        $this->query['type'] = Query::TYPE_DISTINCT;
        $this->query['distinct'] = $field;
        return $this;
    }

    /**
     * Set whether the query should return its result as an EagerCursor.
     *
     * @param boolean $bool
     * @return self
     */
    public function eagerCursor($bool = true)
    {
        $this->query['eagerCursor'] = (boolean) $bool;
        return $this;
    }

    /**
     * Specify $elemMatch criteria for the current field.
     *
     * You can create a new expression using the {@link Builder::expr()} method.
     *
     * @see Expr::elemMatch()
     * @see http://docs.mongodb.org/manual/reference/operator/elemMatch/
     * @param array|Expr $expression
     * @return self
     */
    public function elemMatch($expression)
    {
        $this->expr->elemMatch($expression);
        return $this;
    }

    /**
     * Specify an equality match for the current field.
     *
     * @see Expr::equals()
     * @param mixed $value
     * @return self
     */
    public function equals($value)
    {
        $this->expr->equals($value);
        return $this;
    }

    /**
     * Set one or more fields to be excluded from the query projection.
     *
     * If fields have been selected for inclusion, only the "_id" field may be
     * excluded.
     *
     * @param array|string $fieldName
     * @return self
     */
    public function exclude($fieldName = null)
    {
        if ( ! isset($this->query['select'])) {
            $this->query['select'] = array();
        }

        $fieldNames = is_array($fieldName) ? $fieldName : func_get_args();

        foreach ($fieldNames as $fieldName) {
            $this->query['select'][$fieldName] = 0;
        }

        return $this;
    }

    /**
     * Specify $exists criteria for the current field.
     *
     * @see Expr::exists()
     * @see http://docs.mongodb.org/manual/reference/operator/exists/
     * @param boolean $bool
     * @return self
     */
    public function exists($bool)
    {
        $this->expr->exists((boolean) $bool);
        return $this;
    }

    /**
     * Create a new Expr instance that can be used to build partial expressions
     * for other operator methods.
     *
     * @return Expr $expr
     */
    public function expr()
    {
        return new Expr();
    }

    /**
     * Set the current field for building the expression.
     *
     * @see Expr::field()
     * @param string $field
     * @return self
     */
    public function field($field)
    {
        $this->expr->field((string) $field);
        return $this;
    }

    /**
     * Set the "finalize" option for a mapReduce or group command.
     *
     * @param string|\MongoCode $finalize
     * @return self
     * @throws BadMethodCallException if the query is not a mapReduce or group command
     */
    public function finalize($finalize)
    {
        switch ($this->query['type']) {
            case Query::TYPE_MAP_REDUCE:
                $this->query['mapReduce']['options']['finalize'] = $finalize;
                break;

            case Query::TYPE_GROUP:
                $this->query['group']['options']['finalize'] = $finalize;
                break;

            default:
                throw new BadMethodCallException('mapReduce(), map() or group() must be called before finalize()');
        }

        return $this;
    }

    /**
     * Change the query type to find.
     *
     * @return self
     */
    public function find()
    {
        $this->query['type'] = Query::TYPE_FIND;
        return $this;
    }

    /**
     * Change the query type to findAndRemove (uses the findAndModify command).
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @return self
     */
    public function findAndRemove()
    {
        $this->query['type'] = Query::TYPE_FIND_AND_REMOVE;
        return $this;
    }

    /**
     * Change the query type to findAndUpdate (uses the findAndModify command).
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @return self
     */
    public function findAndUpdate()
    {
        $this->query['type'] = Query::TYPE_FIND_AND_UPDATE;
        return $this;
    }

    /**
     * Add $geoIntersects criteria with a GeoJSON geometry to the query.
     *
     * The geometry parameter GeoJSON object or an array corresponding to the
     * geometry's JSON representation.
     *
     * @see Expr::geoIntersects()
     * @see http://docs.mongodb.org/manual/reference/operator/geoIntersects/
     * @param array|Geometry $geometry
     * @return self
     */
    public function geoIntersects($geometry)
    {
        $this->expr->geoIntersects($geometry);
        return $this;
    }

    /**
     * Change the query type to a geoNear command.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation. If GeoJSON is
     * used, the "spherical" option will default to true.
     *
     * This method sets the "near" option for the geoNear command. The "num"
     * option may be set using {@link Expr::limit()}. The "distanceMultiplier",
     * "maxDistance", and "spherical" options may be set using their respective
     * builder methods. Additional query criteria will be assigned to the
     * "query" option.
     *
     * @see http://docs.mongodb.org/manual/reference/command/geoNear/
     * @param float|array|Point $x
     * @param float $y
     * @return self
     */
    public function geoNear($x, $y = null)
    {
        if ($x instanceof Point) {
            $x = $x->jsonSerialize();
        }

        $this->query['type'] = Query::TYPE_GEO_NEAR;
        $this->query['geoNear'] = array(
            'near' => is_array($x) ? $x : array($x, $y),
            'options' => array(
                'spherical' => is_array($x) && isset($x['type']),
            ),
        );
        return $this;
    }

    /**
     * Add $geoWithin criteria with a GeoJSON geometry to the query.
     *
     * The geometry parameter GeoJSON object or an array corresponding to the
     * geometry's JSON representation.
     *
     * @see Expr::geoWithin()
     * @see http://docs.mongodb.org/manual/reference/operator/geoWithin/
     * @param array|Geometry $geometry
     * @return self
     */
    public function geoWithin(Geometry $geometry)
    {
        $this->expr->geoWithin($geometry);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a $box shape to the query.
     *
     * A rectangular polygon will be constructed from a pair of coordinates
     * corresponding to the bottom left and top right corners.
     *
     * Note: the $box operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see Expr::geoWithinBox()
     * @see http://docs.mongodb.org/manual/reference/operator/box/
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return self
     */
    public function geoWithinBox($x1, $y1, $x2, $y2)
    {
        $this->expr->geoWithinBox($x1, $y1, $x2, $y2);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a $center shape to the query.
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
        $this->expr->geoWithinCenter($x, $y, $radius);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a $centerSphere shape to the query.
     *
     * Note: the $centerSphere operator supports both 2d and 2dsphere indexes.
     *
     * @see Expr::geoWithinCenterSphere()
     * @see http://docs.mongodb.org/manual/reference/operator/centerSphere/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function geoWithinCenterSphere($x, $y, $radius)
    {
        $this->expr->geoWithinCenterSphere($x, $y, $radius);
        return $this;
    }

    /**
     * Add $geoWithin criteria with a $polygon shape to the query.
     *
     * Point coordinates are in x, y order (easting, northing for projected
     * coordinates, longitude, latitude for geographic coordinates).
     *
     * The last point coordinate is implicitly connected with the first.
     *
     * Note: the $polygon operator only supports legacy coordinate pairs and 2d
     * indexes. This cannot be used with 2dsphere indexes and GeoJSON shapes.
     *
     * @see Expr::geoWithinPolygon()
     * @see http://docs.mongodb.org/manual/reference/operator/polygon/
     * @param array $point,... Three or more point coordinate tuples
     * @return self
     */
    public function geoWithinPolygon(/* array($x1, $y1), ... */)
    {
        call_user_func_array(array($this->expr, 'geoWithinPolygon'), func_get_args());
        return $this;
    }

    /**
     * Return the expression's "new object".
     *
     * @see Expr::getNewObj()
     * @return array
     */
    public function getNewObj()
    {
        return $this->expr->getNewObj();
    }

    /**
     * Set the expression's "new object".
     *
     * @see Expr::setNewObj()
     * @param array $newObj
     * @return self
     */
    public function setNewObj(array $newObj)
    {
        $this->expr->setNewObj($newObj);
        return $this;
    }

    /**
     * Create a new Query instance from the Builder state.
     *
     * @param array $options
     * @return Query
     */
    public function getQuery(array $options = array())
    {
        $query = $this->query;
        $query['query'] = $this->expr->getQuery();
        $query['newObj'] = $this->expr->getNewObj();
        return new Query($this->collection, $query, $options);
    }

    /**
     * Return the expression's query criteria.
     *
     * @see Expr::getQuery()
     * @return array
     */
    public function getQueryArray()
    {
        return $this->expr->getQuery();
    }

    /**
     * Set the expression's query criteria.
     *
     * @see Expr::setQuery()
     * @param array $query
     * @return self
     */
    public function setQueryArray(array $query)
    {
        $this->expr->setQuery($query);
        return $this;
    }

    /**
     * Get the type of this query.
     *
     * @return integer $type
     */
    public function getType()
    {
        return $this->query['type'];
    }

    /**
     * Change the query type to a group command.
     *
     * If the "reduce" option is not specified when calling this method, it must
     * be set with the {@link Builder::reduce()} method.
     *
     * @see http://docs.mongodb.org/manual/reference/command/group/
     * @param mixed $keys
     * @param array $initial
     * @param string|\MongoCode $reduce
     * @param array $options
     * @return self
     */
    public function group($keys, array $initial, $reduce = null, array $options = array())
    {
        $this->query['type'] = Query::TYPE_GROUP;
        $this->query['group'] = array(
            'keys' => $keys,
            'initial' => $initial,
            'reduce' => $reduce,
            'options' => $options,
        );
        return $this;
    }

    /**
     * Specify $gt criteria for the current field.
     *
     * @see Expr::gt()
     * @see http://docs.mongodb.org/manual/reference/operator/gt/
     * @param mixed $value
     * @return self
     */
    public function gt($value)
    {
        $this->expr->gt($value);
        return $this;
    }

    /**
     * Specify $gte criteria for the current field.
     *
     * @see Expr::gte()
     * @see http://docs.mongodb.org/manual/reference/operator/gte/
     * @param mixed $value
     * @return self
     */
    public function gte($value)
    {
        $this->expr->gte($value);
        return $this;
    }

    /**
     * Set the index hint for the query.
     *
     * @param array|string $index
     * @return self
     */
    public function hint($index)
    {
        $this->query['hint'] = $index;
        return $this;
    }

    /**
     * Set the immortal cursor flag.
     *
     * @param boolean $bool
     * @return self
     */
    public function immortal($bool = true)
    {
        $this->query['immortal'] = (boolean) $bool;
        return $this;
    }

    /**
     * Specify $in criteria for the current field.
     *
     * @see Expr::in()
     * @see http://docs.mongodb.org/manual/reference/operator/in/
     * @param array $values
     * @return self
     */
    public function in(array $values)
    {
        $this->expr->in($values);
        return $this;
    }

    /**
     * Increment the current field.
     *
     * If the field does not exist, it will be set to this value.
     *
     * @see Expr::inc()
     * @see http://docs.mongodb.org/manual/reference/operator/inc/
     * @param float|integer $value
     * @return self
     */
    public function inc($value)
    {
        $this->expr->inc($value);
        return $this;
    }

    /**
     * Change the query type to insert.
     *
     * @return self
     */
    public function insert()
    {
        $this->query['type'] = Query::TYPE_INSERT;
        return $this;
    }

    /**
     * Set the limit for the query.
     *
     * This is only relevant for find queries and geoNear and mapReduce
     * commands.
     *
     * @see Query::prepareCursor()
     * @param integer $limit
     * @return self
     */
    public function limit($limit)
    {
        $this->query['limit'] = (integer) $limit;
        return $this;
    }

    /**
     * Specify $lt criteria for the current field.
     *
     * @see Expr::lte()
     * @see http://docs.mongodb.org/manual/reference/operator/lte/
     * @param mixed $value
     * @return self
     */
    public function lt($value)
    {
        $this->expr->lt($value);
        return $this;
    }

    /**
     * Specify $lte criteria for the current field.
     *
     * @see Expr::lte()
     * @see http://docs.mongodb.org/manual/reference/operator/lte/
     * @param mixed $value
     * @return self
     */
    public function lte($value)
    {
        $this->expr->lte($value);
        return $this;
    }

    /**
     * Change the query type to a mapReduce command.
     *
     * The "reduce" option is not specified when calling this method; it must
     * be set with the {@link Builder::reduce()} method.
     *
     * The "out" option defaults to inline, like {@link Builder::mapReduce()}.
     *
     * @see http://docs.mongodb.org/manual/reference/command/mapReduce/
     * @param string|\MongoCode $map
     * @return self
     */
    public function map($map)
    {
        $this->query['type'] = Query::TYPE_MAP_REDUCE;
        $this->query['mapReduce'] = array(
            'map' => $map,
            'reduce' => null,
            'out' => array('inline' => true),
            'options' => array(),
        );
        return $this;
    }

    /**
     * Change the query type to a mapReduce command.
     *
     * @see http://docs.mongodb.org/manual/reference/command/mapReduce/
     * @param string|\MongoCode $map
     * @param string|\MongoCode $reduce
     * @param array|string $out
     * @param array $options
     * @return self
     */
    public function mapReduce($map, $reduce, $out = array('inline' => true), array $options = array())
    {
        $this->query['type'] = Query::TYPE_MAP_REDUCE;
        $this->query['mapReduce'] = array(
            'map' => $map,
            'reduce' => $reduce,
            'out' => $out,
            'options' => $options
        );
        return $this;
    }

    /**
     * Set additional options for a mapReduce command.
     *
     * @param array $options
     * @return self
     * @throws BadMethodCallException if the query is not a mapReduce command
     */
    public function mapReduceOptions(array $options)
    {
        if ($this->query['type'] !== Query::TYPE_MAP_REDUCE) {
            throw new BadMethodCallException('This method requires a mapReduce command (call map() or mapReduce() first)');
        }

        $this->query['mapReduce']['options'] = $options;
        return $this;
    }

    /**
     * Set the "maxDistance" option for a geoNear command query or add
     * $maxDistance criteria to the query.
     *
     * If the query is a geoNear command ({@link Expr::geoNear()} was called),
     * the "maxDistance" command option will be set; otherwise, $maxDistance
     * will be added to the current expression.
     *
     * If the query uses GeoJSON points, $maxDistance will be interpreted in
     * meters. If legacy point coordinates are used, $maxDistance will be
     * interpreted in radians.
     *
     * @see Expr::maxDistance()
     * @see http://docs.mongodb.org/manual/reference/command/geoNear/
     * @see http://docs.mongodb.org/manual/reference/operator/maxDistance/
     * @see http://docs.mongodb.org/manual/reference/operator/near/
     * @see http://docs.mongodb.org/manual/reference/operator/nearSphere/
     * @param float $maxDistance
     * @return self
     */
    public function maxDistance($maxDistance)
    {
        if ($this->query['type'] === Query::TYPE_GEO_NEAR) {
            $this->query['geoNear']['options']['maxDistance'] = $maxDistance;
        } else {
            $this->expr->maxDistance($maxDistance);
        }
        return $this;
    }

    /**
     * Specify $mod criteria for the current field.
     *
     * @see Expr::mod()
     * @see http://docs.mongodb.org/manual/reference/operator/mod/
     * @param float|integer $divisor
     * @param float|integer $remainder
     * @return self
     */
    public function mod($divisor, $remainder = 0)
    {
        $this->expr->mod($divisor, $remainder);
        return $this;
    }

    /**
     * Set the "multiple" option for an update query.
     *
     * @param boolean $bool
     * @return self
     */
    public function multiple($bool = true)
    {
        $this->query['multiple'] = (boolean) $bool;
        return $this;
    }

    /**
     * Add $near criteria to the query.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation.
     *
     * @see Expr::near()
     * @see http://docs.mongodb.org/manual/reference/operator/near/
     * @param float|array|Point $x
     * @param float $y
     * @return self
     */
    public function near($x, $y = null)
    {
        $this->expr->near($x, $y);
        return $this;
    }

    /**
     * Add $nearSphere criteria to the query.
     *
     * A GeoJSON point may be provided as the first and only argument for
     * 2dsphere queries. This single parameter may be a GeoJSON point object or
     * an array corresponding to the point's JSON representation.
     *
     * @see Expr::nearSphere()
     * @see http://docs.mongodb.org/manual/reference/operator/nearSphere/
     * @param float|array|Point $x
     * @param float $y
     * @return self
     */
    public function nearSphere($x, $y = null)
    {
        $this->expr->nearSphere($x, $y);
        return $this;
    }

    /**
     * Negates an expression for the current field.
     *
     * You can create a new expression using the {@link Builder::expr()} method.
     *
     * @see Expr::not()
     * @see http://docs.mongodb.org/manual/reference/operator/not/
     * @param array|Expr $expression
     * @return self
     */
    public function not($expression)
    {
        $this->expr->not($expression);
        return $this;
    }

    /**
     * Specify $ne criteria for the current field.
     *
     * @see Expr::notEqual()
     * @see http://docs.mongodb.org/manual/reference/operator/ne/
     * @param mixed $value
     * @return self
     */
    public function notEqual($value)
    {
        $this->expr->notEqual($value);
        return $this;
    }

    /**
     * Specify $nin criteria for the current field.
     *
     * @see Expr::notIn()
     * @see http://docs.mongodb.org/manual/reference/operator/nin/
     * @param array $values
     * @return self
     */
    public function notIn(array $values)
    {
        $this->expr->notIn($values);
        return $this;
    }

    /**
     * Set the "out" option for a mapReduce command.
     *
     * @param array|string $out
     * @return self
     * @throws BadMethodCallException if the query is not a mapReduce command
     */
    public function out($out)
    {
        if ($this->query['type'] !== Query::TYPE_MAP_REDUCE) {
            throw new BadMethodCallException('This method requires a mapReduce command (call map() or mapReduce() first)');
        }

        $this->query['mapReduce']['out'] = $out;
        return $this;
    }

    /**
     * Remove the first element from the current array field.
     *
     * @see Expr::popFirst()
     * @see http://docs.mongodb.org/manual/reference/operator/pop/
     * @return self
     */
    public function popFirst()
    {
        $this->expr->popFirst();
        return $this;
    }

    /**
     * Remove the last element from the current array field.
     *
     * @see Expr::popLast()
     * @see http://docs.mongodb.org/manual/reference/operator/pop/
     * @return self
     */
    public function popLast()
    {
        $this->expr->popLast();
        return $this;
    }

    /**
     * Remove all elements matching the given value or expression from the
     * current array field.
     *
     * @see Expr::pull()
     * @see http://docs.mongodb.org/manual/reference/operator/pull/
     * @param mixed|Expr $valueOrExpression
     * @return self
     */
    public function pull($valueOrExpression)
    {
        $this->expr->pull($valueOrExpression);
        return $this;
    }

    /**
     * Remove all elements matching any of the given values from the current
     * array field.
     *
     * @see Expr::pullAll()
     * @see http://docs.mongodb.org/manual/reference/operator/pullAll/
     * @param array $values
     * @return self
     */
    public function pullAll(array $values)
    {
        $this->expr->pullAll($values);
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
     * @see Expr::push()
     * @see http://docs.mongodb.org/manual/reference/operator/push/
     * @see http://docs.mongodb.org/manual/reference/operator/each/
     * @see http://docs.mongodb.org/manual/reference/operator/slice/
     * @see http://docs.mongodb.org/manual/reference/operator/sort/
     * @param mixed|Expr $valueOrExpression
     * @return self
     */
    public function push($valueOrExpression)
    {
        $this->expr->push($valueOrExpression);
        return $this;
    }

    /**
     * Append multiple values to the current array field.
     *
     * If the field does not exist, it will be set to an array containing the
     * values in the argument. If the field is not an array, the query will
     * yield an error.
     *
     * This operator is deprecated in MongoDB 2.4. {@link Builder::push()} and
     * {@link Expr::each()} should be used in its place.
     *
     * @see Expr::pushAll()
     * @see http://docs.mongodb.org/manual/reference/operator/pushAll/
     * @param array $values
     * @return self
     */
    public function pushAll(array $values)
    {
        $this->expr->pushAll($values);
        return $this;
    }

    /**
     * Specify $gte and $lt criteria for the current field.
     *
     * This method is shorthand for specifying $gte criteria on the lower bound
     * and $lt criteria on the upper bound. The upper bound is not inclusive.
     *
     * @see Expr::range()
     * @param mixed $start
     * @param mixed $end
     * @return self
     */
    public function range($start, $end)
    {
        $this->expr->range($start, $end);
        return $this;
    }

    /**
     * Set the "reduce" option for a mapReduce or group command.
     *
     * @param string|\MongoCode $reduce
     * @return self
     * @throws BadMethodCallException if the query is not a mapReduce or group command
     */
    public function reduce($reduce)
    {
        switch ($this->query['type']) {
            case Query::TYPE_MAP_REDUCE:
                $this->query['mapReduce']['reduce'] = $reduce;
                break;

            case Query::TYPE_GROUP:
                $this->query['group']['reduce'] = $reduce;
                break;

            default:
                throw new BadMethodCallException('mapReduce(), map() or group() must be called before reduce()');
        }

        return $this;
    }

    /**
     * Change the query type to remove.
     *
     * @return self
     */
    public function remove()
    {
        $this->query['type'] = Query::TYPE_REMOVE;
        return $this;
    }

    /**
     * Rename the current field.
     *
     * @see Expr::rename()
     * @see http://docs.mongodb.org/manual/reference/operator/rename/
     * @param string $name
     * @return self
     */
    public function rename($name)
    {
        $this->expr->rename($name);
        return $this;
    }

    /**
     * Set the "new" option for a findAndUpdate command.
     *
     * @param boolean $bool
     * @return self
     */
    public function returnNew($bool = true)
    {
        $this->query['new'] = (boolean) $bool;
        return $this;
    }

    /**
     * Set one or more fields to be included in the query projection.
     *
     * @param array|string $fieldName
     * @return self
     */
    public function select($fieldName = null)
    {
        if ( ! isset($this->query['select'])) {
            $this->query['select'] = array();
        }

        $fieldNames = is_array($fieldName) ? $fieldName : func_get_args();

        foreach ($fieldNames as $fieldName) {
            $this->query['select'][$fieldName] = 1;
        }

        return $this;
    }

    /**
     * Select only matching embedded documents in an array field for the query
     * projection.
     *
     * @see http://docs.mongodb.org/manual/reference/projection/elemMatch/
     * @param string $fieldName
     * @param array|Expr $expression
     * @return self
     */
    public function selectElemMatch($fieldName, $expression)
    {
        if ( ! isset($this->query['select'])) {
            $this->query['select'] = array();
        }

        if ($expression instanceof Expr) {
            $expression = $expression->getQuery();
        }
        $this->query['select'][$fieldName] = array('$elemMatch' => $expression);
        return $this;
    }

    /**
     * Select a slice of an array field for the query projection.
     *
     * The $countOrSkip parameter has two very different meanings, depending on
     * whether or not $limit is provided. See the MongoDB documentation for more
     * information.
     *
     * @see http://docs.mongodb.org/manual/reference/projection/slice/
     * @param string $fieldName
     * @param integer $countOrSkip Count parameter, or skip if limit is specified
     * @param integer $limit       Limit parameter used in conjunction with skip
     * @return self
     */
    public function selectSlice($fieldName, $countOrSkip, $limit = null)
    {
        if ( ! isset($this->query['select'])) {
            $this->query['select'] = array();
        }

        $slice = $countOrSkip;
        if ($limit !== null) {
            $slice = array($slice, $limit);
        }
        $this->query['select'][$fieldName] = array('$slice' => $slice);
        return $this;
    }

    /**
     * Set the current field to a value.
     *
     * This is only relevant for insert, update, or findAndUpdate queries. For
     * update and findAndUpdate queries, the $atomic parameter will determine
     * whether or not a $set operator is used.
     *
     * @see Expr::set()
     * @see http://docs.mongodb.org/manual/reference/operator/set/
     * @param mixed $value
     * @param boolean $atomic
     * @return self
     */
    public function set($value, $atomic = true)
    {
        $this->expr->set($value, $atomic && $this->query['type'] !== Query::TYPE_INSERT);
        return $this;
    }

    /**
     * Set the read preference for the query.
     *
     * This is only relevant for read-only queries and commands.
     *
     * @see http://docs.mongodb.org/manual/core/read-preference/
     * @param mixed $value
     * @param boolean $atomic
     * @return self
     */
    public function setReadPreference($readPreference, array $tags = null)
    {
        $this->query['readPreference'] = $readPreference;
        $this->query['readPreferenceTags'] = $tags;
        return $this;
    }

    /**
     * Specify $size criteria for the current field.
     *
     * @see Expr::size()
     * @see http://docs.mongodb.org/manual/reference/operator/size/
     * @param integer $size
     * @return self
     */
    public function size($size)
    {
        $this->expr->size((integer) $size);
        return $this;
    }

    /**
     * Set the skip for the query cursor.
     *
     * This is only relevant for find queries, or mapReduce queries that store
     * results in an output collecton and return a cursor.
     *
     * @see Query::prepareCursor()
     * @param integer $skip
     * @return self
     */
    public function skip($skip)
    {
        $this->query['skip'] = (integer) $skip;
        return $this;
    }

    /**
     * Set whether the query may be directed to replica set secondaries.
     *
     * If the driver supports read preferences and slaveOkay is true, a
     * "secondaryPreferred" read preference will be used. Otherwise, a "primary"
     * read preference will be used.
     *
     * @see \Doctrine\MongoDB\Cursor::setMongoCursorSlaveOkay()
     * @param boolean $bool
     * @return self
     */
    public function slaveOkay($bool = true)
    {
        $this->query['slaveOkay'] = (boolean) $bool;
        return $this;
    }

    /**
     * Set the snapshot cursor flag.
     *
     * @param boolean $bool
     * @return self
     */
    public function snapshot($bool = true)
    {
        $this->query['snapshot'] = (boolean) $bool;
        return $this;
    }

    /**
     * Set one or more field/order pairs on which to sort the query.
     *
     * If sorting by multiple fields, the first argument should be an array of
     * field name (key) and order (value) pairs.
     *
     * @param array|string $fieldName Field name or array of field/order pairs
     * @param int|string $order       Field order (if one field is specified)
     * @return self
     */
    public function sort($fieldName, $order = 1)
    {
        if ( ! isset($this->query['sort'])) {
            $this->query['sort'] = array();
        }

        $fields = is_array($fieldName) ? $fieldName : array($fieldName => $order);

        foreach ($fields as $fieldName => $order) {
            if (is_string($order)) {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }
            $this->query['sort'][$fieldName] = (integer) $order;
        }

        return $this;
    }

    /**
     * Set the "spherical" option for a geoNear command query.
     *
     * @param bool $spherical
     * @return self
     * @throws BadMethodCallException if the query is not a geoNear command
     */
    public function spherical($spherical = true)
    {
        if ($this->query['type'] !== Query::TYPE_GEO_NEAR) {
            throw new BadMethodCallException('This method requires a geoNear command (call geoNear() first)');
        }

        $this->query['geoNear']['options']['spherical'] = $spherical;
        return $this;
    }

    /**
     * Specify $type criteria for the current field.
     *
     * @see Expr::type()
     * @see http://docs.mongodb.org/manual/reference/operator/type/
     * @param integer $type
     * @return self
     */
    public function type($type)
    {
        $this->expr->type($type);
        return $this;
    }

    /**
     * Unset the current field.
     *
     * The field will be removed from the document (not set to null).
     *
     * @see Expr::unsetField()
     * @see http://docs.mongodb.org/manual/reference/operator/unset/
     * @return self
     */
    public function unsetField()
    {
        $this->expr->unsetField();
        return $this;
    }

    /**
     * Change the query type to update.
     *
     * @return self
     */
    public function update()
    {
        $this->query['type'] = Query::TYPE_UPDATE;
        return $this;
    }

    /**
     * Set the "upsert" option for an update or findAndUpdate query.
     *
     * @param boolean $bool
     * @return self
     */
    public function upsert($bool = true)
    {
        $this->query['upsert'] = (boolean) $bool;
        return $this;
    }

    /**
     * Specify a JavaScript expression to use for matching documents.
     *
     * @see Expr::where()
     * @see http://docs.mongodb.org/manual/reference/operator/where/
     * @param string|\MongoCode $javascript
     * @return self
     */
    public function where($javascript)
    {
        $this->expr->where($javascript);
        return $this;
    }

    /**
     * Add $within criteria with a $box shape to the query.
     *
     * @deprecated 1.1 MongoDB 2.4 deprecated $within in favor of $geoWithin
     * @see Builder::geoWithinBox()
     * @see Expr::withinBox()
     * @see http://docs.mongodb.org/manual/reference/operator/box/
     * @param float $x1
     * @param float $y1
     * @param float $x2
     * @param float $y2
     * @return self
     */
    public function withinBox($x1, $y1, $x2, $y2)
    {
        $this->expr->withinBox($x1, $y1, $x2, $y2);
        return $this;
    }

    /**
     * Add $within criteria with a $center shape to the query.
     *
     * @deprecated 1.1 MongoDB 2.4 deprecated $within in favor of $geoWithin
     * @see Builder::geoWithinCenter()
     * @see Expr::withinCenter()
     * @see http://docs.mongodb.org/manual/reference/operator/center/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function withinCenter($x, $y, $radius)
    {
        $this->expr->withinCenter($x, $y, $radius);
        return $this;
    }

    /**
     * Add $within criteria with a $centerSphere shape to the query.
     *
     * @deprecated 1.1 MongoDB 2.4 deprecated $within in favor of $geoWithin
     * @see Builder::geoWithinCenterSphere()
     * @see Expr::withinCenterSphere()
     * @see http://docs.mongodb.org/manual/reference/operator/centerSphere/
     * @param float $x
     * @param float $y
     * @param float $radius
     * @return self
     */
    public function withinCenterSphere($x, $y, $radius)
    {
        $this->expr->withinCenterSphere($x, $y, $radius);
        return $this;
    }

    /**
     * Add $within criteria with a $polygon shape to the query.
     *
     * Point coordinates are in x, y order (easting, northing for projected
     * coordinates, longitude, latitude for geographic coordinates).
     *
     * The last point coordinate is implicitly connected with the first.
     *
     * @deprecated 1.1 MongoDB 2.4 deprecated $within in favor of $geoWithin
     * @see Builder::geoWithinPolygon()
     * @see Expr::withinPolygon()
     * @see http://docs.mongodb.org/manual/reference/operator/polygon/
     * @param array $point,... Three or more point coordinate tuples
     * @return self
     */
    public function withinPolygon(/* array($x1, $y1), array($x2, $y2), ... */)
    {
        call_user_func_array(array($this->expr, 'withinPolygon'), func_get_args());
        return $this;
    }

    /**
     * @see http://php.net/manual/en/language.oop5.cloning.php
     */
    public function __clone()
    {
        $this->expr = clone $this->expr;
    }
}
