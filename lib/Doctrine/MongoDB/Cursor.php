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
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Cursor implements Iterator
{
    /** The Doctrine Connection */
    protected $connection;

    /** The Doctrine Collection */
    protected $collection;

    /** The PHP MongoCursor being wrapped */
    protected $mongoCursor;

    /** Number of times to try operations on the cursor */
    protected $numRetries;
    protected $query = array();
    protected $fields = array();
    protected $hints = array();
    protected $immortal;
    protected $options = array();
    protected $batchSize;
    protected $limit;
    protected $skip;
    protected $slaveOkay;
    protected $snapshot;
    protected $sorts = array();
    protected $tailable;
    protected $timeout;

    /**
     * Create a new Cursor, which wraps around a given PHP MongoCursor.
     *
     * @param Connection  $connection   Connection used to create this cursor
     * @param Collection  $collection   Collection used to create this cursor
     * @param MongoCursor $mongoCursor  Cursor being wrapped
     * @param array       $query        Query criteria
     * @param array       $fields       Selected fields (projection)
     * @param integer     $numRetries   Number of times to retry queries
     */
    public function __construct(Connection $connection, Collection $collection, \MongoCursor $mongoCursor, array $query = array(), array $fields = array(), $numRetries = 0)
    {
        $this->connection = $connection;
        $this->collection = $collection;
        $this->mongoCursor = $mongoCursor;
        $this->query = $query;
        $this->fields = $fields;
        $this->numRetries = (integer) $numRetries;
    }

    /**
     * Return the database connection for this cursor.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
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
     * Return the query criteria.
     *
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
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
     * Recreates the internal MongoCursor.
     */
    public function recreate()
    {
        $this->mongoCursor = $this->collection->getMongoCollection()->find($this->query, $this->fields);
        foreach ($this->hints as $hint) {
            $this->mongoCursor->hint($hint);
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
        if ($this->snapshot) {
            $this->mongoCursor->snapshot();
        }
        foreach ($this->sorts as $sort) {
            $this->mongoCursor->sort($sort);
        }
        if ($this->tailable !== null) {
            $this->mongoCursor->tailable($this->tailable);
        }
        if ($this->timeout !== null) {
            $this->mongoCursor->timeout($this->timeout);
        }
    }

    /**
     * Returns the MongoCursor instance being wrapped.
     *
     * @return \MongoCursor $mongoCursor
     */
    public function getMongoCursor()
    {
        return $this->mongoCursor;
    }

    /**
     * @see \Iterator::current()
     * @see \MongoCursor::current()
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
     * @see \Iterator::key()
     * @see \MongoCursor::dead()
     */
    public function key()
    {
        return $this->mongoCursor->key();
    }

    /**
     * @see \MongoCursor::dead()
     */
    public function dead()
    {
        return $this->mongoCursor->dead();
    }

    /**
     * @see \MongoCursor::explain()
     */
    public function explain()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->explain();
        }, true);
    }

    /**
     * @see \MongoCursor::fields()
     */
    public function fields(array $f)
    {
        $this->fields = $f;
        $this->mongoCursor->fields($f);
        return $this;
    }

    /**
     * @see \MongoCursor::getNext()
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
     * @see \MongoCursor::hasNext()
     */
    public function hasNext()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->hasNext();
        }, false);
    }

    /**
     * @see \MongoCursor::hint()
     */
    public function hint(array $keyPattern)
    {
        $this->hints[] = $keyPattern;
        $this->mongoCursor->hint($keyPattern);
        return $this;
    }

    /**
     * @see \MongoCursor::immortal()
     */
    public function immortal($liveForever = true)
    {
        $liveForever = (boolean) $liveForever;
        $this->immortal = $liveForever;
        $this->mongoCursor->immortal($liveForever);
        return $this;
    }

    /**
     * @see \MongoCursor::info()
     */
    public function info()
    {
        return $this->mongoCursor->info();
    }

    /**
     * @see \Iterator::rewind()
     * @see \MongoCursor::rewind()
     */
    public function rewind()
    {
        $cursor = $this;
        $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->rewind();
        }, false);
    }

    /**
     * @see \Iterator::next()
     * @see \MongoCursor::next()
     */
    public function next()
    {
        $cursor = $this;
        $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->next();
        }, false);
    }

    /**
     * @see \MongoCursor::reset()
     */
    public function reset()
    {
        $this->mongoCursor->reset();
    }

    /**
     * @see \Countable::count()
     * @see \MongoCursor::count()
     */
    public function count($foundOnly = false)
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor, $foundOnly) {
            return $cursor->getMongoCursor()->count($foundOnly);
        }, true);
    }

    /**
     * @see \MongoCursor::addOption()
     */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;
        $this->mongoCursor->addOption($key, $value);
        return $this;
    }

    /**
     * @see \MongoCursor::batchSize()
     */
    public function batchSize($num)
    {
        $limit = (integer) $num;
        $this->batchSize = $num;
        $this->mongoCursor->batchSize($num);
        return $this;
    }

    /**
     * @see \MongoCursor::limit()
     */
    public function limit($num)
    {
        $limit = (integer) $num;
        $this->limit = $num;
        $this->mongoCursor->limit($num);
        return $this;
    }

    /**
     * @see \MongoCursor::skip()
     */
    public function skip($num)
    {
        $num = (integer) $num;
        $this->skip = $num;
        $this->mongoCursor->skip($num);
        return $this;
    }

    /**
     * @see \MongoCursor::slaveOkay()
     */
    public function slaveOkay($ok = true)
    {
        $ok = (boolean) $ok;
        $this->slaveOkay = $ok;
        $this->setMongoCursorSlaveOkay($ok);
        return $this;
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
     * @see \MongoCursor::snapshot()
     */
    public function snapshot()
    {
        $this->snapshot = true;
        $this->mongoCursor->snapshot();
        return $this;
    }

    /**
     * @see \MongoCursor::sort()
     */
    public function sort($fields)
    {
        foreach ($fields as $fieldName => $order) {
            if (is_string($order)) {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }
            $fields[$fieldName] = (integer) $order;
        }
        $this->sorts[] = $fields;
        $this->mongoCursor->sort($fields);
        return $this;
    }

    /**
     * @see \MongoCursor::tailable()
     */
    public function tailable($tail = true)
    {
        $tail = (boolean) $tail;
        $this->tailable = $tail;
        $this->mongoCursor->tailable($tail);
        return $this;
    }

    /**
     * @see \MongoCursor::timeout()
     */
    public function timeout($ms)
    {
        $this->timeout = (integer) $ms;
        $this->mongoCursor->timeout($ms);
        return $this;
    }

    /**
     * @see \Iterator::valid()
     * @see \MongoCursor::valid()
     */
    public function valid()
    {
        return $this->mongoCursor->valid();
    }

    /**
     * @see Iterator::toArray()
     */
    public function toArray($useKeys = true)
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor, $useKeys) {
            return iterator_to_array($cursor, $useKeys);
        }, true);
    }

    /**
     * @see Iterator::getSingleResult()
     */
    public function getSingleResult()
    {
        $originalLimit = $this->limit;
        $this->limit(1);
        $result = current($this->toArray(false)) ?: null;
        $this->reset();
        $this->limit($originalLimit);
        return $result;
    }

    protected function retry(\Closure $retry, $recreate = false)
    {
        if ($this->numRetries) {
            $firstException = null;
            for ($i = 0; $i <= $this->numRetries; $i++) {
                $reconnect = false;
                try {
                    return $retry();
                } catch (\MongoCursorTimeoutException $e) {
                } catch (\MongoCursorException $e) {
                } catch (\MongoConnectionException $e) {
                    $reconnect = true;
                }
                if (isset($e)) {
                    if (!$firstException) {
                        $firstException = $e;
                    }
                    if ($i === $this->numRetries) {
                        throw $firstException;
                    }
                    if ($recreate) {
                        if ($reconnect) {
                            $this->connection->initialize(true);
                        }
                        $this->recreate();
                    }
                }
            }
        } else {
            return $retry();
        }
    }
}
