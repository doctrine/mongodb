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
     * Array containing the query data.
     *
     * @var array
     */
    protected $query = array(
        'type' => Query::TYPE_FIND,
        'distinctField' => null,
        'select' => array(),
        'sort' => array(),
        'limit' => null,
        'skip' => null,
        'group' => array(),
        'hints' => array(),
        'immortal' => false,
        'snapshot' => false,
        'slaveOkay' => false,
        'mapReduce' => array(
            'map' => null,
            'reduce' => null,
            'options' => array()
        ),
        'near' => array(),
        'new' => false,
        'upsert' => false,
    );

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
        return $this->query['type'];
    }

    /**
     * Set slave okaye.
     *
     * @param bool $bool
     * @return Builder
     */
    public function slaveOkay($bool = true)
    {
        $this->query['slaveOkay'] = $bool;
        return $this;
    }

    /**
     * Set snapshot.
     *
     * @param bool $bool
     * @return Builder
     */
    public function snapshot($bool = true)
    {
        $this->query['snapshot'] = $bool;
        return $this;
    }

    /**
     * Set immortal.
     *
     * @param bool $bool
     * @return Builder
     */
    public function immortal($bool = true)
    {
        $this->query['immortal'] = $bool;
        return $this;
    }

    /**
     * Pass a hint to the Cursor
     *
     * @param string $keyPattern
     * @return Builder
     */
    public function hint($keyPattern)
    {
        $this->query['hints'][] = $keyPattern;
        return $this;
    }

    /**
     * Change the query type to find and optionally set and change the class being queried.
     *
     * @param string $className The Document class being queried.
     * @return Builder
     */
    public function find()
    {
        $this->query['type'] = Query::TYPE_FIND;
        return $this;
    }
    
    public function count()
    {
        $this->query['type'] = Query::TYPE_COUNT;
        return $this;
    }

    /**
     * Sets a flag for the query to be executed as a findAndUpdate query query.
     *
     * @return Builder
     */
    public function findAndUpdate()
    {
        $this->query['type'] = Query::TYPE_FIND_AND_UPDATE;
        return $this;
    }

    public function returnNew($bool = true)
    {
        $this->query['new'] = $bool;
        return $this;
    }

    public function upsert($bool = true)
    {
        $this->query['upsert'] = $bool;
        return $this;
    }

    /**
     * Sets a flag for the query to be executed as a findAndUpdate query query.
     *
     * @return Builder
     */
    public function findAndRemove()
    {
        $this->query['type'] = Query::TYPE_FIND_AND_REMOVE;
        return $this;
    }

    /**
     * Change the query type to update and optionally set and change the class being queried.
     *
     * @return Builder
     */
    public function update()
    {
        $this->query['type'] = Query::TYPE_UPDATE;
        return $this;
    }

    /**
     * Change the query type to insert and optionally set and change the class being queried.
     *
     * @return Builder
     */
    public function insert()
    {
        $this->query['type'] = Query::TYPE_INSERT;
        return $this;
    }

    /**
     * Change the query type to remove and optionally set and change the class being queried.
     *
     * @return Builder
     */
    public function remove()
    {
        $this->query['type'] = Query::TYPE_REMOVE;
        return $this;
    }

    /**
     * Perform an operation similar to SQL's GROUP BY command
     *
     * @param $keys
     * @param array $initial
     * @return Builder
     */
    public function group($keys, array $initial)
    {
        $this->query['group'] = array(
            'keys' => $keys,
            'initial' => $initial
        );
        $this->query['type'] = Query::TYPE_GROUP;
        return $this;
    }

    /**
     * The distinct method queries for a list of distinct values for the given
     * field for the document being queried for.
     *
     * @param string $field
     * @return Builder
     */
    public function distinct($field)
    {
        $this->query['type'] = Query::TYPE_DISTINCT_FIELD;
        $this->query['distinctField'] = $field;
        return $this;
    }

    /**
     * The fields to select.
     *
     * @param string $fieldName
     * @return Builder
     */
    public function select($fieldName = null)
    {
        $select = func_get_args();
        foreach ($select as $fieldName) {
            $this->query['select'][$fieldName] = 1;
        }
        return $this;
    }

    /**
     * The fields not to select.
     *
     * @param string $fieldName
     * @return Builder
     */
    public function exclude($fieldName = null)
    {
        $select = func_get_args();
        foreach ($select as $fieldName) {
            $this->query['select'][$fieldName] = 0;
        }
        return $this;
    }

    /**
     * Select a slice of an embedded document.
     *
     * @param string $fieldName
     * @param integer $skip
     * @param integer $limit
     * @return Builder
     */
    public function selectSlice($fieldName, $skip, $limit = null)
    {
        $slice = array($skip);
        if ($limit !== null) {
            $slice[] = $limit;
        }
        $this->query['select'][$fieldName][$this->cmd . 'slice'] = $slice;
        return $this;
    }

    /**
     * Add where near criteria.
     *
     * @param string $x
     * @param string $y
     * @return Builder
     */
    public function near($value)
    {
        $this->query['type'] = Query::TYPE_GEO_LOCATION;
        $this->query['near'][$this->currentField] = $value;
        return $this;
    }

    /**
     * Set the current field to operate on.
     *
     * @param string $field
     * @return Builder
     */
    public function field($field)
    {
        $this->currentField = $field;
        $this->expr->field($field);
        return $this;
    }

    /**
     * @param $value
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
     */
    public function withinCenter($x, $y, $radius)
    {
        $this->expr->withinCenter($x, $y, $radius);
        return $this;
    }


    /**
     * Set sort and erase all old sorts.
     *
     * @param string $fieldName
     * @param string $order
     * @return Builder
     */
    public function sort($fieldName, $order = null)
    {
        if (is_array($fieldName)) {
            foreach ($fieldName as $fieldName => $order) {
                $this->sort($fieldName, $order);
            }
        } else {
            if (is_string($order)) {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }
            $order = (int) $order;
            $this->query['sort'][$fieldName] = $order;
        }
        return $this;
    }

    /**
     * Set the Document limit for the Cursor
     *
     * @param string $limit
     * @return Builder
     */
    public function limit($limit)
    {
        $this->query['limit'] = $limit;
        return $this;
    }

    /**
     * Set the number of Documents to skip for the Cursor
     *
     * @param string $skip
     * @return Builder
     */
    public function skip($skip)
    {
        $this->query['skip'] = $skip;
        return $this;
    }

    /**
     * Specify a map reduce operation for this query.
     *
     * @param string $map
     * @param string $reduce
     * @param array $out
     * @param array $options
     * @return Builder
     */
    public function mapReduce($map, $reduce, array $out = array('inline' => true), array $options = array())
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
     * Specify a map operation for this query.
     *
     * @param string $map
     * @return Builder
     */
    public function map($map)
    {
        $this->query['mapReduce']['map'] = $map;
        $this->query['type'] = Query::TYPE_MAP_REDUCE;
        return $this;
    }

    /**
     * Specify a reduce operation for this query.
     *
     * @param string $reduce
     * @return Builder
     */
    public function reduce($reduce)
    {
        $this->query['mapReduce']['reduce'] = $reduce;
        if (isset($this->query['mapReduce']['map']) && isset($this->query['mapReduce']['reduce'])) {
            $this->query['type'] = Query::TYPE_MAP_REDUCE;
        }
        return $this;
    }

    /**
     * Specify output type for mar/reduce operation.
     *
     * @param array $out
     * @return Builder
     */
    public function out(array $out)
    {
        $this->query['mapReduce']['out'] = $out;
        $this->query['type'] = Query::TYPE_MAP_REDUCE;

        return $this;
    }

    /**
     * Specify the map reduce array of options for this query.
     *
     * @param array $options
     * @return Builder
     */
    public function mapReduceOptions(array $options)
    {
        $this->query['mapReduce']['options'] = $options;
        return $this;
    }

    /**
     * Set field to value.
     *
     * @param mixed $value
     * @param boolean $atomic
     * @return Builder
     */
    public function set($value, $atomic = true)
    {
        if ($this->query['type'] == Query::TYPE_INSERT) {
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
     * @return Builder
     */
    public function inc($value)
    {
        $this->expr->inc($value);
        return $this;
    }

    /**
     * Deletes a given field.
     *
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
     */
    public function addManyToSet(array $values)
    {
        $this->expr->addManyToSet($values);
        return $this;
    }

    /**
     * Removes first element in an array
     *
     * @return Builder
     */
    public function popFirst()
    {
        $this->expr->popFirst();
        return $this;
    }

    /**
     * Removes last element in an array
     *
     * @return Builder
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
     * @return Builder
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
     * @return Builder
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
     * @return Builder
     */
    public function addOr($expression)
    {
        $this->expr->addOr($expression);
        return $this;
    }

    /**
     * Adds an "and" expression to the current query.
     *
     * You can create the expression using the expr() method:
     *
     *     $qb = $this->createQueryBuilder('User');
     *     $qb
     *         ->addAnd($qb->expr()->field('first_name')->equals('Kris'))
     *         ->addAnd($qb->expr()->field('first_name')->equals('Chris'));
     *
     * @param array|QueryBuilder $expression
     * @return Query
     */
    public function addAnd($expression)
    {
        $this->expr->addAnd($expression);
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
     * @return Builder
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
     * @return Builder
     */
    public function not($expression)
    {
        $this->expr->not($expression);
        return $this;
    }

    /**
     * Create a new Query\Expr instance that can be used as an expression with the QueryBuilder
     *
     * @return Expr $expr
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
     * @return Query $query
     */
    public function getQuery(array $options = array())
    {
        $query = $this->query;
        $query['query'] = $this->expr->getQuery();
        $query['newObj'] = $this->expr->getNewObj();
        return new Query($this->database, $this->collection, $query, $options, $this->cmd);
    }

    /**
     * Gets an array of information about this query builder for debugging.
     *
     * @param string $name
     * @return array $debug
     */
    public function debug($name = null)
    {
        $debug = $this->query;
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
