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
use Doctrine\MongoDB\Event\DistinctEventArgs;
use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Event\GroupEventArgs;
use Doctrine\MongoDB\Event\MapReduceEventArgs;
use Doctrine\MongoDB\Event\NearEventArgs;
use Doctrine\MongoDB\Event\UpdateEventArgs;
use Doctrine\MongoDB\Util\ReadPreference;

/**
 * Wrapper for the PHP MongoCollection class.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class Collection
{
    /**
     * The Doctrine Connection object.
     *
     * @var Doctrine\MongoDB\Connection
     */
    protected $connection;

    /**
     * The name of the collection.
     *
     * @var string $name
     */
    protected $name;

    /**
     * The Database instance this collection belongs to.
     *
     * @var Database
     */
    protected $database;

    /**
     * The event manager that is the central point of the event system.
     *
     * @var Doctrine\Common\EventManager
     */
    protected $eventManager;

    /**
     * Mongo command prefix
     *
     * @var string
     */
    protected $cmd;

    /**
     * Number of times to retry queries.
     *
     * @var mixed
     */
    protected $numRetries;

    /**
     * Create a new MongoCollection instance that wraps a PHP MongoCollection instance
     * for a given ClassMetadata instance.
     *
     * @param Connection $connection The Doctrine Connection instance.
     * @param string $name The name of the collection.
     * @param Database $database The Database instance.
     * @param EventManager $evm The EventManager instance.
     * @param string $cmd Mongo cmd character.
     * @param boolean|integer $numRetries Number of times to retry queries.
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

    public function getName()
    {
        return $this->name;
    }

    /**
     * Returns the wrapped MongoCollection instance.
     *
     * @return \MongoCollection
     */
    public function getMongoCollection()
    {
        return $this->database->getMongoDB()->selectCollection($this->name);
    }

    /**
     * Gets the database this collection belongs to.
     *
     * @return Database $database
     */
    public function getDatabase()
    {
        return $this->database;
    }

    public function getIndexInfo()
    {
        return $this->getMongoCollection()->getIndexInfo();
    }

    /**
     * Check if a given field name is indexed in mongodb.
     *
     * @param string $fieldName
     * @return bool
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
     * Creates a new query builder instnce.
     *
     * @return Query\Builder $qb
     */
    public function createQueryBuilder()
    {
        return new Query\Builder($this->database, $this, $this->cmd);
    }

    /** @override */
    public function batchInsert(array &$a, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preBatchInsert)) {
            $this->eventManager->dispatchEvent(Events::preBatchInsert, new EventArgs($this, $a));
        }

        $result = $this->doBatchInsert($a, $options);

        if ($this->eventManager->hasListeners(Events::postBatchInsert)) {
            $this->eventManager->dispatchEvent(Events::postBatchInsert, new EventArgs($this, $result));
        }

        return $a;
    }

    protected function doBatchInsert(array &$a, array $options = array())
    {
        return $this->getMongoCollection()->batchInsert($a, $options);
    }

    /** @override */
    public function update($query, array $newObj, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preUpdate)) {
            $this->eventManager->dispatchEvent(Events::preUpdate, new UpdateEventArgs($this, $query, $newObj));
        }

        $result = $this->doUpdate($query, $newObj, $options);

        if ($this->eventManager->hasListeners(Events::postUpdate)) {
            $this->eventManager->dispatchEvent(Events::postUpdate, new EventArgs($this, $result));
        }

        return $result;
    }

    protected function doUpdate($query, array $newObj, array $options)
    {
        if (is_scalar($query)) {
            $query = array('_id' => $query);
        }
        return $this->getMongoCollection()->update($query, $newObj, $options);
    }

    public function upsert($query, array $newObj, array $options = array())
    {
        $options['upsert'] = true;
        return $this->update($query, $newObj, $options);
    }

    /** @override */
    public function find(array $query = array(), array $fields = array())
    {
        if ($this->eventManager->hasListeners(Events::preFind)) {
            $this->eventManager->dispatchEvent(Events::preFind, new EventArgs($this, $query));
        }

        $result = $this->doFind($query, $fields);

        if ($this->eventManager->hasListeners(Events::postFind)) {
            $this->eventManager->dispatchEvent(Events::postFind, new EventArgs($this, $result));
        }

        return $result;
    }

    protected function doFind(array $query, array $fields)
    {
        $collection = $this;
        $cursor = $this->retry(function() use ($collection, $query, $fields) {
            return $collection->getMongoCollection()->find($query, $fields);
        });
        return $this->wrapCursor($cursor, $query, $fields);
    }

    protected function wrapCursor(\MongoCursor $cursor, $query, $fields)
    {
        return new Cursor($this->connection, $this, $cursor, $query, $fields, $this->numRetries);
    }

    /** @override */
    public function findOne(array $query = array(), array $fields = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindOne)) {
            $this->eventManager->dispatchEvent(Events::preFindOne, new EventArgs($this, $query));
        }

        $result = $this->doFindOne($query, $fields);

        if ($this->eventManager->hasListeners(Events::postFindOne)) {
            $this->eventManager->dispatchEvent(Events::postFindOne, new EventArgs($this, $result));
        }

        return $result;
    }

    protected function doFindOne(array $query, array $fields)
    {
        $collection = $this;
        return $this->retry(function() use ($collection, $query, $fields) {
            return $collection->getMongoCollection()->findOne($query, $fields);
        });
    }

    public function findAndRemove(array $query, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindAndRemove)) {
            $this->eventManager->dispatchEvent(Events::preFindAndRemove, new EventArgs($this, $query));
        }

        $document = $this->doFindAndRemove($query, $options);

        if ($this->eventManager->hasListeners(Events::postFindAndRemove)) {
            $this->eventManager->dispatchEvent(Events::postFindAndRemove, new EventArgs($this, $document));
        }

        return $document;
    }

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

    public function findAndUpdate(array $query, array $newObj, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindAndUpdate)) {
            $this->eventManager->dispatchEvent(Events::preFindAndUpdate, new UpdateEventArgs($this, $query, $query));
        }

        $document = $this->doFindAndUpdate($query, $newObj, $options);

        if ($this->eventManager->hasListeners(Events::postFindAndUpdate)) {
            $this->eventManager->dispatchEvent(Events::postFindAndUpdate, new EventArgs($this, $document));
        }

        return $document;
    }

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

    public function near(array $near, array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preNear)) {
            $this->eventManager->dispatchEvent(Events::preNear, new NearEventArgs($this, $query, $near));
        }

        $result = $this->doNear($near, $query, $options);

        if ($this->eventManager->hasListeners(Events::postNear)) {
            $this->eventManager->dispatchEvent(Events::postNear, new EventArgs($this, $result));
        }

        return $result;
    }

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

    public function distinct($field, array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preDistinct)) {
            $this->eventManager->dispatchEvent(Events::preDistinct, new DistinctEventArgs($this, $field, $query));
        }

        $result = $this->doDistinct($field, $query, $options);

        if ($this->eventManager->hasListeners(Events::postDistinct)) {
            $this->eventManager->dispatchEvent(Events::postDistinct, new EventArgs($this, $result));
        }

        return $result;
    }

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

    public function mapReduce($map, $reduce, array $out = array('inline' => true), array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preMapReduce)) {
            $this->eventManager->dispatchEvent(Events::preMapReduce, new MapReduceEventArgs($this, $map, $reduce, $out, $query));
        }

        $result = $this->doMapReduce($map, $reduce, $out, $query, $options);

        if ($this->eventManager->hasListeners(Events::postMapReduce)) {
            $this->eventManager->dispatchEvent(Events::postMapReduce, new EventArgs($this, $result));
        }

        return $result;
    }

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

    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        $collection = $this;
        return $this->retry(function() use ($collection, $query, $limit, $skip) {
            return $collection->getMongoCollection()->count($query, $limit, $skip);
        });
    }

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

    protected function doCreateDBRef(array $a)
    {
        return $this->getMongoCollection()->createDBRef($a);
    }

    public function deleteIndex($keys)
    {
        return $this->getMongoCollection()->deleteIndex($keys);
    }

    public function deleteIndexes()
    {
        return $this->getMongoCollection()->deleteIndexes();
    }

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

    protected function doDrop()
    {
        return $this->getMongoCollection()->drop();
    }

    public function ensureIndex(array $keys, array $options = array())
    {
        return $this->getMongoCollection()->ensureIndex($keys, $options);
    }

    public function __get($name)
    {
        return $this->getMongoCollection()->__get($name);
    }

    public function getDBRef(array $reference)
    {
        if ($this->eventManager->hasListeners(Events::preGetDBRef)) {
            $this->eventManager->dispatchEvent(Events::preGetDBRef, new EventArgs($this, $reference));
        }

        $result = $this->doGetDBRef($reference);

        if ($this->eventManager->hasListeners(Events::postGetDBRef)) {
            $this->eventManager->dispatchEvent(Events::postGetDBRef, new EventArgs($this, $result));
        }

        return $result;
    }

    protected function doGetDBRef(array $reference)
    {
        $collection = $this;
        return $this->retry(function() use ($collection, $reference) {
            return $collection->getMongoCollection()->getDBRef($reference);
        });
    }

    public function group($keys, array $initial, $reduce, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preGroup)) {
            $this->eventManager->dispatchEvent(Events::preGroup, new GroupEventArgs($this, $keys, $initial, $reduce));
        }

        $result = $this->doGroup($keys, $initial, $reduce, $options);

        if ($this->eventManager->hasListeners(Events::postGroup)) {
            $this->eventManager->dispatchEvent(Events::postGroup, new EventArgs($this, $result));
        }

        return $result;
    }

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

    public function insert(array &$a, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preInsert)) {
            $this->eventManager->dispatchEvent(Events::preInsert, new EventArgs($this, $a));
        }

        $result = $this->doInsert($a, $options);

        if ($this->eventManager->hasListeners(Events::postInsert)) {
            $this->eventManager->dispatchEvent(Events::postInsert, new EventArgs($this, $result));
        }
        return $result;
    }

    protected function doInsert(array &$a, array $options)
    {
        $document = $a;
        $result = $this->getMongoCollection()->insert($document, $options);
        if (isset($document['_id'])) {
            $a['_id'] = $document['_id'];
        }
        return $result;
    }

    public function remove(array $query, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preRemove)) {
            $this->eventManager->dispatchEvent(Events::preRemove, new EventArgs($this, $query));
        }

        $result = $this->doRemove($query, $options);

        if ($this->eventManager->hasListeners(Events::postRemove)) {
            $this->eventManager->dispatchEvent(Events::postRemove, new EventArgs($this, $result));
        }

        return $result;
    }

    protected function doRemove(array $query, array $options)
    {
        return $this->getMongoCollection()->remove($query, $options);
    }

    public function save(array &$a, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preSave)) {
            $this->eventManager->dispatchEvent(Events::preSave, new EventArgs($this, $a));
        }

        $result = $this->doSave($a, $options);

        if ($this->eventManager->hasListeners(Events::postSave)) {
            $this->eventManager->dispatchEvent(Events::postSave, new EventArgs($this, $result));
        }

        return $result;
    }

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

    public function validate($scanData = false)
    {
        return $this->getMongoCollection()->validate($scanData);
    }

    public function __toString()
    {
        return $this->getMongoCollection()->__toString();
    }

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
