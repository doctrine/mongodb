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
    Doctrine\MongoDB\Event\EventArgs,
    Doctrine\MongoDB\Event\DistinctEventArgs,
    Doctrine\MongoDB\Event\GroupEventArgs,
    Doctrine\MongoDB\Event\NearEventArgs,
    Doctrine\MongoDB\Event\MapReduceEventArgs,
    Doctrine\MongoDB\Event\UpdateEventArgs;

/**
 * Wrapper for the PHP MongoCollection class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
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
     * @param string $cmd Mongo cmd character.
     */
    public function __construct(\MongoCollection $mongoCollection, Database $database, EventManager $evm, $cmd)
    {
        $this->mongoCollection = $mongoCollection;
        $this->database = $database;
        $this->eventManager = $evm;
        $this->cmd = $cmd;
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
        return $this->mongoCollection->batchInsert($a, $options);
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
        return $this->mongoCollection->update($query, $newObj, $options);
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
        $cursor = $this->mongoCollection->find($query, $fields);
        return $this->wrapCursor($cursor, $query, $fields);
    }

    protected function wrapCursor(\MongoCursor $cursor, $query, $fields)
    {
        return new Cursor($cursor);
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
        return $this->mongoCollection->findOne($query, $fields);
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
        $command['findandmodify'] = $this->mongoCollection->getName();
        $command['query'] = $query;
        $command['remove'] = true;
        $command = array_merge($command, $options);

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
        $command['findandmodify'] = $this->mongoCollection->getName();
        $command['query'] = $query;
        $command['update'] = $newObj;
        $command = array_merge($command, $options);
        $result = $this->database->command($command);
        return isset($result['value']) ? $result['value'] : null;
    }

    public function near(array $near, array $query = array(), array $options = array())
    {
        if ($this->eventManager->hasListeners(Events::preNear)) {
            $this->eventManager->dispatchEvent(Events::preNear, new NearEventArgs($this, $near, $query));
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
        $command['geoNear'] = $this->mongoCollection->getName();
        $command['near'] = $near;
        $command['query'] = $query;
        $command = array_merge($command, $options);
        $result = $this->database->command($command);
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
        $command['distinct'] = $this->mongoCollection->getName();
        $command['key'] = $field;
        $command['query'] = $query;
        $command = array_merge($command, $options);
        $result = $this->database->command($command);
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
        $command['mapreduce'] = $this->mongoCollection->getName();
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

    /** @proxy */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        return $this->mongoCollection->count($query, $limit, $skip);
    }

    /** @proxy */
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
        return $this->mongoCollection->createDBRef($a);
    }

    /** @proxy */
    public function deleteIndex($keys)
    {
        return $this->mongoCollection->deleteIndex($keys);
    }

    /** @proxy */
    public function deleteIndexes()
    {
        return $this->mongoCollection->deleteIndexes();
    }

    /** @proxy */
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
        return $this->mongoCollection->drop();
    }

    /** @proxy */
    public function ensureIndex(array $keys, array $options = array())
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
        return $this->mongoCollection->getDBRef($reference);
    }

    /** @proxy */
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
        $result = $this->mongoCollection->group($keys, $initial, $reduce, $options);
        return new ArrayIterator($result);
    }

    /** @proxy */
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
        return $this->mongoCollection->remove($query, $options);
    }

    /** @proxy */
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
        return $this->mongoCollection->save($a, $options);
    }

    /** @proxy */
    public function validate($scanData = false)
    {
        return $this->mongoCollection->validate($scanData);
    }

    /** @proxy */
    public function __toString()
    {
        return $this->mongoCollection->__toString();
    }
}
