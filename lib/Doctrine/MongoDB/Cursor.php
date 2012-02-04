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

/**
 * Wrapper for the PHP MongoCursor class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
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
    protected $immortal = false;
    protected $options = array();
    protected $batchSize;
    protected $limit;
    protected $skip;
    protected $slaveOkay = false;
    protected $snapshot;
    protected $sorts = array();
    protected $tailable = false;
    protected $timeout;

    /**
     * Create a new MongoCursor which wraps around a given PHP MongoCursor.
     *
     * @param Connection $connection The Doctrine Connection instance.
     * @param Collection $collection The Doctrine Collection that created this cursor.
     * @param MongoCursor $mongoCursor The cursor being wrapped.
     * @param array $query Query object for this cursor.
     * @param array $fields Fields to select for this cursor.
     * @param boolean|integer $numRetries Number of times to retry queries.
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

    public function getConnection()
    {
        return $this->connection;
    }

    public function getCollection()
    {
        return $this->collection;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function recreate()
    {
        $this->mongoCursor = $this->collection->getMongoCollection()->find($this->query, $this->fields);
        foreach ($this->hints as $hint) {
            $this->mongoCursor->hint($hint);
        }
        $this->mongoCursor->immortal($this->immortal);
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
        $this->mongoCursor->slaveOkay($this->slaveOkay);
        if ($this->snapshot) {
            $this->mongoCursor->snapshot();
        }
        foreach ($this->sorts as $sort) {
            $this->mongoCursor->sort($sort);
        }
        $this->mongoCursor->tailable($this->tailable);
        if ($this->timeout !== null) {
            $this->mongoCursor->timeout($this->timeout);
        }
    }

    /**
     * Returns the MongoCursor instance being wrapped.
     *
     * @return MongoCursor $mongoCursor The MongoCursor instance being wrapped.
     */
    public function getMongoCursor()
    {
        return $this->mongoCursor;
    }

    /** @proxy */
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

    /** @proxy */
    public function key()
    {
        return $this->mongoCursor->key();
    }

    /** @proxy */
    public function dead()
    {
        return $this->mongoCursor->dead();
    }

    /** @proxy */
    public function explain()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->explain();
        }, true);
    }

    /** @proxy */
    public function fields(array $f)
    {
        $this->fields = $f;
        $this->mongoCursor->fields($f);
        return $this;
    }

    /** @proxy */
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

    /** @proxy */
    public function hasNext()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->hasNext();
        }, false);
    }

    /** @proxy */
    public function hint(array $keyPattern)
    {
        $this->hints[] = $keyPattern;
        $this->mongoCursor->hint($keyPattern);
        return $this;
    }

    /** @proxy */
    public function immortal($liveForever = true)
    {
        $this->immortal = $liveForever;
        $this->mongoCursor->immortal($liveForever);
        return $this;
    }

    /** @proxy */
    public function info()
    {
        return $this->mongoCursor->info();
    }

    /** @proxy */
    public function rewind()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->rewind();
        }, false);
    }


    /** @proxy */
    public function next()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->next();
        }, false);
    }

    /** @proxy */
    public function reset()
    {
        return $this->mongoCursor->reset();
    }

    /** @proxy */
    public function count($foundOnly = false)
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor, $foundOnly) {
            return $cursor->getMongoCursor()->count($foundOnly);
        }, true);
    }

    /** @proxy */
    public function addOption($key, $value)
    {
        $this->options[$key] = $value;
        $this->mongoCursor->addOption($key, $value);
        return $this;
    }

    /** @proxy */
    public function batchSize($num)
    {
        $this->batchSize = $num;
        $this->mongoCursor->batchSize($num);
        return $this;
    }

    /** @proxy */
    public function limit($num)
    {
        $this->limit = $num;
        $this->mongoCursor->limit($num);
        return $this;
    }

    /** @proxy */
    public function skip($num)
    {
        $this->skip = $num;
        $this->mongoCursor->skip($num);
        return $this;
    }

    /** @proxy */
    public function slaveOkay($okay = true)
    {
        $this->slaveOkay = $okay;
        $this->mongoCursor->slaveOkay($okay);
        return $this;
    }

    /** @proxy */
    public function snapshot()
    {
        $this->snapshot = true;
        $this->mongoCursor->snapshot();
        return $this;
    }

    /** @proxy */
    public function sort($fields)
    {
        foreach ($fields as $fieldName => $order) {
            if (is_string($order)) {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }
            $order = (int) $order;
            $fields[$fieldName] = $order;
        }
        $this->sorts[] = $fields;
        $this->mongoCursor->sort($fields);
        return $this;
    }

    /** @proxy */
    public function tailable($tail = true)
    {
        $this->tailable = $tail;
        $this->mongoCursor->tailable($tail);
        return $this;
    }

    /** @proxy */
    public function timeout($ms)
    {
        $this->timeout = $ms;
        $this->mongoCursor->timeout($ms);
        return $this;
    }

    /** @proxy */
    public function valid()
    {
        return $this->mongoCursor->valid();
    }

    public function toArray()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return iterator_to_array($cursor);
        }, true);
    }

    /**
     * Get the first single result from the cursor.
     *
     * @return array $document  The single document.
     */
    public function getSingleResult()
    {
        $originalLimit = $this->limit;
        $this->limit(1);
        $results = $this->toArray();
        return $results ? current($results) : null;
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