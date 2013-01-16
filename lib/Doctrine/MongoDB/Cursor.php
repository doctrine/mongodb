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
     * @return MongoCursor $mongoCursor The MongoCursor instance being wrapped.
     */
    public function getMongoCursor()
    {
        return $this->mongoCursor;
    }

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

    public function key()
    {
        return $this->mongoCursor->key();
    }

    public function dead()
    {
        return $this->mongoCursor->dead();
    }

    public function explain()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->explain();
        }, true);
    }

    public function fields(array $f)
    {
        $this->fields = $f;
        $this->mongoCursor->fields($f);
        return $this;
    }

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

    public function hasNext()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->hasNext();
        }, false);
    }

    public function hint(array $keyPattern)
    {
        $this->hints[] = $keyPattern;
        $this->mongoCursor->hint($keyPattern);
        return $this;
    }

    public function immortal($liveForever = true)
    {
        $liveForever = (boolean) $liveForever;
        $this->immortal = $liveForever;
        $this->mongoCursor->immortal($liveForever);
        return $this;
    }

    public function info()
    {
        return $this->mongoCursor->info();
    }

    public function rewind()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->rewind();
        }, false);
    }

    public function next()
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCursor()->next();
        }, false);
    }

    public function reset()
    {
        return $this->mongoCursor->reset();
    }

    public function count($foundOnly = false)
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor, $foundOnly) {
            return $cursor->getMongoCursor()->count($foundOnly);
        }, true);
    }

    public function addOption($key, $value)
    {
        $this->options[$key] = $value;
        $this->mongoCursor->addOption($key, $value);
        return $this;
    }

    public function batchSize($num)
    {
        $limit = (integer) $num;
        $this->batchSize = $num;
        $this->mongoCursor->batchSize($num);
        return $this;
    }

    public function limit($num)
    {
        $limit = (integer) $num;
        $this->limit = $num;
        $this->mongoCursor->limit($num);
        return $this;
    }

    public function skip($num)
    {
        $num = (integer) $num;
        $this->skip = $num;
        $this->mongoCursor->skip($num);
        return $this;
    }

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

    public function snapshot()
    {
        $this->snapshot = true;
        $this->mongoCursor->snapshot();
        return $this;
    }

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

    public function tailable($tail = true)
    {
        $tail = (boolean) $tail;
        $this->tailable = $tail;
        $this->mongoCursor->tailable($tail);
        return $this;
    }

    public function timeout($ms)
    {
        $this->timeout = (integer) $ms;
        $this->mongoCursor->timeout($ms);
        return $this;
    }

    public function valid()
    {
        return $this->mongoCursor->valid();
    }

    public function toArray($useKeys = true)
    {
        $cursor = $this;
        return $this->retry(function() use ($cursor, $useKeys) {
            return iterator_to_array($cursor, $useKeys);
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
        $result = current($this->toArray()) ?: null;
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
