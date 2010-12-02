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

namespace Doctrine\MongoDB;

use Doctrine\Common\EventManager,
    Doctrine\ODM\Event\CollectionEventArgs;

/**
 * Wrapper for the PHP MongoCollection class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Collection
{
    /**
     * The PHP MongoCollection being wrapped.
     *
     * @var \MongoCollection
     */
    protected $mongoCollection;

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
     * A callable for logging statements.
     *
     * @var mixed
     */
    protected $loggerCallable;

    /**
     * Mongo command prefix
     *
     * @var string
     */
    protected $cmd;

    /**
     * Create a new MongoCollection instance that wraps a PHP MongoCollection instance
     * for a given ClassMetadata instance.
     *
     * @param MongoCollection $mongoCollection The MongoCollection instance.
     * @param Database $database The Database instance.
     * @param EventManager $evm The EventManager instance.
     * @param mixed $loggerCallable The logger callable.
     */
    public function __construct(\MongoCollection $mongoCollection, Database $database, EventManager $evm, $loggerCallable, $cmd)
    {
        $this->mongoCollection = $mongoCollection;
        $this->database = $database;
        $this->eventManager = $evm;
        $this->loggerCallable = $loggerCallable;
        $this->cmd = $cmd;
    }

    /**
     * Log something using the configured logger callable.
     *
     * @param array $log The array of data to log.
     */
    public function log(array $log)
    {
        $log['db'] = $this->database->getName();
        $log['collection'] = $this->getName();
        call_user_func_array($this->loggerCallable, array($log));
    }

    /** @proxy */
    public function getName()
    {
        return $this->mongoCollection->getName();
    }

    /**
     * Returns the wrapped MongoCollection instance.
     *
     * @return \MongoCollection
     */
    public function getMongoCollection()
    {
        return $this->mongoCollection;
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

    /** @proxy */
    public function getIndexInfo()
    {
        return $this->mongoCollection->getIndexInfo();
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
            $this->eventManager->dispatchEvent(Events::preBatchInsert, new CollectionEventArgs($this, $a));
        }

        $this->doBatchInsert($a, $options);

        if ($this->loggerCallable) {
            $this->log(array(
                'batchInsert' => true,
                'num' => count($a),
                'data' => $a,
                'options' => $options
            ));
        }

        if ($this->eventManager->hasListeners(Events::postBatchInsert)) {
            $this->eventManager->dispatchEvent(Events::postBatchInsert, new CollectionEventArgs($this, $result));
        }

        return $a;
    }

    protected function doBatchInsert(array &$a, array $options = array())
    {
        return $this->mongoCollection->batchInsert($a, $options);
    }

    /** @override */
    public function update($query, array $newObj, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preUpdate)) {
            $this->eventManager->dispatchEvent(Events::preUpdate, new CollectionUpdateEventArgs($this, $query, $newObj));
        }

        if ($this->loggerCallable) {
            $this->log(array(
                'update' => true,
                'query' => $query,
                'newObj' => $newObj,
                'options' => $options
            ));
        }

        $result = $this->doUpdate($query, $newObj, $options);

        if ($this->eventManager->hasListeners(Events::postUpdate)) {
            $this->eventManager->dispatchEvent(Events::postUpdate, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doUpdate($query, array $newObj, array $options)
    {
        if (is_scalar($query)) {
            $query = array('_id' => $query);
        }
        return $this->mongoCollection->update($query, $newObj, $options);
    }

    /** @override */
    public function find(array $query = array(), array $fields = array())
    {
        if ($this->eventManager->hasListeners(Events::preFind)) {
            $this->eventManager->dispatchEvent(Events::preFind, new CollectionEventArgs($this, $query));
        }

        if ($this->loggerCallable) {
            $this->log(array(
                'find' => true,
                'query' => $query,
                'fields' => $fields
            ));
        }

        $result = $this->doFind($query, $fields);

        if ($this->eventManager->hasListeners(Events::postFind)) {
            $this->eventManager->dispatchEvent(Events::postFind, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doFind(array $query, array $fields)
    {
        $cursor = $this->mongoCollection->find($query, $fields);
        return new Cursor($cursor);
    }

    /** @override */
    public function findOne(array $query = array(), array $fields = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindOne)) {
            $this->eventManager->dispatchEvent(Events::preFindOne, new CollectionEventArgs($this, $query));
        }

        if ($this->loggerCallable) {
            $this->log(array(
                'findOne' => true,
                'query' => $query,
                'fields' => $fields
            ));
        }

        $result = $this->doFindOne($query, $fields);

        if ($this->eventManager->hasListeners(Events::postFindOne)) {
            $this->eventManager->dispatchEvent(Events::postFindOne, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doFindOne(array $query, array $fields)
    {
        return $this->mongoCollection->findOne($query, $fields);
    }

    public function findAndRemove(array $query, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindAndRemove)) {
            $this->eventManager->dispatchEvent(Events::preFindAndRemove, new CollectionEventArgs($this, $query));
        }

        $document = $this->doFindAndRemove($query, $options);

        if ($this->eventManager->hasListeners(Events::postFindAndRemove)) {
            $this->eventManager->dispatchEvent(Events::postFindAndRemove, new CollectionEventArgs($this, $document));
        }

        return $document;
    }

    protected function doFindAndRemove(array $query, array $options = array())
    {
        $command = $options;
        $command['findandmodify'] = $this->mongoCollection->getName();
        $command['query'] = $query;
        $command['remove'] = true;

        $document = null;
        $result = $this->database->command($command);
        if (isset($result['value'])) {
            $document = $result['value'];
            if ($this->mongoCollection instanceof \MongoGridFS) {
                // Remove the file data from the chunks collection
                $this->mongoCollection->chunks->remove(array('files_id' => $document['_id']), $options);
            }
        }
        return $document;
    }

    public function findAndUpdate(array $query, array $newObj, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preFindAndUpdate)) {
            $this->eventManager->dispatchEvent(Events::preFindAndUpdate, new CollectionUpdateEventArgs($this, $query, $query));
        }

        $document = $this->doFindAndUpdate($query, $newObj, $options);

        if ($this->eventManager->hasListeners(Events::postFindAndUpdate)) {
            $this->eventManager->dispatchEvent(Events::postFindAndUpdate, new CollectionEventArgs($this, $document));
        }

        return $document;
    }

    protected function doFindAndUpdate(array $query, array $newObj, array $options)
    {
        $command = $options;
        $command['findandmodify'] = $this->mongoCollection->getName();
        $command['query'] = $query;
        $command['update'] = $newObj;
        $result = $this->database->command($command);
        return isset($result['value']) ? $result['value'] : null;
    }

    public function near(array $near, array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preNear)) {
            $this->eventManager->dispatchEvent(Events::preNear, new CollectionNearEventArgs($this, $near, $query));
        }

        $result = $this->doNear($near, $query, $options);

        if ($this->eventManager->hasListeners(Events::postNear)) {
            $this->eventManager->dispatchEvent(Events::postNear, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doNear(array $near, array $query, array $options)
    {
        $command = $options;
        $command['geoNear'] = $this->mongoCollection->getName();
        $command['near'] = $near;
        $command['query'] = $query;
        $result = $this->database->command($command);
        return new ArrayIterator(isset($result['results']) ? $result['results'] : array());
    }

    public function distinct($field, array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preDistinct)) {
            $this->eventManager->dispatchEvent(Events::preDistinct, new CollectionDistinctEventArgs($this, $field, $query));
        }

        $result = $this->doDistinct($field, $query, $options);

        if ($this->eventManager->hasListeners(Events::postDistinct)) {
            $this->eventManager->dispatchEvent(Events::postDistinct, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doDistinct($field, array $query, array $options)
    {
        $command = $options;
        $command['distinct'] = $this->mongoCollection->getName();
        $command['key'] = $field;
        $command['query'] = $query;
        $result = $this->database->command($command);
        return new ArrayIterator(isset($result['values']) ? $result['values'] : array());
    }

    public function mapReduce($map, $reduce, array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preDistinct)) {
            $this->eventManager->dispatchEvent(Events::preDistinct, new CollectionDistinctEventArgs($this, $map, $reduce, $query));
        }

        $result = $this->doMapReduce($map, $reduce, $query, $options);

        if ($this->eventManager->hasListeners(Events::postDistinct)) {
            $this->eventManager->dispatchEvent(Events::postDistinct, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doMapReduce($map, $reduce, array $query, array $options)
    {
        if (is_string($map)) {
            $map = new \MongoCode($map);
        }
        if (is_string($reduce)) {
            $reduce = new \MongoCode($reduce);
        }
        $command = $options;
        $command['mapreduce'] = $this->mongoCollection->getName();
        $command['map'] = $map;
        $command['reduce'] = $reduce;
        $command['query'] = $query;

        $result = $this->database->command($command);

        if ( ! $result['ok']) {
            print_r($command);
            print_r($result);
            throw new \RuntimeException($result['errmsg']);
        }

        $cursor = $this->database->selectCollection($result['result'])->find();
        return new Cursor($cursor);
    }

    /** @proxy */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        if ($this->loggerCallable) {
            $this->log(array(
                'count' => true,
                'query' => $query,
                'limit' => $limit,
                'skip' => $skip
            ));
        }

        return $this->mongoCollection->count($query, $limit, $skip);
    }

    /** @proxy */
    public function createDBRef(array $a)
    {
        if ($this->eventManager->hasListeners(Events::preCreateDBRef)) {
            $this->eventManager->dispatchEvent(Events::preCreateDBRef, new CollectionEventArgs($this, $a));
        }

        if ($this->loggerCallable) {
            $this->log(array(
                'createDBRef' => true,
                'reference' => $a
            ));
        }

        $result = $this->doCreateDBRef($a);

        if ($this->eventManager->hasListeners(Events::postCreateDBRef)) {
            $this->eventManager->dispatchEvent(Events::postCreateDBRef, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doCreateDBRef(array $a)
    {
        return $this->mongoCollection->createDBRef($a);
    }

    /** @proxy */
    public function deleteIndex($keys)
    {
        if ($this->loggerCallable) {
            $this->log(array(
                'deleteIndex' => true,
                'keys' => $keys
            ));
        }

        return $this->doDeleteIndex($keys);
    }

    protected function doDeleteIndex($keys)
    {
        return $this->mongoCollection->deleteIndex($keys);
    }

    /** @proxy */
    public function deleteIndexes()
    {
        if ($this->loggerCallable) {
            $this->log(array(
                'deleteIndexes' => true
            ));
        }

        return $this->doDeleteIndexes();
    }

    protected function doDeleteIndexes()
    {
        return $this->mongoCollection->deleteIndexes();
    }

    /** @proxy */
    public function drop()
    {
        if ($this->loggerCallable) {
            $this->log(array(
                'drop' => true
            ));
        }

        return $this->doDrop();
    }

    protected function doDrop()
    {
        return $this->mongoCollection->drop();
    }

    /** @proxy */
    public function ensureIndex(array $keys, array $options)
    {
        if ($this->loggerCallable) {
            $this->log(array(
                'ensureIndex' => true,
                'keys' => $keys,
                'options' => $options
            ));
        }

        return $this->doEnsureIndex($keys, $options);
    }

    protected function doEnsureIndex(array $keys, array $options)
    {
        return $this->mongoCollection->ensureIndex($keys, $options);
    }

    /** @proxy */
    public function __get($name)
    {
        return $this->mongoCollection->__get($name);
    }

    /** @proxy */
    public function getDBRef(array $reference)
    {
        if ($this->eventManager->hasListeners(Events::preGetDBRef)) {
            $this->eventManager->dispatchEvent(Events::preGetDBRef, new CollectionEventArgs($this, $reference));
        }

        if ($this->loggerCallable) {
            $this->log(array(
                'getDBRef' => true,
                'reference' => $reference
            ));
        }

        $result = $this->doGetDBRef($reference);

        if ($this->eventManager->hasListeners(Events::postGetDBRef)) {
            $this->eventManager->dispatchEvent(Events::postGetDBRef, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doGetDBRef(array $reference)
    {
        return $this->mongoCollection->getDBRef($reference);
    }

    /** @proxy */
    public function group($keys, array $initial, $reduce, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preGroup)) {
            $this->eventManager->dispatchEvent(Events::preGroup, new CollectionGroupEventArgs($this, $keys, $initial, $reduce));
        }

        if ($this->loggerCallable) {
            $this->log(array(
                'group' => true,
                'keys' => $keys,
                'initial' => $initial,
                'reduce' => $reduce,
                'options' => $options
            ));
        }

        $result = $this->doGroup($keys, $initial, $reduce, $options);

        if ($this->eventManager->hasListeners(Events::postGroup)) {
            $this->eventManager->dispatchEvent(Events::postGroup, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doGroup($keys, array $initial, $reduce, array $options)
    {
        $result = $this->mongoCollection->group($keys, $initial, $reduce, $options);
        return new ArrayIterator($result);
    }

    /** @proxy */
    public function insert(array &$a, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preInsert)) {
            $this->eventManager->dispatchEvent(Events::preInsert, new CollectionEventArgs($this, $a));
        }

        $result = $this->doInsert($a, $options);

        if ($this->loggerCallable) {
            $this->log(array(
                'insert' => true,
                'document' => $a,
                'options' => $options
            ));
        }

        if ($this->eventManager->hasListeners(Events::postInsert)) {
            $this->eventManager->dispatchEvent(Events::postInsert, new CollectionEventArgs($this, $result));
        }
        return $result;
    }

    protected function doInsert(array &$a, array $options)
    {
        $document = $a;
        $result = $this->mongoCollection->insert($document, $options);
        if ($result && isset($document['_id'])) {
            $a['_id'] = $document['_id'];
        }
        return $result;
    }

    /** @proxy */
    public function remove(array $query, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preRemove)) {
            $this->eventManager->dispatchEvent(Events::preRemove, new CollectionEventArgs($this, $query));
        }

        if ($this->loggerCallable) {
            $this->log(array(
                'remove' => true,
                'query' => $query,
                'options' => $options
            ));
        }

        $result = $this->doRemove($query, $options);

        if ($this->eventManager->hasListeners(Events::postRemove)) {
            $this->eventManager->dispatchEvent(Events::postRemove, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doRemove(array $query, array $options)
    {
        return $this->mongoCollection->remove($query, $options);
    }

    /** @proxy */
    public function save(array &$a, array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preSave)) {
            $this->eventManager->dispatchEvent(Events::preSave, new CollectionEventArgs($this, $a));
        }

        $result = $this->doSave($a, $options);

        if ($this->loggerCallable) {
            $this->log(array(
                'save' => true,
                'document' => $a,
                'options' => $options
            ));
        }

        if ($this->eventManager->hasListeners(Events::postSave)) {
            $this->eventManager->dispatchEvent(Events::postSave, new CollectionEventArgs($this, $result));
        }

        return $result;
    }

    protected function doSave(array &$a, array $options)
    {
        return $this->mongoCollection->save($a, $options);
    }

    /** @proxy */
    public function validate($scanData = false)
    {
        if ($this->loggerCallable) {
            $this->log(array(
                'validate' => true,
                'scanData' => $scanData
            ));
        }

        return $this->mongoCollection->validate($scanData);
    }

    /** @proxy */
    public function __toString()
    {
        return $this->mongoCollection->__toString();
    }
}