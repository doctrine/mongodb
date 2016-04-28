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
use Doctrine\MongoDB\Exception\ResultException;
use GeoJson\Geometry\Point;
use BadMethodCallException;
use MongoCommandCursor;

/**
 * Wrapper for the MongoCollection class.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class Collection
{
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
     * The MongoCollection instance being wrapped.
     *
     * @var \MongoCollection
     */
    protected $mongoCollection;

    /**
     * Number of times to retry queries.
     *
     * @var integer
     */
    protected $numRetries;

    /**
     * Constructor.
     *
     * @param Database         $database        Database to which this collection belongs
     * @param \MongoCollection $mongoCollection MongoCollection instance being wrapped
     * @param EventManager     $evm             EventManager instance
     * @param integer          $numRetries      Number of times to retry queries
     */
    public function __construct(Database $database, \MongoCollection $mongoCollection, EventManager $evm, $numRetries = 0)
    {
        $this->database = $database;
        $this->mongoCollection = $mongoCollection;
        $this->eventManager = $evm;
        $this->numRetries = (integer) $numRetries;
    }

    /**
     * Invokes the aggregate command.
     *
     * This method will dispatch preAggregate and postAggregate events.
     *
     * By default, the results from a non-cursor aggregate command will be
     * returned as an ArrayIterator; however, if the pipeline ends in an $out
     * operator, a cursor on the output collection will be returned instead.
     *
     * If the "cursor" option is true or an array, a command cursor will be
     * returned (requires driver >= 1.5.0 and MongoDB >= 2.6).
     *
     * @see http://php.net/manual/en/mongocollection.aggregate.php
     * @see http://docs.mongodb.org/manual/reference/command/aggregate/
     * @param array $pipeline Array of pipeline operators, or the first operator
     * @param array $options  Command options (if $pipeline was an array of pipeline operators)
     * @param array $op,...   Additional operators (if $pipeline was the first)
     * @return Iterator
     * @throws ResultException if the command fails
     */
    public function aggregate(array $pipeline, array $options = [] /* , array $op, ... */)
    {
        /* If the single array argument contains a zeroth index, consider it an
         * array of pipeline operators. Otherwise, assume that each argument is
         * a pipeline operator.
         */
        if ( ! array_key_exists(0, $pipeline)) {
            $pipeline = func_get_args();
            $options = [];
        }

        if ($this->eventManager->hasListeners(Events::preAggregate)) {
            $aggregateEventArgs = new AggregateEventArgs($this, $pipeline, $options);
            $this->eventManager->dispatchEvent(Events::preAggregate, $aggregateEventArgs);
            $pipeline = $aggregateEventArgs->getPipeline();
            $options = $aggregateEventArgs->getOptions();
        }

        $result = $this->doAggregate($pipeline, $options);

        if ($this->eventManager->hasListeners(Events::postAggregate)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postAggregate, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
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
    public function batchInsert(array &$a, array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preBatchInsert)) {
            $eventArgs = new EventArgs($this, $a, $options);
            $this->eventManager->dispatchEvent(Events::preBatchInsert, $eventArgs);
            $a = $eventArgs->getData();
            $options = $eventArgs->getOptions();
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
     * Invokes the count command.
     *
     * @see http://php.net/manual/en/mongocollection.count.php
     * @see http://docs.mongodb.org/manual/reference/command/count/
     * @param array         $query
     * @param integer|array $limitOrOptions Limit or options array
     * @param integer       $skip
     * @return integer
     */
    public function count(array $query = [], $limitOrOptions = 0, $skip = 0)
    {
        $options = is_array($limitOrOptions)
            ? array_merge(['limit' => 0, 'skip' => 0], $limitOrOptions)
            : ['limit' => $limitOrOptions, 'skip' => $skip];

        $options['limit'] = (integer) $options['limit'];
        $options['skip'] = (integer) $options['skip'];

        return $this->doCount($query, $options);
    }

    /**
     * Creates a new aggregation builder instance.
     *
     * @return Aggregation\Builder
     */
    public function createAggregationBuilder()
    {
        return new Aggregation\Builder($this);
    }

    /**
     * Wrapper method for MongoCollection::createDBRef().
     *
     * @see http://php.net/manual/en/mongocollection.createdbref.php
     * @param mixed $documentOrId
     * @return array
     */
    public function createDBRef($documentOrId)
    {
        return $this->mongoCollection->createDBRef($documentOrId);
    }

    /**
     * Creates a new query builder instance.
     *
     * @return \Doctrine\MongoDB\Query\Builder
     */
    public function createQueryBuilder()
    {
        return new Query\Builder($this);
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
        return $this->mongoCollection->deleteIndex($keys);
    }

    /**
     * Wrapper method for MongoCollection::deleteIndexes().
     *
     * @see http://php.net/manual/en/mongocollection.deleteindexes.php
     * @return array
     */
    public function deleteIndexes()
    {
        return $this->mongoCollection->deleteIndexes();
    }

    /**
     * Invokes the distinct command.
     *
     * This method will dispatch preDistinct and postDistinct events.
     *
     * @see http://php.net/manual/en/mongocollection.distinct.php
     * @see http://docs.mongodb.org/manual/reference/command/distinct/
     * @param string $field
     * @param array  $query
     * @param array  $options
     * @return ArrayIterator
     * @throws ResultException if the command fails
     */
    public function distinct($field, array $query = [], array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preDistinct)) {
            /* The distinct command currently does not have options beyond field
             * and query, so do not include it in the event args.
             */
            $distinctEventArgs = new DistinctEventArgs($this, $field, $query);
            $this->eventManager->dispatchEvent(Events::preDistinct, $distinctEventArgs);
            $query = $distinctEventArgs->getQuery();
            $field = $distinctEventArgs->getField();
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
     * Wrapper method for MongoCollection::ensureIndex().
     *
     * @see http://php.net/manual/en/mongocollection.ensureindex.php
     * @param array $keys
     * @param array $options
     * @return array|boolean
     */
    public function ensureIndex(array $keys, array $options = [])
    {
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;

        return $this->mongoCollection->ensureIndex($keys, $options);
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
    public function find(array $query = [], array $fields = [])
    {
        if ($this->eventManager->hasListeners(Events::preFind)) {
            $findEventArgs = new FindEventArgs($this, $query, $fields);
            $this->eventManager->dispatchEvent(Events::preFind, $findEventArgs);
            $query = $findEventArgs->getQuery();
            $fields = $findEventArgs->getFields();
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
     * Invokes the findAndModify command with the remove option.
     *
     * This method will dispatch preFindAndRemove and postFindAndRemove events.
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @param array $query
     * @param array $options
     * @return array|null
     * @throws ResultException if the command fails
     */
    public function findAndRemove(array $query, array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preFindAndRemove)) {
            $eventArgs = new MutableEventArgs($this, $query, $options);
            $this->eventManager->dispatchEvent(Events::preFindAndRemove, $eventArgs);
            $query = $eventArgs->getData();
            $options = $eventArgs->getOptions();
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
     * Invokes the findAndModify command with the update option.
     *
     * This method will dispatch preFindAndUpdate and postFindAndUpdate events.
     *
     * @see http://docs.mongodb.org/manual/reference/command/findAndModify/
     * @param array $query
     * @param array $newObj
     * @param array $options
     * @return array|null
     * @throws ResultException if the command fails
     */
    public function findAndUpdate(array $query, array $newObj, array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preFindAndUpdate)) {
            $updateEventArgs = new UpdateEventArgs($this, $query, $newObj, $options);
            $this->eventManager->dispatchEvent(Events::preFindAndUpdate, $updateEventArgs);
            $query = $updateEventArgs->getQuery();
            $newObj = $updateEventArgs->getNewObj();
            $options = $updateEventArgs->getOptions();
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
     * Wrapper method for MongoCollection::findOne().
     *
     * This method will dispatch preFindOne and postFindOne events.
     *
     * @see http://php.net/manual/en/mongocollection.findone.php
     * @param array $query
     * @param array $fields
     * @return array|null
     */
    public function findOne(array $query = [], array $fields = [])
    {
        if ($this->eventManager->hasListeners(Events::preFindOne)) {
            $findEventArgs = new FindEventArgs($this, $query, $fields);
            $this->eventManager->dispatchEvent(Events::preFindOne, $findEventArgs);
            $query = $findEventArgs->getQuery();
            $fields = $findEventArgs->getFields();
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
     * Return the database for this collection.
     *
     * @return Database
     */
    public function getDatabase()
    {
        return $this->database;
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
            $eventArgs = new EventArgs($this, $reference);
            $this->eventManager->dispatchEvent(Events::preGetDBRef, $eventArgs);
            $reference = $eventArgs->getData();
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
     * Wrapper method for MongoCollection::getIndexInfo().
     *
     * @see http://php.net/manual/en/mongocollection.getindexinfo.php
     * @return array
     */
    public function getIndexInfo()
    {
        return $this->mongoCollection->getIndexInfo();
    }

    /**
     * Return the MongoCollection instance being wrapped.
     *
     * @return \MongoCollection
     */
    public function getMongoCollection()
    {
        return $this->mongoCollection;
    }

    /**
     * Wrapper method for MongoCollection::getName().
     *
     * @see http://php.net/manual/en/mongocollection.getname.php
     * @return string
     */
    public function getName()
    {
        return $this->mongoCollection->getName();
    }

    /**
     * Wrapper method for MongoCollection::getReadPreference().
     *
     * For driver versions between 1.3.0 and 1.3.3, the return value will be
     * converted for consistency with {@link Collection::setReadPreference()}.
     *
     * @see http://php.net/manual/en/mongocollection.getreadpreference.php
     * @return array
     */
    public function getReadPreference()
    {
        return $this->mongoCollection->getReadPreference();
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
            return $this->mongoCollection->setReadPreference($readPreference, $tags);
        }

        return $this->mongoCollection->setReadPreference($readPreference);
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
        $readPref = $this->getReadPreference();

        return \MongoClient::RP_PRIMARY !== $readPref['type'];
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
        $prevSlaveOkay = $this->getSlaveOkay();

        if ($ok) {
            // Preserve existing tags for non-primary read preferences
            $readPref = $this->getReadPreference();
            $tags = ! empty($readPref['tagsets']) ? $readPref['tagsets'] : [];
            $this->mongoCollection->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, $tags);
        } else {
            $this->mongoCollection->setReadPreference(\MongoClient::RP_PRIMARY);
        }

        return $prevSlaveOkay;
    }

    /**
     * Invokes the group command.
     *
     * This method will dispatch preGroup and postGroup events.
     *
     * @see http://www.php.net/manual/en/mongocollection.group.php
     * @see http://docs.mongodb.org/manual/reference/command/group/
     * @param array|string|\MongoCode $keys
     * @param array                   $initial
     * @param string|\MongoCode       $reduce
     * @param array                   $options
     * @return ArrayIterator
     * @throws ResultException if the command fails
     */
    public function group($keys, array $initial, $reduce, array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preGroup)) {
            $groupEventArgs = new GroupEventArgs($this, $keys, $initial, $reduce, $options);
            $this->eventManager->dispatchEvent(Events::preGroup, $groupEventArgs);
            $keys = $groupEventArgs->getKeys();
            $initial = $groupEventArgs->getInitial();
            $reduce = $groupEventArgs->getReduce();
            $options = $groupEventArgs->getOptions();
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
     * Wrapper method for MongoCollection::insert().
     *
     * This method will dispatch preInsert and postInsert events.
     *
     * @see http://php.net/manual/en/mongocollection.insert.php
     * @param array $a       Document to insert
     * @param array $options
     * @return array|boolean
     */
    public function insert(array &$a, array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preInsert)) {
            $eventArgs = new EventArgs($this, $a, $options);
            $this->eventManager->dispatchEvent(Events::preInsert, $eventArgs);
            $a = $eventArgs->getData();
            $options = $eventArgs->getOptions();
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
     * Invokes the mapReduce command.
     *
     * This method will dispatch preMapReduce and postMapReduce events.
     *
     * If the output method is inline, an ArrayIterator will be returned.
     * Otherwise, a Cursor to all documents in the output collection will be
     * returned.
     *
     * @see http://docs.mongodb.org/manual/reference/command/mapReduce/
     * @param string|\MongoCode $map
     * @param string|\MongoCode $reduce
     * @param array|string      $out
     * @param array             $query
     * @param array             $options
     * @return ArrayIterator|Cursor
     * @throws ResultException if the command fails
     */
    public function mapReduce($map, $reduce, $out = ['inline' => true], array $query = [], array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preMapReduce)) {
            $mapReduceEventArgs = new MapReduceEventArgs($this, $map, $reduce, $out, $query, $options);
            $this->eventManager->dispatchEvent(Events::preMapReduce, $mapReduceEventArgs);
            $map = $mapReduceEventArgs->getMap();
            $reduce = $mapReduceEventArgs->getReduce();
            $out = $mapReduceEventArgs->getOut();
            $query = $mapReduceEventArgs->getQuery();
            $options = $mapReduceEventArgs->getOptions();
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
     * Invokes the geoNear command.
     *
     * This method will dispatch preNear and postNear events.
     *
     * The $near parameter may be a GeoJSON point or a legacy coordinate pair,
     * which is an array of float values in x, y order (easting, northing for
     * projected coordinates, longitude, latitude for geographic coordinates).
     * A GeoJSON point may be a Point object or an array corresponding to the
     * point's JSON representation.
     *
     * @see http://docs.mongodb.org/manual/reference/command/geoNear/
     * @param array|Point $near
     * @param array       $query
     * @param array       $options
     * @return ArrayIterator
     * @throws ResultException if the command fails
     */
    public function near($near, array $query = [], array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preNear)) {
            $nearEventArgs = new NearEventArgs($this, $query, $near, $options);
            $this->eventManager->dispatchEvent(Events::preNear, $nearEventArgs);
            $query = $nearEventArgs->getQuery();
            $near = $nearEventArgs->getNear();
            $options = $nearEventArgs->getOptions();
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
     * Wrapper method for MongoCollection::parallelCollectionScan()
     *
     * @param int $numCursors
     * @return CommandCursor[]
     *
     * @throws BadMethodCallException if MongoCollection::parallelCollectionScan() is not available
     */
    public function parallelCollectionScan($numCursors)
    {
        $mongoCollection = $this->mongoCollection;
        $commandCursors = $this->retry(function() use ($mongoCollection, $numCursors) {
            return $mongoCollection->parallelCollectionScan($numCursors);
        });

        return array_map([$this, 'wrapCommandCursor'], $commandCursors);
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
    public function remove(array $query, array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preRemove)) {
            $eventArgs = new MutableEventArgs($this, $query, $options);
            $this->eventManager->dispatchEvent(Events::preRemove, $eventArgs);
            $query = $eventArgs->getData();
            $options = $eventArgs->getOptions();
        }

        $result = $this->doRemove($query, $options);

        if ($this->eventManager->hasListeners(Events::postRemove)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postRemove, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
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
    public function save(array &$a, array $options = [])
    {
        if ($this->eventManager->hasListeners(Events::preSave)) {
            $eventArgs = new EventArgs($this, $a, $options);
            $this->eventManager->dispatchEvent(Events::preSave, $eventArgs);
            $a = $eventArgs->getData();
            $options = $eventArgs->getOptions();
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
    public function update($query, array $newObj, array $options = [])
    {
        if (is_scalar($query)) {
            trigger_error('Scalar $query argument for update() is deprecated', E_USER_DEPRECATED);
            $query = ['_id' => $query];
        }

        if ($this->eventManager->hasListeners(Events::preUpdate)) {
            $updateEventArgs = new UpdateEventArgs($this, $query, $newObj, $options);
            $this->eventManager->dispatchEvent(Events::preUpdate, $updateEventArgs);
            $query = $updateEventArgs->getQuery();
            $newObj = $updateEventArgs->getNewObj();
            $options = $updateEventArgs->getOptions();
        }

        $result = $this->doUpdate($query, $newObj, $options);

        if ($this->eventManager->hasListeners(Events::postUpdate)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postUpdate, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
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
    public function upsert($query, array $newObj, array $options = [])
    {
        $options['upsert'] = true;
        return $this->update($query, $newObj, $options);
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
        return $this->mongoCollection->validate($scanData);
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
        return $this->mongoCollection->__get($name);
    }

    /**
     * Wrapper method for MongoCollection::__toString().
     *
     * @see http://www.php.net/manual/en/mongocollection.--tostring.php
     * @return string
     */
    public function __toString()
    {
        return $this->mongoCollection->__toString();
    }

    /**
     * Execute the aggregate command.
     *
     * @see Collection::aggregate()
     * @param array $pipeline
     * @param array $options
     * @return Iterator
     */
    protected function doAggregate(array $pipeline, array $options = [])
    {
        if (isset($options['cursor']) && ($options['cursor'] || is_array($options['cursor']))) {
            return $this->doAggregateCursor($pipeline, $options);
        }

        unset($options['cursor']);

        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        $command = [];
        $command['aggregate'] = $this->mongoCollection->getName();
        $command['pipeline'] = $pipeline;
        $command = array_merge($command, $commandOptions);

        $database = $this->database;
        $result = $this->retry(function() use ($database, $command, $clientOptions) {
            return $database->command($command, $clientOptions);
        });

        if (empty($result['ok'])) {
            throw new ResultException($result);
        }

        /* If the pipeline ends with an $out operator, return a cursor on that
         * collection so a table scan may be performed.
         */
        if (isset($pipeline[count($pipeline) - 1]['$out'])) {
            $outputCollection = $pipeline[count($pipeline) - 1]['$out'];

            return $database->selectCollection($outputCollection)->find();
        }

        $arrayIterator = new ArrayIterator(isset($result['result']) ? $result['result'] : []);
        $arrayIterator->setCommandResult($result);

        return $arrayIterator;
    }

    /**
     * Executes the aggregate command and returns a MongoCommandCursor.
     *
     * @param array $pipeline
     * @param array $options
     * @return CommandCursor
     * @throws BadMethodCallException if MongoCollection::aggregateCursor() is not available
     */
    protected function doAggregateCursor(array $pipeline, array $options = [])
    {
        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        if (is_scalar($commandOptions['cursor'])) {
            unset($commandOptions['cursor']);
        }

        $timeout = isset($clientOptions['socketTimeoutMS'])
            ? $clientOptions['socketTimeoutMS']
            : (isset($clientOptions['timeout']) ? $clientOptions['timeout'] : null);

        $mongoCollection = $this->mongoCollection;
        $commandCursor = $this->retry(function() use ($mongoCollection, $pipeline, $commandOptions) {
            return $mongoCollection->aggregateCursor($pipeline, $commandOptions);
        });

        $commandCursor = $this->wrapCommandCursor($commandCursor);

        if (isset($timeout)) {
            $commandCursor->timeout($timeout);
        }

        return $commandCursor;
    }

    /**
     * Execute the batchInsert query.
     *
     * @see Collection::batchInsert()
     * @param array $a
     * @param array $options
     * @return array|boolean
     */
    protected function doBatchInsert(array &$a, array $options = [])
    {
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;
        return $this->mongoCollection->batchInsert($a, $options);
    }

    /**
     * Execute the count command.
     *
     * @see Collection::count()
     * @param array $query
     * @param array $options
     * @return integer
     * @throws ResultException if the command fails or omits the result field
     */
    protected function doCount(array $query, array $options)
    {
        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        $command = [];
        $command['count'] = $this->mongoCollection->getName();
        $command['query'] = (object) $query;
        $command = array_merge($command, $commandOptions);

        $database = $this->database;
        $result = $this->retry(function() use ($database, $command, $clientOptions) {
            return $database->command($command, $clientOptions);
        });

        if (empty($result['ok']) || ! isset($result['n'])) {
            throw new ResultException($result);
        }

        return (integer) $result['n'];
    }

    /**
     * Execute the distinct command.
     *
     * @see Collection::distinct()
     * @param string $field
     * @param array  $query
     * @param array  $options
     * @return ArrayIterator
     * @throws ResultException if the command fails
     */
    protected function doDistinct($field, array $query, array $options)
    {
        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        $command = [];
        $command['distinct'] = $this->mongoCollection->getName();
        $command['key'] = $field;
        $command['query'] = (object) $query;
        $command = array_merge($command, $commandOptions);

        $database = $this->database;
        $result = $this->retry(function() use ($database, $command, $clientOptions) {
            return $database->command($command, $clientOptions);
        });

        if (empty($result['ok'])) {
            throw new ResultException($result);
        }

        $arrayIterator = new ArrayIterator(isset($result['values']) ? $result['values'] : []);
        $arrayIterator->setCommandResult($result);

        return $arrayIterator;
    }

    /**
     * Drops the collection.
     *
     * @see Collection::drop()
     * @return array
     */
    protected function doDrop()
    {
        return $this->mongoCollection->drop();
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
        $mongoCollection = $this->mongoCollection;
        $cursor = $this->retry(function() use ($mongoCollection, $query, $fields) {
            return $mongoCollection->find($query, $fields);
        });
        return $this->wrapCursor($cursor, $query, $fields);
    }

    /**
     * Execute the findAndModify command with the remove option.
     *
     * @see Collection::findAndRemove()
     * @param array $query
     * @param array $options
     * @return array|null
     * @throws ResultException if the command fails
     */
    protected function doFindAndRemove(array $query, array $options = [])
    {
        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        $command = [];
        $command['findandmodify'] = $this->mongoCollection->getName();
        $command['query'] = (object) $query;
        $command['remove'] = true;
        $command = array_merge($command, $commandOptions);

        $result = $this->database->command($command, $clientOptions);

        if (empty($result['ok'])) {
            throw new ResultException($result);
        }

        return isset($result['value']) ? $result['value'] : null;
    }

    /**
     * Execute the findAndModify command with the update option.
     *
     * @see Collection::findAndUpdate()
     * @param array $query
     * @param array $newObj
     * @param array $options
     * @return array|null
     * @throws ResultException if the command fails
     */
    protected function doFindAndUpdate(array $query, array $newObj, array $options)
    {
        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        $command = [];
        $command['findandmodify'] = $this->mongoCollection->getName();
        $command['query'] = (object) $query;
        $command['update'] = (object) $newObj;
        $command = array_merge($command, $commandOptions);

        $result = $this->database->command($command, $clientOptions);

        if (empty($result['ok'])) {
            throw new ResultException($result);
        }

        return isset($result['value']) ? $result['value'] : null;
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
        $mongoCollection = $this->mongoCollection;
        return $this->retry(function() use ($mongoCollection, $query, $fields) {
            return $mongoCollection->findOne($query, $fields);
        });
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
        $mongoCollection = $this->mongoCollection;
        return $this->retry(function() use ($mongoCollection, $reference) {
            return $mongoCollection->getDBRef($reference);
        });
    }

    /**
     * Execute the group command.
     *
     * @see Collection::group()
     * @param array|string|\MongoCode $keys
     * @param array                   $initial
     * @param string|\MongoCode       $reduce
     * @param array                   $options
     * @return ArrayIterator
     * @throws ResultException if the command fails
     */
    protected function doGroup($keys, array $initial, $reduce, array $options)
    {
        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        $command = [];
        $command['ns'] = $this->mongoCollection->getName();
        $command['initial'] = (object) $initial;
        $command['$reduce'] = $reduce;

        if (is_string($keys) || $keys instanceof \MongoCode) {
            $command['$keyf'] = $keys;
        } else {
            $command['key'] = $keys;
        }

        $command = array_merge($command, $commandOptions);

        foreach (['$keyf', '$reduce', 'finalize'] as $key) {
            if (isset($command[$key]) && is_string($command[$key])) {
                $command[$key] = new \MongoCode($command[$key]);
            }
        }

        if (isset($command['cond']) && is_array($command['cond'])) {
            $command['cond'] = (object) $command['cond'];
        }

        $database = $this->database;
        $result = $this->retry(function() use ($database, $command, $clientOptions) {
            return $database->command(['group' => $command], $clientOptions);
        });

        if (empty($result['ok'])) {
            throw new ResultException($result);
        }

        $arrayIterator = new ArrayIterator(isset($result['retval']) ? $result['retval'] : []);
        $arrayIterator->setCommandResult($result);

        return $arrayIterator;
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
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;
        $result = $this->mongoCollection->insert($document, $options);
        if (isset($document['_id'])) {
            $a['_id'] = $document['_id'];
        }
        return $result;
    }

    /**
     * Execute the mapReduce command.
     *
     * @see Collection::mapReduce()
     * @param string|\MongoCode $map
     * @param string|\MongoCode $reduce
     * @param array|string      $out
     * @param array             $query
     * @param array             $options
     * @return ArrayIterator
     * @throws ResultException if the command fails
     */
    protected function doMapReduce($map, $reduce, $out, array $query, array $options)
    {
        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        $command = [];
        $command['mapreduce'] = $this->mongoCollection->getName();
        $command['map'] = $map;
        $command['reduce'] = $reduce;
        $command['query'] = (object) $query;
        $command['out'] = $out;
        $command = array_merge($command, $commandOptions);

        foreach (['map', 'reduce', 'finalize'] as $key) {
            if (isset($command[$key]) && is_string($command[$key])) {
                $command[$key] = new \MongoCode($command[$key]);
            }
        }

        $result = $this->database->command($command, $clientOptions);

        if (empty($result['ok'])) {
            throw new ResultException($result);
        }

        if (isset($result['result']) && is_string($result['result'])) {
            return $this->database->selectCollection($result['result'])->find();
        }

        if (isset($result['result']) && is_array($result['result']) &&
            isset($result['result']['db'], $result['result']['collection'])) {
            return $this->database->getConnection()
                ->selectCollection($result['result']['db'], $result['result']['collection'])
                ->find();
        }

        $arrayIterator = new ArrayIterator(isset($result['results']) ? $result['results'] : []);
        $arrayIterator->setCommandResult($result);

        return $arrayIterator;
    }

    /**
     * Execute the geoNear command.
     *
     * @see Collection::near()
     * @param array|Point $near
     * @param array       $query
     * @param array       $options
     * @return ArrayIterator
     * @throws ResultException if the command fails
     */
    protected function doNear($near, array $query, array $options)
    {
        if ($near instanceof Point) {
            $near = $near->jsonSerialize();
        }

        list($commandOptions, $clientOptions) = isset($options['socketTimeoutMS']) || isset($options['timeout'])
            ? $this->splitCommandAndClientOptions($options)
            : [$options, []];

        $command = [];
        $command['geoNear'] = $this->mongoCollection->getName();
        $command['near'] = $near;
        $command['spherical'] = isset($near['type']);
        $command['query'] = (object) $query;
        $command = array_merge($command, $commandOptions);

        $database = $this->database;
        $result = $this->retry(function() use ($database, $command, $clientOptions) {
            return $database->command($command, $clientOptions);
        });

        if (empty($result['ok'])) {
            throw new ResultException($result);
        }

        $arrayIterator = new ArrayIterator(isset($result['results']) ? $result['results'] : []);
        $arrayIterator->setCommandResult($result);

        return $arrayIterator;
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
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;
        return $this->mongoCollection->remove($query, $options);
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
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;
        return $this->mongoCollection->save($a, $options);
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
    protected function doUpdate(array $query, array $newObj, array $options)
    {
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        $options = isset($options['wtimeout']) ? $this->convertWriteTimeout($options) : $options;
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        /* Allow "multi" to be used instead of "multiple", as it's accepted in
         * the MongoDB shell and other (non-PHP) drivers.
         */
        if (isset($options['multi']) && ! isset($options['multiple'])) {
            $options['multiple'] = $options['multi'];
            unset($options['multi']);
        }

        return $this->mongoCollection->update($query, $newObj, $options);
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
        if ($this->numRetries < 1) {
            return $retry();
        }

        $firstException = null;

        for ($i = 0; $i <= $this->numRetries; $i++) {
            try {
                return $retry();
            } catch (\MongoException $e) {
                if ($firstException === null) {
                    $firstException = $e;
                }
                if ($i === $this->numRetries) {
                    throw $firstException;
                }
            }
        }
    }

    /**
     * Wraps a MongoCommandCursor instance with a CommandCursor.
     *
     * @param \MongoCommandCursor $commandCursor
     * @return CommandCursor
     */
    protected function wrapCommandCursor(\MongoCommandCursor $commandCursor)
    {
        return new CommandCursor($commandCursor, $this->numRetries);
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
        return new Cursor($this, $cursor, $query, $fields, $this->numRetries);
    }

    /**
     * Converts "safe" write option to "w" for driver versions 1.3.0+.
     *
     * @param array $options
     * @return array
     */
    protected function convertWriteConcern(array $options)
    {
        if (isset($options['safe']) && ! isset($options['w'])) {
            $options['w'] = is_bool($options['safe']) ? (integer) $options['safe'] : $options['safe'];
            unset($options['safe']);
        }

        return $options;
    }

    /**
     * Convert "wtimeout" write option to "wTimeoutMS" for driver version
     * 1.5.0+.
     *
     * @param array $options
     * @return array
     */
    protected function convertWriteTimeout(array $options)
    {
        if (isset($options['wtimeout']) && ! isset($options['wTimeoutMS'])) {
            $options['wTimeoutMS'] = $options['wtimeout'];
            unset($options['wtimeout']);
        }

        return $options;
    }

    /**
     * Convert "timeout" write option to "socketTimeoutMS" for driver version
     * 1.5.0+.
     *
     * @param array $options
     * @return array
     */
    protected function convertSocketTimeout(array $options)
    {
        if (isset($options['timeout']) && ! isset($options['socketTimeoutMS'])) {
            $options['socketTimeoutMS'] = $options['timeout'];
            unset($options['timeout']);
        }

        return $options;
    }

    /**
     * Splits a command helper's options array into command and client options.
     *
     * Command options are intended to be merged into the command document.
     * Client options (e.g. socket timeout) are for {@link Database::command()}.
     *
     * @param array $options
     * @return array Tuple of command options and client options
     */
    protected function splitCommandAndClientOptions(array $options)
    {
        $keys = ['socketTimeoutMS' => 1, 'timeout' => 1];

        return [
            array_diff_key($options, $keys),
            array_intersect_key($options, $keys),
        ];
    }
}
