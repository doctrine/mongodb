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

use Doctrine\MongoDB\Util\ReadPreference;

/**
 * Wrapper for the PHP MongoCursor class.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Cursor implements Iterator
{
    /**
     * The Collection instance used for recreating this cursor.
     *
     * This is also used to access the Connection for reinitializing during
     * retry attempts.
     *
     * @var Collection
     */
    protected $collection;

    /**
     * The MongoCursor instance being wrapped.
     *
     * @var \MongoCursor
     */
    protected $mongoCursor;

    /**
     * Number of times to retry queries.
     *
     * @var integer
     */
    protected $numRetries;

    protected $query = array();
    protected $fields = array();
    protected $hint;
    protected $immortal;
    protected $options = array();
    protected $batchSize;
    protected $limit;
    protected $readPreference;
    protected $readPreferenceTags;
    protected $skip;
    protected $slaveOkay;
    protected $snapshot;
    protected $sort;
    protected $tailable;
    protected $timeout;

    /**
     * Constructor.
     *
     * The wrapped MongoCursor instance may change if the cursor is recreated.
     *
     * @param Collection   $collection  Collection used to create this Cursor
     * @param \MongoCursor $mongoCursor MongoCursor instance being wrapped
     * @param array        $query       Query criteria
     * @param array        $fields      Selected fields (projection)
     * @param integer      $numRetries  Number of times to retry queries
     */
    public function __construct(Collection $collection, \MongoCursor $mongoCursor, array $query = array(), array $fields = array(), $numRetries = 0)
    {
        $this->collection = $collection;
        $this->mongoCursor = $mongoCursor;
        $this->query = $query;
        $this->fields = $fields;
        $this->numRetries = (integer) $numRetries;
    }

    /**
     * Wrapper method for MongoCursor::addOption().
     *
     * @see http://php.net/manual/en/mongocursor.addoption.php
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;
        $this->mongoCursor->addOption($key, $value);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::batchSize().
     *
     * @see http://php.net/manual/en/mongocursor.batchsize.php
     * @param integer $num
     * @return self
     */
    public function batchSize($num)
    {
        $num = (integer) $num;
        $this->batchSize = $num;
        $this->mongoCursor->batchSize($num);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::count().
     *
     * @see http://php.net/manual/en/countable.count.php
     * @see http://php.net/manual/en/mongocursor.count.php
     * @param boolean $foundOnly
     * @return integer
     */
    public function count($foundOnly = false)
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor, $foundOnly) {
            return $cursor->getMongoCursor()->count($foundOnly);
        }, true);
    }

    /**
     * Wrapper method for MongoCursor::current().
     *
     * @see http://php.net/manual/en/iterator.current.php
     * @see http://php.net/manual/en/mongocursor.current.php
     * @return array|null
     */
    public function current()
    {
        $current = $this->mongoCursor->current();
        if ($current instanceof \MongoGridFSFile) {
            $document = $current->file;
            $document['file'] = new GridFSFile($current);
            $current = $document;
        }
        return $current;
    }

    /**
     * Wrapper method for MongoCursor::dead().
     *
     * @see http://php.net/manual/en/mongocursor.dead.php
     * @return boolean
     */
    public function dead()
    {
        return $this->mongoCursor->dead();
    }

    /**
     * Wrapper method for MongoCursor::explain().
     *
     * @see http://php.net/manual/en/mongocursor.explain.php
     * @return array
     */
    public function explain()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->explain();
        }, true);
    }

    /**
     * Wrapper method for MongoCursor::fields().
     *
     * @see http://php.net/manual/en/mongocursor.fields.php
     * @return self
     */
    public function fields(array $f)
    {
        $this->fields = $f;
        $this->mongoCursor->fields($f);
        return $this;
    }

    /**
     * Return the collection for this cursor.
     *
     * @return Collection
     */
    public function getCollection()
    {
        return $this->collection;
    }

    /**
     * Return the connection for this cursor.
     *
     * @deprecated 1.1 Will be removed for 2.0
     * @return Connection
     */
    public function getConnection()
    {
        return $this->collection->getDatabase()->getConnection();
    }

    /**
     * Return the selected fields (projection).
     *
     * @return array
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Returns the MongoCursor instance being wrapped.
     *
     * @return \MongoCursor
     */
    public function getMongoCursor()
    {
        return $this->mongoCursor;
    }

    /**
     * Wrapper method for MongoCursor::getNext().
     *
     * @see http://php.net/manual/en/mongocursor.getnext.php
     * @return array|null
     */
    public function getNext()
    {
        $cursor = $this;
        $next = $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->getNext();
        }, false);
        if ($next instanceof \MongoGridFSFile) {
            $document = $next->file;
            $document['file'] = new GridFSFile($next);
            $next = $document;
        }
        return $next;
    }

    /**
     * Return the query criteria.
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Wrapper method for MongoCursor::getReadPreference().
     *
     * @see http://php.net/manual/en/mongocursor.getreadpreference.php
     * @return array
     */
    public function getReadPreference()
    {
        return $this->mongoCursor->getReadPreference();
    }

    /**
     * Set the read preference.
     *
     * @see http://php.net/manual/en/mongocursor.setreadpreference.php
     * @param string $readPreference
     * @param array  $tags
     * @return self
     */
    public function setReadPreference($readPreference, array $tags = null)
    {
        if ($tags !== null) {
            $this->mongoCursor->setReadPreference($readPreference, $tags);
        } else {
            $this->mongoCursor->setReadPreference($readPreference);
        }

        $this->readPreference = $readPreference;
        $this->readPreferenceTags = $tags;

        return $this;
    }

    /**
     * Reset the cursor and return its first result.
     *
     * The cursor will be reset both before and after the single result is
     * fetched. The original cursor limit (if any) will remain in place.
     *
     * @see Iterator::getSingleResult()
     * @return array|object|null
     */
    public function getSingleResult()
    {
        $originalLimit = $this->limit;
        $this->reset();
        $this->limit(1);
        $result = current($this->toArray(false)) ?: null;
        $this->reset();
        $this->limit($originalLimit);
        return $result;
    }

    /**
     * Wrapper method for MongoCursor::hasNext().
     *
     * @see http://php.net/manual/en/mongocursor.hasnext.php
     * @return boolean
     */
    public function hasNext()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->hasNext();
        }, false);
    }

    /**
     * Wrapper method for MongoCursor::hint().
     *
     * @see http://php.net/manual/en/mongocursor.hint.php
     * @param array|string $keyPattern
     * @return self
     */
    public function hint($keyPattern)
    {
        $this->hint = $keyPattern;
        $this->mongoCursor->hint($keyPattern);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::immortal().
     *
     * @see http://php.net/manual/en/mongocursor.immortal.php
     * @param boolean $liveForever
     * @return self
     */
    public function immortal($liveForever = true)
    {
        $liveForever = (boolean) $liveForever;
        $this->immortal = $liveForever;
        $this->mongoCursor->immortal($liveForever);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::info().
     *
     * @see http://php.net/manual/en/mongocursor.info.php
     * @return array
     */
    public function info()
    {
        return $this->mongoCursor->info();
    }

    /**
     * Wrapper method for MongoCursor::key().
     *
     * @see http://php.net/manual/en/iterator.key.php
     * @see http://php.net/manual/en/mongocursor.key.php
     * @return string
     */
    public function key()
    {
        return $this->mongoCursor->key();
    }

    /**
     * Wrapper method for MongoCursor::limit().
     *
     * @see http://php.net/manual/en/mongocursor.limit.php
     * @param integer $num
     * @return self
     */
    public function limit($num)
    {
        $num = (integer) $num;
        $this->limit = $num;
        $this->mongoCursor->limit($num);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::next().
     *
     * @see http://php.net/manual/en/iterator.next.php
     * @see http://php.net/manual/en/mongocursor.next.php
     */
    public function next()
    {
        $cursor = $this;
        $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->next();
        }, false);
    }

    /**
     * Recreates the internal MongoCursor.
     */
    public function recreate()
    {
        $this->mongoCursor = $this->collection->getMongoCollection()->find($this->query, $this->fields);
        if ($this->hint !== null) {
            $this->mongoCursor->hint($this->hint);
        }
        if ($this->immortal !== null) {
            $this->mongoCursor->immortal($this->immortal);
        }
        foreach ($this->options as $key => $value) {
            $this->mongoCursor->addOption($key, $value);
        }
        if ($this->batchSize !== null) {
            $this->mongoCursor->batchSize($this->batchSize);
        }
        if ($this->limit !== null) {
            $this->mongoCursor->limit($this->limit);
        }
        if ($this->skip !== null) {
            $this->mongoCursor->skip($this->skip);
        }
        if ($this->slaveOkay !== null) {
            $this->setMongoCursorSlaveOkay($this->slaveOkay);
        }
        // Set read preferences after slaveOkay, since they may be more specific
        if ($this->readPreference !== null) {
            if ($this->readPreferenceTags !== null) {
                $this->mongoCursor->setReadPreference($this->readPreference, $this->readPreferenceTags);
            } else {
                $this->mongoCursor->setReadPreference($this->readPreference);
            }
        }
        if ($this->snapshot) {
            $this->mongoCursor->snapshot();
        }
        if ($this->sort !== null) {
            $this->mongoCursor->sort($this->sort);
        }
        if ($this->tailable !== null) {
            $this->mongoCursor->tailable($this->tailable);
        }
        if ($this->timeout !== null) {
            $this->mongoCursor->timeout($this->timeout);
        }
    }

    /**
     * Wrapper method for MongoCursor::reset().
     *
     * @see http://php.net/manual/en/iterator.reset.php
     * @see http://php.net/manual/en/mongocursor.reset.php
     */
    public function reset()
    {
        $this->mongoCursor->reset();
    }

    /**
     * Wrapper method for MongoCursor::rewind().
     *
     * @see http://php.net/manual/en/iterator.rewind.php
     * @see http://php.net/manual/en/mongocursor.rewind.php
     */
    public function rewind()
    {
        $cursor = $this;
        $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->rewind();
        }, false);
    }

    /**
     * Set whether secondary read queries are allowed for this cursor.
     *
     * This method wraps setSlaveOkay() for driver versions before 1.3.0. For
     * newer drivers, this method either wraps setReadPreference() method and
     * specifies SECONDARY_PREFERRED or does nothing, depending on whether
     * setReadPreference() exists.
     *
     * @param boolean $ok
     */
    public function setMongoCursorSlaveOkay($ok)
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            $this->mongoCursor->slaveOkay($ok);
            return;
        }

        /* MongoCursor::setReadPreference() may not exist until 1.4.0. Although
         * we could throw an exception here, it's more user-friendly to NOP.
         */
        if (!method_exists($this->mongoCursor, 'setReadPreference')) {
            return;
        }

        if ($ok) {
            // Preserve existing tags for non-primary read preferences
            $readPref = $this->mongoCursor->getReadPreference();
            $tags = !empty($readPref['tagsets']) ? ReadPreference::convertTagSets($readPref['tagsets']) : array();
            $this->mongoCursor->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, $tags);
        } else {
            $this->mongoCursor->setReadPreference(\MongoClient::RP_PRIMARY);
        }
    }

    /**
     * Wrapper method for MongoCursor::skip().
     *
     * @see http://php.net/manual/en/mongocursor.skip.php
     * @param integer $num
     * @return self
     */
    public function skip($num)
    {
        $num = (integer) $num;
        $this->skip = $num;
        $this->mongoCursor->skip($num);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::slaveOkay().
     *
     * @see http://php.net/manual/en/mongocursor.slaveokay.php
     * @param boolean $ok
     * @return self
     */
    public function slaveOkay($ok = true)
    {
        $ok = (boolean) $ok;
        $this->slaveOkay = $ok;
        $this->setMongoCursorSlaveOkay($ok);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::snapshot().
     *
     * @see http://php.net/manual/en/mongocursor.snapshot.php
     * @return self
     */
    public function snapshot()
    {
        $this->snapshot = true;
        $this->mongoCursor->snapshot();
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::sort().
     *
     * @see http://php.net/manual/en/mongocursor.sort.php
     * @param array $fields
     * @return self
     */
    public function sort($fields)
    {
        foreach ($fields as $fieldName => $order) {
            if (is_string($order)) {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }
            $fields[$fieldName] = (integer) $order;
        }
        $this->sort = $fields;
        $this->mongoCursor->sort($fields);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::tailable().
     *
     * @see http://php.net/manual/en/mongocursor.tailable.php
     * @param boolean $tail
     * @return self
     */
    public function tailable($tail = true)
    {
        $tail = (boolean) $tail;
        $this->tailable = $tail;
        $this->mongoCursor->tailable($tail);
        return $this;
    }

    /**
     * Wrapper method for MongoCursor::timeout().
     *
     * @see http://php.net/manual/en/mongocursor.timeout.php
     * @param integer $ms
     * @return self
     */
    public function timeout($ms)
    {
        $this->timeout = (integer) $ms;
        $this->mongoCursor->timeout($ms);
        return $this;
    }

    /**
     * Return the cursor's results as an array.
     *
     * If documents in the result set use BSON objects for their "_id", the
     * $useKeys parameter may be set to false to avoid errors attempting to cast
     * arrays (i.e. BSON objects) to string keys.
     *
     * @see Iterator::toArray()
     * @param boolean $useKeys
     * @return array
     */
    public function toArray($useKeys = true)
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor, $useKeys) {
            return iterator_to_array($cursor, $useKeys);
        }, true);
    }

    /**
     * Wrapper method for MongoCursor::valid().
     *
     * @see http://php.net/manual/en/iterator.valid.php
     * @see http://php.net/manual/en/mongocursor.valid.php
     * @return boolean
     */
    public function valid()
    {
        return $this->mongoCursor->valid();
    }

    /**
     * Conditionally retry a closure if it yields an exception.
     *
     * If the closure does not return successfully within the configured number
     * of retries, its first exception will be thrown.
     *
     * The $recreate parameter may be used to recreate the MongoCursor between
     * retry attempts.
     *
     * @param \Closure $retry
     * @param boolean $recreate
     * @return mixed
     */
    protected function retry(\Closure $retry, $recreate = false)
    {
        if ($this->numRetries < 1) {
            return $retry();
        }

        $firstException = null;

        for ($i = 0; $i <= $this->numRetries; $i++) {
            try {
                return $retry();
            } catch (\MongoCursorException $e) {
            } catch (\MongoConnectionException $e) {
            }

            if ($firstException === null) {
                $firstException = $e;
            }
            if ($i === $this->numRetries) {
                throw $firstException;
            }
            if ($recreate) {
                $this->recreate();
            }
        }
    }
}
