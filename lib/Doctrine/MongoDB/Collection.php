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

namespace Doctrine\MongoDB;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Event\AggregateEventArgs;
use Doctrine\MongoDB\Event\DistinctEventArgs;
use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Event\FindEventArgs;
use Doctrine\MongoDB\Event\GroupEventArgs;
use Doctrine\MongoDB\Event\MapReduceEventArgs;
use Doctrine\MongoDB\Event\MutableEventArgs;
use Doctrine\MongoDB\Event\NearEventArgs;
use Doctrine\MongoDB\Event\UpdateEventArgs;
use Doctrine\MongoDB\Util\ReadPreference;

/**
 * Wrapper for the PHP MongoCollection class.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class Collection
{
    /**
     * The Connection instance used to create Cursors.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The collection name.
     *
     * @var string $name
     */
    protected $name;

    /**
     * The Database instance to which this collection belongs.
     *
     * @var Database
     */
    protected $database;

    /**
     * The EventManager used to dispatch events.
     *
     * @var \Doctrine\Common\EventManager
     */
    protected $eventManager;

    /**
     * MongoDB command prefix.
     *
     * @var string
     */
    protected $cmd;

    /**
     * Number of times to retry queries.
     *
     * @var integer
     */
    protected $numRetries;

    /**
     * Constructor.
     *
     * @param Connection      $connection Connection used to create Cursors
     * @param string          $name       The collection name
     * @param Database        $database   Database to which this collection belongs
     * @param EventManager    $evm        EventManager instance
     * @param string          $cmd        MongoDB command prefix
     * @param boolean|integer $numRetries Number of times to retry queries
     */
    public function __construct(Connection $connection, $name, Database $database, EventManager $evm, $cmd, $numRetries = 0)
    {
        $this->connection = $connection;
        $this->name = $name;
        $this->database = $database;
        $this->eventManager = $evm;
        $this->cmd = $cmd;
        $this->numRetries = (integer) $numRetries;
    }

    /**
     * Return the name of this collection.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Return a new MongoCollection instance for this collection.
     *
     * @return \MongoCollection
     */
    public function getMongoCollection()
    {
        return $this->database->getMongoDB()->selectCollection($this->name);
    }

    /**
     * Gets the database for this collection.
     *
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    /**
     * Wrapper method for MongoCollection::getIndexInfo().
     *
     * @see http://php.net/manual/en/mongocollection.getindexinfo.php
     * @return array
     */
    public function getIndexInfo()
    {
        return $this->getMongoCollection()->getIndexInfo();
    }

    /**
     * Check if a given field name is indexed in MongoDB.
     *
     * @param string $fieldName
     * @return boolean
     */
    public function isFieldIndexed($fieldName)
    {
        $indexes = $this->getIndexInfo();
        foreach ($indexes as $index) {
            if (isset($index['key']) && isset($index['key'][$fieldName])) {
                return true;
            }
        }
        return false;
    }

    /**
     * Creates a new query builder instance.
     *
     * @return \Doctrine\MongoDB\Query\Builder
     */
    public function createQueryBuilder()
    {
        return new Query\Builder($this->database, $this, $this->cmd);
    }

    /**
     * Invokes the aggregate command.
     *
     * This method will dispatch preAggregate and postAggregate events.
     *
     * @see http://php.net/manual/en/mongocollection.aggregate.php
     * @see http://docs.mongodb.org/manual/reference/command/aggregate/
     * @param array $pipeline Array of pipeline operators, or the first operator
     * @param array $op,...   Additional operators (if $pipeline was the first)
     * @return ArrayIterator
     */
    public function aggregate(array $pipeline /* , array $op, ... */)
    {
        /* If the single array argument contains a zeroth index, consider it an
         * array of pipeline operators. Otherwise, assume that each argument is
         * a pipeline operator.
         */
        if ( ! array_key_exists(0, $pipeline)) {
            $pipeline = func_get_args();
        }

        if ($this->eventManager->hasListeners(Events::preAggregate)) {
            $this->eventManager->dispatchEvent(Events::preAggregate, new AggregateEventArgs($this, $pipeline));
        }

        $result = $this->doAggregate($pipeline);

        if ($this->eventManager->hasListeners(Events::postAggregate)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postAggregate, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the aggregate command.
     *
     * @see Collection::aggregate()
     * @param array $pipeline
     * @return ArrayIterator
     * @throws \RuntimeException if the command fails
     */
    protected function doAggregate(array $pipeline)
    {
        $command = array();
        $command['aggregate'] = $this->getMongoCollection()->getName();
        $command['pipeline'] = $pipeline;

        $database = $this->database;
        $result = $this->retry(function() use ($database, $command) {
            return $database->command($command);
        });

        if ( ! $result['ok']) {
            throw new \RuntimeException($result['errmsg']);
        }

        return new ArrayIterator(isset($result['result']) ? $result['result'] : array());
    }

    /**
     * Wrapper method for MongoCollection::batchInsert().
     *
     * This method will dispatch preBatchInsert and postBatchInsert events.
     *
     * @see http://php.net/manual/en/mongocollection.batchinsert.php
     * @param array $a       Array of documents (arrays/objects) to insert
     * @param array $options
     * @return array|boolean
     */
    public function batchInsert(array &$a, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preBatchInsert)) {
            $this->eventManager->dispatchEvent(Events::preBatchInsert, new EventArgs($this, $a, $options));
        }

        $result = $this->doBatchInsert($a, $options);

        if ($this->eventManager->hasListeners(Events::postBatchInsert)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postBatchInsert, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the batchInsert query.
     *
     * @see Collection::batchInsert()
     * @param array $a
     * @param array $options
     * @return array|boolean
     */
    protected function doBatchInsert(array &$a, array $options = array())
    {
        return $this->getMongoCollection()->batchInsert($a, $options);
    }

    /**
     * Wrapper method for MongoCollection::update().
     *
     * This method will dispatch preUpdate and postUpdate events.
     *
     * @see http://php.net/manual/en/mongocollection.update.php
     * @param array $query
     * @param array $newObj
     * @param array $options
     * @return array|boolean
     */
    public function update($query, array $newObj, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preUpdate)) {
            $this->eventManager->dispatchEvent(Events::preUpdate, new UpdateEventArgs($this, $query, $newObj, $options));
        }

        $result = $this->doUpdate($query, $newObj, $options);

        if ($this->eventManager->hasListeners(Events::postUpdate)) {
            $this->eventManager->dispatchEvent(Events::postUpdate, new EventArgs($this, $result));
        }

        return $result;
    }

    /**
     * Execute the update query.
     *
     * @see Collection::update()
     * @param array $query
     * @param array $newObj
     * @param array $options
     * @return array|boolean
     */
    protected function doUpdate($query, array $newObj, array $options)
    {
        if (is_scalar($query)) {
            $query = array('_id' => $query);
        }
        return $this->getMongoCollection()->update($query, $newObj, $options);
    }

    /**
     * Invokes {@link Collection::update()} with the upsert option.
     *
     * This method will dispatch preUpdate and postUpdate events.
     *
     * @see Collection::update()
     * @see http://php.net/manual/en/mongocollection.update.php
     * @param array $query
     * @param array $newObj
     * @param array $options
     * @return array|boolean
     */
    public function upsert($query, array $newObj, array $options = array())
    {
        $options['upsert'] = true;
        return $this->update($query, $newObj, $options);
    }

    /**
     * Wrapper method for MongoCollection::find().
     *
     * This method will dispatch preFind and postFind events.
     *
     * @see http://php.net/manual/en/mongocollection.find.php
     * @param array $query
     * @param array $fields
     * @return Cursor
     */
    public function find(array $query = array(), array $fields = array())
    {
        if ($this->eventManager->hasListeners(Events::preFind)) {
            $this->eventManager->dispatchEvent(Events::preFind, new FindEventArgs($this, $query, $fields));
        }

        $result = $this->doFind($query, $fields);

        if ($this->eventManager->hasListeners(Events::postFind)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postFind, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the find query.
     *
     * @see Collection::find()
     * @param array $query
     * @param array $fields
     * @return Cursor
     */
    protected function doFind(array $query, array $fields)
    {
        $collection = $this;
        $cursor = $this->retry(function() use ($collection, $query, $fields) {
            return $collection->getMongoCollection()->find($query, $fields);
        });
        return $this->wrapCursor($cursor, $query, $fields);
    }

    /**
     * Wraps a MongoCursor instance with a Cursor.
     *
     * @param \MongoCursor $cursor
     * @param array        $query
     * @param array        $fields
     * @return Cursor
     */
    protected function wrapCursor(\MongoCursor $cursor, $query, $fields)
    {
        return new Cursor($this->connection, $this, $cursor, $query, $fields, $this->numRetries);
    }

    /**
     * Wrapper method for MongoCollection::findOne().
     *
     * This method will dispatch preFindOne and postFindOne events.
     *
     * @see http://php.net/manual/en/mongocollection.findone.php
     * @param array $query
     * @param array $fields
     * @return array|null
     */
    public function findOne(array $query = array(), array $fields = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindOne)) {
            $this->eventManager->dispatchEvent(Events::preFindOne, new FindEventArgs($this, $query, $fields));
        }

        $result = $this->doFindOne($query, $fields);

        if ($this->eventManager->hasListeners(Events::postFindOne)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postFindOne, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the findOne query.
     *
     * @see Collection::findOne()
     * @param array $query
     * @param array $fields
     * @return array|null
     */
    protected function doFindOne(array $query, array $fields)
    {
        $collection = $this;
        return $this->retry(function() use ($collection, $query, $fields) {
            return $collection->getMongoCollection()->findOne($query, $fields);
        });
    }

    /**
     * Invokes the findAndModify command with the remove option.
     *
     * This method will dispatch preFindAndRemove and postFindAndRemove events.
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @param array $query
     * @param array $options
     * @return array|null
     */
    public function findAndRemove(array $query, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindAndRemove)) {
            $this->eventManager->dispatchEvent(Events::preFindAndRemove, new EventArgs($this, $query, $options));
        }

        $result = $this->doFindAndRemove($query, $options);

        if ($this->eventManager->hasListeners(Events::postFindAndRemove)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postFindAndRemove, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the findAndModify command with the remove option.
     *
     * @see Collection::findAndRemove()
     * @param array $query
     * @param array $options
     * @return array|null
     */
    protected function doFindAndRemove(array $query, array $options = array())
    {
        $command = array();
        $command['findandmodify'] = $this->getMongoCollection()->getName();
        $command['query'] = $query;
        $command['remove'] = true;
        $command = array_merge($command, $options);

        $result = $this->database->command($command);

        return isset($result['value']) ? $result['value'] : null;
    }

    /**
     * Invokes the findAndModify command with the update option.
     *
     * This method will dispatch preFindAndUpdate and postFindAndUpdate events.
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @param array $query
     * @param array $newObj
     * @param array $options
     * @return array|null
     */
    public function findAndUpdate(array $query, array $newObj, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindAndUpdate)) {
            $this->eventManager->dispatchEvent(Events::preFindAndUpdate, new UpdateEventArgs($this, $query, $newObj, $options));
        }

        $result = $this->doFindAndUpdate($query, $newObj, $options);

        if ($this->eventManager->hasListeners(Events::postFindAndUpdate)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postFindAndUpdate, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the findAndModify command with the update option.
     *
     * @see Collection::findAndUpdate()
     * @param array $query
     * @param array $newObj
     * @param array $options
     * @return array|null
     */
    protected function doFindAndUpdate(array $query, array $newObj, array $options)
    {
        $command = array();
        $command['findandmodify'] = $this->getMongoCollection()->getName();
        $command['query'] = $query;
        $command['update'] = $newObj;
        $command = array_merge($command, $options);

        $result = $this->database->command($command);
        return isset($result['value']) ? $result['value'] : null;
    }

    /**
     * Invokes the geoNear command.
     *
     * This method will dispatch preNear and postNear events.
     *
     * @see http://docs.mongodb.org/manual/reference/command/geoNear/
     * @param array $near
     * @param array $query
     * @param array $options
     * @return ArrayIterator
     */
    public function near(array $near, array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preNear)) {
            $this->eventManager->dispatchEvent(Events::preNear, new NearEventArgs($this, $query, $near, $options));
        }

        $result = $this->doNear($near, $query, $options);

        if ($this->eventManager->hasListeners(Events::postNear)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postNear, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the geoNear command.
     *
     * @see Collection::near()
     * @param array $near
     * @param array $query
     * @param array $options
     * @return ArrayIterator
     */
    protected function doNear(array $near, array $query, array $options)
    {
        $command = array();
        $command['geoNear'] = $this->getMongoCollection()->getName();
        $command['near'] = $near;
        $command['query'] = $query;
        $command = array_merge($command, $options);

        $database = $this->database;
        $result = $this->retry(function() use ($database, $command) {
            return $database->command($command);
        });
        return new ArrayIterator(isset($result['results']) ? $result['results'] : array());
    }

    /**
     * Invokes the distinct command.
     *
     * This method will dispatch preDistinct and postDistinct events.
     *
     * @see http://php.net/manual/en/mongocollection.distinct.php
     * @see http://docs.mongodb.org/manual/reference/command/distinct/
     * @param array $field
     * @param array $query
     * @param array $options
     * @return ArrayIterator
     */
    public function distinct($field, array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preDistinct)) {
            /* The distinct command currently does not have options beyond field
             * and query, so do not include it in the event args.
             */
            $this->eventManager->dispatchEvent(Events::preDistinct, new DistinctEventArgs($this, $field, $query));
        }

        $result = $this->doDistinct($field, $query, $options);

        if ($this->eventManager->hasListeners(Events::postDistinct)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postDistinct, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the distinct command.
     *
     * @see Collection::distinct()
     * @param array $field
     * @param array $query
     * @param array $options
     * @return ArrayIterator
     */
    protected function doDistinct($field, array $query, array $options)
    {
        $command = array();
        $command['distinct'] = $this->getMongoCollection()->getName();
        $command['key'] = $field;
        $command['query'] = $query;
        $command = array_merge($command, $options);

        $database = $this->database;
        $result = $this->retry(function() use ($database, $command) {
            return $database->command($command);
        });
        return new ArrayIterator(isset($result['values']) ? $result['values'] : array());
    }

    /**
     * Invokes the mapReduce command.
     *
     * This method will dispatch preMapReduce and postMapReduce events.
     *
     * @see http://docs.mongodb.org/manual/reference/command/mapReduce/
     * @param string|\MongoCode $map
     * @param string|\MongoCode $reduce
     * @param array             $out
     * @param array             $query
     * @param array             $options
     * @return ArrayIterator
     */
    public function mapReduce($map, $reduce, array $out = array('inline' => true), array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preMapReduce)) {
            $this->eventManager->dispatchEvent(Events::preMapReduce, new MapReduceEventArgs($this, $map, $reduce, $out, $query, $options));
        }

        $result = $this->doMapReduce($map, $reduce, $out, $query, $options);

        if ($this->eventManager->hasListeners(Events::postMapReduce)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postMapReduce, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the mapReduce command.
     *
     * @see Collection::mapReduce()
     * @param string|\MongoCode $map
     * @param string|\MongoCode $reduce
     * @param array             $out
     * @param array             $query
     * @param array             $options
     * @return ArrayIterator
     */
    protected function doMapReduce($map, $reduce, array $out, array $query, array $options)
    {
        if (is_string($map)) {
            $map = new \MongoCode($map);
        }
        if (is_string($reduce)) {
            $reduce = new \MongoCode($reduce);
        }
        $command = array();
        $command['mapreduce'] = $this->getMongoCollection()->getName();
        $command['map'] = $map;
        $command['reduce'] = $reduce;
        $command['query'] = (object) $query;
        $command['out'] = $out;
        $command = array_merge($command, $options);

        $result = $this->database->command($command);

        if (!$result['ok']) {
            throw new \RuntimeException($result['errmsg']);
        }

        if (isset($out['inline']) && $out['inline'] === true) {
            return new ArrayIterator($result['results']);
        }

        return $this->database->selectCollection($result['result'])->find();
    }

    /**
     * Invokes the count command.
     *
     * @see http://php.net/manual/en/mongocollection.count.php
     * @see http://docs.mongodb.org/manual/reference/command/count/
     * @param array   $query
     * @param integer $limit
     * @param integer $skip
     * @return ArrayIterator
     */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        $collection = $this;
        return $this->retry(function() use ($collection, $query, $limit, $skip) {
            return $collection->getMongoCollection()->count($query, $limit, $skip);
        });
    }

    /**
     * Wrapper method for MongoCollection::createDBRef().
     *
     * @see http://php.net/manual/en/mongocollection.createdbref.php
     * @param array $a
     * @return array
     */
    public function createDBRef(array $a)
    {
        if ($this->eventManager->hasListeners(Events::preCreateDBRef)) {
            $this->eventManager->dispatchEvent(Events::preCreateDBRef, new EventArgs($this, $a));
        }

        $result = $this->doCreateDBRef($a);

        if ($this->eventManager->hasListeners(Events::postCreateDBRef)) {
            $this->eventManager->dispatchEvent(Events::postCreateDBRef, new EventArgs($this, $result));
        }

        return $result;
    }

    /**
     * Creates a database reference.
     *
     * @see Collection::createDBRef()
     * @param array $a
     * @return array
     */
    protected function doCreateDBRef(array $a)
    {
        return $this->getMongoCollection()->createDBRef($a);
    }

    /**
     * Wrapper method for MongoCollection::deleteIndex().
     *
     * @see http://php.net/manual/en/mongocollection.deleteindex.php
     * @param array|string $keys
     * @return array
     */
    public function deleteIndex($keys)
    {
        return $this->getMongoCollection()->deleteIndex($keys);
    }

    /**
     * Wrapper method for MongoCollection::deleteIndexes().
     *
     * @see http://php.net/manual/en/mongocollection.deleteindexes.php
     * @return array
     */
    public function deleteIndexes()
    {
        return $this->getMongoCollection()->deleteIndexes();
    }

    /**
     * Wrapper method for MongoCollection::drop().
     *
     * This method will dispatch preDropCollection and postDropCollection
     * events.
     *
     * @see http://php.net/manual/en/mongocollection.drop.php
     * @return array
     */
    public function drop()
    {
        if ($this->eventManager->hasListeners(Events::preDropCollection)) {
            $this->eventManager->dispatchEvent(Events::preDropCollection, new EventArgs($this));
        }

        $result = $this->doDrop();

        if ($this->eventManager->hasListeners(Events::postDropCollection)) {
            $this->eventManager->dispatchEvent(Events::postDropCollection, new EventArgs($this, $result));
        }

        return $result;
    }

    /**
     * Drops the collection.
     *
     * @see Collection::drop()
     * @return array
     */
    protected function doDrop()
    {
        return $this->getMongoCollection()->drop();
    }

    /**
     * Wrapper method for MongoCollection::ensureIndex().
     *
     * @see http://php.net/manual/en/mongocollection.ensureindex.php
     * @param array $keys
     * @param array $options
     * @return array|boolean
     */
    public function ensureIndex(array $keys, array $options = array())
    {
        return $this->getMongoCollection()->ensureIndex($keys, $options);
    }

    /**
     * Wrapper method for MongoCollection::__get().
     *
     * @see http://php.net/manual/en/mongocollection.get.php
     * @param string $name
     * @return \MongoCollection
     */
    public function __get($name)
    {
        return $this->getMongoCollection()->__get($name);
    }

    /**
     * Wrapper method for MongoCollection::getDBRef().
     *
     * This method will dispatch preGetDBRef and postGetDBRef events.
     *
     * @see http://php.net/manual/en/mongocollection.getdbref.php
     * @param array $reference
     * @return array|null
     */
    public function getDBRef(array $reference)
    {
        if ($this->eventManager->hasListeners(Events::preGetDBRef)) {
            $this->eventManager->dispatchEvent(Events::preGetDBRef, new EventArgs($this, $reference));
        }

        $result = $this->doGetDBRef($reference);

        if ($this->eventManager->hasListeners(Events::postGetDBRef)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postGetDBRef, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Resolves a database reference.
     *
     * @see Collection::getDBRef()
     * @param array $reference
     * @return array|null
     */
    protected function doGetDBRef(array $reference)
    {
        $collection = $this;
        return $this->retry(function() use ($collection, $reference) {
            return $collection->getMongoCollection()->getDBRef($reference);
        });
    }

    /**
     * Invokes the group command.
     *
     * This method will dispatch preGroup and postGroup events.
     *
     * @see http://www.php.net/manual/en/mongocollection.group.php
     * @see http://docs.mongodb.org/manual/reference/command/group/
     * @param string|array|\MongoCode $keys
     * @param array                   $initial
     * @param string|\MongoCode       $reduce
     * @param array                   $options
     * @return ArrayIterator
     */
    public function group($keys, array $initial, $reduce, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preGroup)) {
            $this->eventManager->dispatchEvent(Events::preGroup, new GroupEventArgs($this, $keys, $initial, $reduce, $options));
        }

        $result = $this->doGroup($keys, $initial, $reduce, $options);

        if ($this->eventManager->hasListeners(Events::postGroup)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postGroup, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the group command.
     *
     * @see Collection::group()
     * @param string|array|\MongoCode $keys
     * @param array                   $initial
     * @param string|\MongoCode       $reduce
     * @param array                   $options
     * @return ArrayIterator
     */
    protected function doGroup($keys, array $initial, $reduce, array $options)
    {
        if (is_string($reduce)) {
            $reduce = new \MongoCode($reduce);
        }

        if (isset($options['finalize']) && is_string($options['finalize'])) {
            $options['finalize'] = new \MongoCode($options['finalize']);
        }

        $collection = $this;
        $result = $this->retry(function() use ($collection, $keys, $initial, $reduce, $options) {
            /* Version 1.2.11+ of the driver yields an E_DEPRECATED notice if an
             * empty array is passed to MongoCollection::group(), as it assumes
             * it is the "condition" option's value being passed instead of a
             * well-formed options array (the actual deprecated behavior).
             */
            return empty($options)
                ? $collection->getMongoCollection()->group($keys, $initial, $reduce)
                : $collection->getMongoCollection()->group($keys, $initial, $reduce, $options);
        });
        return new ArrayIterator($result);
    }

    /**
     * Wrapper method for MongoCollection::insert().
     *
     * This method will dispatch preInsert and postInsert events.
     *
     * @see http://php.net/manual/en/mongocollection.insert.php
     * @param array $a       Document to insert
     * @param array $options
     * @return array|boolean
     */
    public function insert(array &$a, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preInsert)) {
            $this->eventManager->dispatchEvent(Events::preInsert, new EventArgs($this, $a, $options));
        }

        $result = $this->doInsert($a, $options);

        if ($this->eventManager->hasListeners(Events::postInsert)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postInsert, $eventArgs);
            $result = $eventArgs->getData();
        }
        return $result;
    }

    /**
     * Execute the insert query.
     *
     * @see Collection::insert()
     * @param array $a
     * @param array $options
     * @return array|boolean
     */
    protected function doInsert(array &$a, array $options)
    {
        $document = $a;
        $result = $this->getMongoCollection()->insert($document, $options);
        if (isset($document['_id'])) {
            $a['_id'] = $document['_id'];
        }
        return $result;
    }

    /**
     * Wrapper method for MongoCollection::remove().
     *
     * This method will dispatch preRemove and postRemove events.
     *
     * @see http://php.net/manual/en/mongocollection.remove.php
     * @param array $query
     * @param array $options
     * @return array|boolean
     */
    public function remove(array $query, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preRemove)) {
            $this->eventManager->dispatchEvent(Events::preRemove, new EventArgs($this, $query, $options));
        }

        $result = $this->doRemove($query, $options);

        if ($this->eventManager->hasListeners(Events::postRemove)) {
            $this->eventManager->dispatchEvent(Events::postRemove, new EventArgs($this, $result));
        }

        return $result;
    }

    /**
     * Execute the remove query.
     *
     * @see Collection::remove()
     * @param array $query
     * @param array $options
     * @return array|boolean
     */
    protected function doRemove(array $query, array $options)
    {
        return $this->getMongoCollection()->remove($query, $options);
    }

    /**
     * Wrapper method for MongoCollection::save().
     *
     * This method will dispatch preSave and postSave events.
     *
     * @see http://php.net/manual/en/mongocollection.remove.php
     * @param array $a       Document to save
     * @param array $options
     * @return array|boolean
     */
    public function save(array &$a, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preSave)) {
            $this->eventManager->dispatchEvent(Events::preSave, new EventArgs($this, $a, $options));
        }

        $result = $this->doSave($a, $options);

        if ($this->eventManager->hasListeners(Events::postSave)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postSave, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Execute the save query.
     *
     * @see Collection::save()
     * @param array $a
     * @param array $options
     * @return array|boolean
     */
    protected function doSave(array &$a, array $options)
    {
        return $this->getMongoCollection()->save($a, $options);
    }

    /**
     * Set whether secondary read queries are allowed for this collection.
     *
     * This method wraps setSlaveOkay() for driver versions before 1.3.0. For
     * newer drivers, this method wraps setReadPreference() and specifies
     * SECONDARY_PREFERRED.
     *
     * @see http://php.net/manual/en/mongocollection.setreadpreference.php
     * @see http://php.net/manual/en/mongocollection.setslaveokay.php
     * @param boolean $ok
     * @return boolean Previous slaveOk value
     */
    public function setSlaveOkay($ok = true)
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            return $this->getMongoCollection()->setSlaveOkay($ok);
        }

        $prevSlaveOkay = $this->getSlaveOkay();

        if ($ok) {
            // Preserve existing tags for non-primary read preferences
            $readPref = $this->getMongoCollection()->getReadPreference();
            $tags = !empty($readPref['tagsets']) ? ReadPreference::convertTagSets($readPref['tagsets']) : array();
            $this->getMongoCollection()->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, $tags);
        } else {
            $this->getMongoCollection()->setReadPreference(\MongoClient::RP_PRIMARY);
        }

        return $prevSlaveOkay;
    }

    /**
     * Get whether secondary read queries are allowed for this collection.
     *
     * This method wraps getSlaveOkay() for driver versions before 1.3.0. For
     * newer drivers, this method considers any read preference other than
     * PRIMARY as a true "slaveOkay" value.
     *
     * @see http://php.net/manual/en/mongocollection.getreadpreference.php
     * @see http://php.net/manual/en/mongocollection.getslaveokay.php
     * @return boolean
     */
    public function getSlaveOkay()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            return $this->getMongoCollection()->getSlaveOkay();
        }

        $readPref = $this->getMongoCollection()->getReadPreference();

        if (is_numeric($readPref['type'])) {
            $readPref['type'] = ReadPreference::convertNumericType($readPref['type']);
        }

        return \MongoClient::RP_PRIMARY !== $readPref['type'];
    }

    /**
     * Wrapper method for MongoCollection::getReadPreference().
     *
     * @see http://php.net/manual/en/mongocollection.getreadpreference.php
     * @return array
     */
    public function getReadPreference()
    {
        return $this->getMongoCollection()->getReadPreference();
    }

    /**
     * Wrapper method for MongoCollection::setReadPreference().
     *
     * @see http://php.net/manual/en/mongocollection.setreadpreference.php
     * @param string $readPreference
     * @param array  $tags
     * @return boolean
     */
    public function setReadPreference($readPreference, array $tags = null)
    {
        if (isset($tags)) {
            return $this->getMongoCollection()->setReadPreference($readPreference, $tags);
        }

        return $this->getMongoCollection()->setReadPreference($readPreference);
    }

    /**
     * Wrapper method for MongoCollection::validate().
     *
     * @see http://php.net/manual/en/mongocollection.validate.php
     * @param string $scanData
     * @return array
     */
    public function validate($scanData = false)
    {
        return $this->getMongoCollection()->validate($scanData);
    }

    /**
     * Wrapper method for MongoCollection::__toString().
     *
     * @see http://www.php.net/manual/en/mongocollection.--tostring.php
     * @return string
     */
    public function __toString()
    {
        return $this->getMongoCollection()->__toString();
    }

    /**
     * Conditionally retry a closure if it yields an exception.
     *
     * If the closure does not return successfully within the configured number
     * of retries, its first exception will be thrown.
     *
     * This method should not be used for write operations.
     *
     * @param \Closure $retry
     * @return mixed
     */
    protected function retry(\Closure $retry)
    {
        if ($this->numRetries) {
            $firstException = null;
            for ($i = 0; $i <= $this->numRetries; $i++) {
                try {
                    return $retry();
                } catch (\MongoException $e) {
                    if (!$firstException) {
                        $firstException = $e;
                    }
                    if ($i === $this->numRetries) {
                        throw $firstException;
                    }
                }
            }
        } else {
            return $retry();
        }
    }
}
