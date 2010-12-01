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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\MongoDB\Query;

use Doctrine\MongoDB\Query\Expr;
use Doctrine\MongoDB\Database;
use Doctrine\MongoDB\Collection;

/**
 * Fluent query builder interface.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Builder
{
    const TYPE_FIND            = 1;
    const TYPE_FIND_AND_UPDATE = 2;
    const TYPE_FIND_AND_REMOVE = 3;
    const TYPE_INSERT          = 4;
    const TYPE_UPDATE          = 5;
    const TYPE_REMOVE          = 6;
    const TYPE_GROUP           = 7;
    const TYPE_MAP_REDUCE      = 9;
    const TYPE_DISTINCT_FIELD  = 10;
    const TYPE_GEO_LOCATION    = 11;

    /**
     * The Database instance.
     *
     * @var Database
     */
    protected $database;

    /**
     * The Collection instance.
     *
     * @var Collection
     */
    protected $collection;

    /**
     * The current field we are operating on.
     *
     * @var string
     */
    protected $currentField;

    /**
     * Field to select distinct values of
     *
     * @var string
     */
    protected $distinctField;

    /**
     * Array of fields to select
     *
     * @var array
     */
    protected $select = array();

    /**
     * Array of sort options
     *
     * @var array
     */
    protected $sort = array();

    /**
     * Limit number of records
     *
     * @var integer
     */
    protected $limit = null;

    /**
     * Skip a specified number of records (offset)
     *
     * @var integer
     */
    protected $skip = null;

    /**
     * Group information.
     *
     * @var array
     */
    protected $group = array();

    /**
     * Pass hints to the Cursor
     *
     * @var array
     */
    protected $hints = array();

    /**
     * Pass immortal to cursor
     *
     * @var bool
     */
    protected $immortal = false;

    /**
     * Pass snapshot to cursor
     *
     * @var bool
     */
    protected $snapshot = false;

    /**
     * Pass slaveOkay to cursor
     *
     * @var bool
     */
    protected $slaveOkay = false;

    /**
     * Map reduce information
     *
     * @var array
     */
    protected $mapReduce = array();

    /**
     * Data to use with $near operator for geospatial indexes
     *
     * @var array
     */
    protected $near;

    /**
     * Whether or not to return the new document on findAndUpdate
     *
     * @var boolean
     */
    protected $new = false;

    /**
     * Whether or not to upsert on findAndUpdate.
     *
     * @var boolean
     */
    protected $upsert = false;

    /**
     * The type of query
     *
     * @var integer
     */
    protected $type = self::TYPE_FIND;

    /**
     * Mongo command prefix
     *
     * @var string
     */
    protected $cmd;

    /**
     * Holds a Query\Expr instance used for generating query expressions using the operators.
     *
     * @var Query\Expr $expr
     */
    protected $expr;

    /** Refresh hint */
    const HINT_REFRESH = 1;

    /**
     * Create a new query builder.
     *
     * @param Database $database
     * @param Collection $collection
     */
    public function __construct(Database $database, Collection $collection, $cmd)
    {
        $this->database = $database;
        $this->collection = $collection;
        $this->expr = new Expr($cmd);
        $this->cmd = $cmd;
    }

    /**
     * Get the type of this query.
     *
     * @return string $type
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set slave okaye.
     *
     * @param bool $bool
     * @return Query
     */
    public function slaveOkay($bool = true)
    {
        $this->slaveOkay = $bool;
        return $this;
    }

    /**
     * Set snapshot.
     *
     * @param bool $bool
     * @return Query
     */
    public function snapshot($bool = true)
    {
        $this->snapshot = $bool;
        return $this;
    }

    /**
     * Set immortal.
     *
     * @param bool $bool
     * @return Query
     */
    public function immortal($bool = true)
    {
        $this->immortal = $bool;
        return $this;
    }

    /**
     * Pass a hint to the Cursor
     *
     * @param string $keyPattern
     * @return Query
     */
    public function hint($keyPattern)
    {
        $this->hints[] = $keyPattern;
        return $this;
    }

    /**
     * Change the query type to find and optionally set and change the class being queried.
     *
     * @param string $className The Document class being queried.
     * @return Query
     */
    public function find()
    {
        $this->type = self::TYPE_FIND;
        return $this;
    }

    /**
     * Sets a flag for the query to be executed as a findAndUpdate query query.
     *
     * @return Query
     */
    public function findAndUpdate()
    {
        $this->type = self::TYPE_FIND_AND_UPDATE;
        return $this;
    }

    public function returnNew($bool = true)
    {
        $this->new = $bool;
        return $this;
    }

    public function upsert($bool = true)
    {
        $this->upsert = $bool;
        return $this;
    }

    /**
     * Sets a flag for the query to be executed as a findAndUpdate query query.
     *
     * @return Query
     */
    public function findAndRemove()
    {
        $this->type = self::TYPE_FIND_AND_REMOVE;
        return $this;
    }

    /**
     * Change the query type to update and optionally set and change the class being queried.
     *
     * @return Query
     */
    public function update()
    {
        $this->type = self::TYPE_UPDATE;
        return $this;
    }

    /**
     * Change the query type to insert and optionally set and change the class being queried.
     *
     * @return Query
     */
    public function insert()
    {
        $this->type = self::TYPE_INSERT;
        return $this;
    }

    /**
     * Change the query type to remove and optionally set and change the class being queried.
     *
     * @return Query
     */
    public function remove()
    {
        $this->type = self::TYPE_REMOVE;
        return $this;
    }

    /**
     * Perform an operation similar to SQL's GROUP BY command
     *
     * @param array $keys
     * @param array $initial
     * @param string $reduce
     * @param array $condition
     * @return Query
     */
    public function group($keys, array $initial)
    {
        $this->group = array(
            'keys' => $keys,
            'initial' => $initial
        );
        $this->type = self::TYPE_GROUP;
        return $this;
    }

    /**
     * The distinct method queries for a list of distinct values for the given
     * field for the document being queried for.
     *
     * @param string $field
     * @return Query
     */
    public function distinct($field)
    {
        $this->type = self::TYPE_DISTINCT_FIELD;
        $this->distinctField = $field;
        return $this;
    }

    /**
     * The fields to select.
     *
     * @param string $fieldName
     * @return Query
     */
    public function select($fieldName = null)
    {
        $select = func_get_args();
        foreach ($select as $fieldName) {
            $this->select[] = $fieldName;
        }
        return $this;
    }

    /**
     * Select a slice of an embedded document.
     *
     * @param string $fieldName
     * @param integer $skip
     * @param integer $limit
     * @return Query
     */
    public function selectSlice($fieldName, $skip, $limit = null)
    {
        $slice = array($skip);
        if ($limit !== null) {
            $slice[] = $limit;
        }
        $this->select[$fieldName][$this->cmd . 'slice'] = $slice;
        return $this;
    }

    /**
     * Add where near criteria.
     *
     * @param string $x
     * @param string $y
     * @return Query
     */
    public function near($value)
    {
        $this->type = self::TYPE_GEO_LOCATION;
        $this->near[$this->currentField] = $value;
        return $this;
    }

    /**
     * Set the current field to operate on.
     *
     * @param string $field
     * @return Query
     */
    public function field($field)
    {
        $this->currentField = $field;
        $this->expr->field($field);
        return $this;
    }

    /**
     * Add a new where criteria erasing all old criteria.
     *
     * @param string $value
     * @return Query
     */
    public function equals($value)
    {
        $this->expr->equals($value);
        return $this;
    }

    /**
     * Add $where javascript function to reduce result sets.
     *
     * @param string $javascript
     * @return Query
     */
    public function where($javascript)
    {
        $this->expr->where($javascript);
        return $this;
    }

    /**
     * Add a new where in criteria.
     *
     * @param mixed $values
     * @return Query
     */
    public function in($values)
    {
        $this->expr->in($values);
        return $this;
    }

    /**
     * Add where not in criteria.
     *
     * @param mixed $values
     * @return Query
     */
    public function notIn($values)
    {
        $this->expr->notIn($values);
        return $this;
    }

    /**
     * Add where not equal criteria.
     *
     * @param string $value
     * @return Query
     */
    public function notEqual($value)
    {
        $this->expr->notEqual($value);
        return $this;
    }

    /**
     * Add where greater than criteria.
     *
     * @param string $value
     * @return Query
     */
    public function gt($value)
    {
        $this->expr->gt($value);
        return $this;
    }

    /**
     * Add where greater than or equal to criteria.
     *
     * @param string $value
     * @return Query
     */
    public function gte($value)
    {
        $this->expr->gte($value);
        return $this;
    }

    /**
     * Add where less than criteria.
     *
     * @param string $value
     * @return Query
     */
    public function lt($value)
    {
        $this->expr->lt($value);
        return $this;
    }

    /**
     * Add where less than or equal to criteria.
     *
     * @param string $value
     * @return Query
     */
    public function lte($value)
    {
        $this->expr->lte($value);
        return $this;
    }

    /**
     * Add where range criteria.
     *
     * @param string $start
     * @param string $end
     * @return Query
     */
    public function range($start, $end)
    {
        $this->expr->range($start, $end);
        return $this;
    }

    /**
     * Add where size criteria.
     *
     * @param string $size
     * @return Query
     */
    public function size($size)
    {
        $this->expr->size($size);
        return $this;
    }

    /**
     * Add where exists criteria.
     *
     * @param string $bool
     * @return Query
     */
    public function exists($bool)
    {
        $this->expr->exists($bool);
        return $this;
    }

    /**
     * Add where type criteria.
     *
     * @param string $type
     * @return Query
     */
    public function type($type)
    {
        $this->expr->type($type);
        return $this;
    }

    /**
     * Add where all criteria.
     *
     * @param mixed $values
     * @return Query
     */
    public function all($values)
    {
        $this->expr->all($values);
        return $this;
    }

    /**
     * Add where mod criteria.
     *
     * @param string $mod
     * @return Query
     */
    public function mod($mod)
    {
        $this->expr->mod($mod);
        return $this;
    }

    /**
     * Add where $within $box query.
     *
     * @param string $x1
     * @param string $y1
     * @param string $x2
     * @param string $y2
     * @return Query
     */
    public function withinBox($x1, $y1, $x2, $y2)
    {
        $this->expr->withinBox($x1, $y1, $x2, $y2);
        return $this;
    }

    /**
     * Add where $within $center query.
     *
     * @param string $x
     * @param string $y
     * @param string $radius
     * @return Query
     */
    public function withinCenter($x, $y, $radius)
    {
        $this->expr->withinCenter($x, $y, $radius);
        return $this;
    }

    /**
     * Set sort and erase all old sorts.
     *
     * @param string $order
     * @return Query
     */
    public function sort($fieldName, $order)
    {
        $this->sort[$fieldName] = strtolower($order) === 'asc' ? 1 : -1;
        return $this;
    }

    /**
     * Set the Document limit for the Cursor
     *
     * @param string $limit
     * @return Query
     */
    public function limit($limit)
    {
        $this->limit = $limit;
        return $this;
    }

    /**
     * Set the number of Documents to skip for the Cursor
     *
     * @param string $skip
     * @return Query
     */
    public function skip($skip)
    {
        $this->skip = $skip;
        return $this;
    }

    /**
     * Specify a map reduce operation for this query.
     *
     * @param mixed $map
     * @param mixed $reduce
     * @param array $options
     * @return Query
     */
    public function mapReduce($map, $reduce, array $options = array())
    {
        $this->type = self::TYPE_MAP_REDUCE;
        $this->mapReduce = array(
            'map' => $map,
            'reduce' => $reduce,
            'options' => $options
        );
        return $this;
    }

    /**
     * Specify a map operation for this query.
     *
     * @param string $map
     * @return Query
     */
    public function map($map)
    {
        $this->mapReduce['map'] = $map;
        $this->type = self::TYPE_MAP_REDUCE;
        return $this;
    }

    /**
     * Specify a reduce operation for this query.
     *
     * @param string $reduce
     * @return Query
     */
    public function reduce($reduce)
    {
        $this->mapReduce['reduce'] = $reduce;
        if (isset($this->mapReduce['map']) && isset($this->mapReduce['reduce'])) {
            $this->type = self::TYPE_MAP_REDUCE;
        }
        return $this;
    }

    /**
     * Specify the map reduce array of options for this query.
     *
     * @param array $options
     * @return Query
     */
    public function mapReduceOptions(array $options)
    {
        $this->mapReduce['options'] = $options;
        return $this;
    }

    /**
     * Set field to value.
     *
     * @param mixed $value
     * @param boolean $atomic
     * @return Query
     */
    public function set($value, $atomic = true)
    {
        if ($this->type == self::TYPE_INSERT) {
            $atomic = false;
        }
        $this->expr->set($value, $atomic);
        return $this;
    }

    /**
     * Increment field by the number value if field is present in the document,
     * otherwise sets field to the number value.
     *
     * @param integer $value
     * @return Query
     */
    public function inc($value)
    {
        $this->expr->inc($value);
        return $this;
    }

    /**
     * Deletes a given field.
     *
     * @return Query
     */
    public function unsetField()
    {
        $this->expr->unsetField();
        return $this;
    }

    /**
     * Appends value to field, if field is an existing array, otherwise sets
     * field to the array [value] if field is not present. If field is present
     * but is not an array, an error condition is raised.
     *
     * @param mixed $value
     * @return Query
     */
    public function push($value)
    {
        $this->expr->push($value);
        return $this;
    }

    /**
     * Appends each value in valueArray to field, if field is an existing
     * array, otherwise sets field to the array valueArray if field is not
     * present. If field is present but is not an array, an error condition is
     * raised.
     *
     * @param array $valueArray
     * @return Query
     */
    public function pushAll(array $valueArray)
    {
        $this->expr->pushAll($valueArray);
        return $this;
    }

    /**
     * Adds value to the array only if its not in the array already.
     *
     * @param mixed $value
     * @return Query
     */
    public function addToSet($value)
    {
        $this->expr->addToSet($value);
        return $this;
    }

    /**
     * Adds values to the array only they are not in the array already.
     *
     * @param array $values
     * @return Query
     */
    public function addManyToSet(array $values)
    {
        $this->expr->addManyToSet($values);
        return $this;
    }

    /**
     * Removes first element in an array
     *
     * @return Query
     */
    public function popFirst()
    {
        $this->expr->popFirst();
        return $this;
    }

    /**
     * Removes last element in an array
     *
     * @return Query
     */
    public function popLast()
    {
        $this->expr->popLast();
        return $this;
    }

    /**
     * Removes all occurrences of value from field, if field is an array.
     * If field is present but is not an array, an error condition is raised.
     *
     * @param mixed $value
     * @return Query
     */
    public function pull($value)
    {
        $this->expr->pull($value);
        return $this;
    }

    /**
     * Removes all occurrences of each value in value_array from field, if
     * field is an array. If field is present but is not an array, an error
     * condition is raised.
     *
     * @param array $valueArray
     * @return Query
     */
    public function pullAll(array $valueArray)
    {
        $this->expr->pullAll($valueArray);
        return $this;
    }

    /**
     * Adds an "or" expression to the current query.
     *
     * You can create the expression using the expr() method:
     *
     *     $qb = $this->createQueryBuilder('User');
     *     $qb
     *         ->addOr($qb->expr()->field('first_name')->equals('Kris'))
     *         ->addOr($qb->expr()->field('first_name')->equals('Chris'));
     *
     * @param array|QueryBuilder $expression
     * @return Query
     */
    public function addOr($expression)
    {
        $this->expr->addOr($expression);
        return $this;
    }

    /**
     * Adds an "elemMatch" expression to the current query.
     *
     * You can create the expression using the expr() method:
     *
     *     $qb = $this->createQueryBuilder('User');
     *     $qb
     *         ->field('phonenumbers')
     *         ->elemMatch($qb->expr()->field('phonenumber')->equals('6155139185'));
     *
     * @param array|QueryBuilder $expression
     * @return Query
     */
    public function elemMatch($expression)
    {
        $this->expr->elemMatch($expression);
        return $this;
    }

    /**
     * Adds a "not" expression to the current query.
     *
     * You can create the expression using the expr() method:
     *
     *     $qb = $this->createQueryBuilder('User');
     *     $qb->field('id')->not($qb->expr()->in(1));
     *
     * @param array|QueryBuilder $expression
     * @return Query
     */
    public function not($expression)
    {
        $this->expr->not($expression);
        return $this;
    }

    /**
     * Create a new Query\Expr instance that can be used as an expression with the QueryBuilder
     *
     * @return Query\Expr $expr
     */
    public function expr()
    {
        return new Expr($this->cmd);
    }

    public function getQueryArray()
    {
        return $this->expr->getQuery();
    }

    public function setQueryArray(array $query)
    {
        $this->expr->setQuery($query);
        return $this;
    }

    public function getNewObj()
    {
        return $this->expr->getNewObj();
    }

    public function setNewObj(array $newObj)
    {
        $this->expr->setNewObj($newObj);
        return $this;
    }

    /**
     * Gets the Query executable.
     *
     * @param array $options
     * @return QueryInterface $query
     */
    public function getQuery()
    {
        switch ($this->type) {
            case self::TYPE_GEO_LOCATION;
                $query = new GeoLocationFindQuery($this->database, $this->collection, $this->cmd);
                $query->setQuery($this->expr->getQuery());
                $query->setNear($this->near);
                $query->setLimit($this->limit);
                return $query;
            case self::TYPE_DISTINCT_FIELD;
                $query = new DistinctFieldQuery($this->database, $this->collection, $this->cmd);
                $query->setDistinctField($this->distinctField);
                $query->setQuery($this->expr->getQuery());
                return $query;
            case self::TYPE_MAP_REDUCE;
                $query = new MapReduceQuery($this->database, $this->collection, $this->cmd);
                $query->setQuery($this->expr->getQuery());
                $query->setMap(isset($this->mapReduce['map']) ? $this->mapReduce['map'] : null);
                $query->setReduce(isset($this->mapReduce['reduce']) ? $this->mapReduce['reduce'] : null);
                $query->setOptions(isset($this->mapReduce['options']) ? $this->mapReduce['options'] : array());
                $query->setSelect($this->select);
                $query->setQuery($this->expr->getQuery());
                $query->setLimit($this->limit);
                $query->setSkip($this->skip);
                $query->setSort($this->sort);
                $query->setImmortal($this->immortal);
                $query->setSlaveOkay($this->slaveOkay);
                $query->setSnapshot($this->snapshot);
                $query->setHints($this->hints);
                return $query;
            case self::TYPE_FIND;
                $query = new FindQuery($this->database, $this->collection, $this->cmd);
                $query->setReduce(isset($this->mapReduce['reduce']) ? $this->mapReduce['reduce'] : null);
                $query->setSelect($this->select);
                $query->setQuery($this->expr->getQuery());
                $query->setLimit($this->limit);
                $query->setSkip($this->skip);
                $query->setSort($this->sort);
                $query->setImmortal($this->immortal);
                $query->setSlaveOkay($this->slaveOkay);
                $query->setSnapshot($this->snapshot);
                $query->setHints($this->hints);
                return $query;
            case self::TYPE_FIND_AND_REMOVE;
                $query = new FindAndRemoveQuery($this->database, $this->collection, $this->cmd);
                $query->setSelect($this->select);
                $query->setQuery($this->expr->getQuery());
                $query->setSort($this->sort);
                $query->setLimit($this->limit);
                return $query;
            case self::TYPE_FIND_AND_UPDATE;
                $query = new FindAndUpdateQuery($this->database, $this->collection, $this->cmd);
                $query->setSelect($this->select);
                $query->setQuery($this->expr->getQuery());
                $query->setNewObj($this->expr->getNewObj());
                $query->setSort($this->sort);
                $query->setUpsert($this->upsert);
                $query->setNew($this->new);
                $query->setLimit($this->limit);
                return $query;
            case self::TYPE_REMOVE;
                $query = new RemoveQuery($this->database, $this->collection, $this->cmd);
                $query->setQuery($this->expr->getQuery());
                return $query;
            case self::TYPE_UPDATE;
                $query = new UpdateQuery($this->database, $this->collection, $this->cmd);
                $query->setQuery($this->expr->getQuery());
                $query->setNewObj($this->expr->getNewObj());
                return $query;
            case self::TYPE_INSERT;
                $query = new InsertQuery($this->database, $this->collection, $this->cmd);
                $query->setNewObj($this->expr->getNewObj());
                return $query;
            case self::TYPE_GROUP;
                $query = new GroupQuery($this->database, $this->collection, $this->cmd);
                $query->setKeys(isset($this->group['keys']) ? $this->group['keys'] : null);
                $query->setInitial(isset($this->group['initial']) ? $this->group['initial'] : array());
                $query->setReduce(isset($this->mapReduce['reduce']) ? $this->mapReduce['reduce'] : null);
                $query->setQuery($this->expr->getQuery());
                return $query;
        }
    }

    /**
     * Gets an array of information about this query builder for debugging.
     *
     * @param string $name
     * @return array $debug
     */
    public function debug($name = null)
    {
        $debug = get_object_vars($this);

        unset($debug['database'], $debug['collection'], $debug['expr']);
        if ($name !== null) {
            return $debug[$name];
        }
        foreach ($debug as $key => $value) {
            if ( ! $value) {
                unset($debug[$key]);
            }
        }
        return $debug;
    }
}