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
    /** The PHP MongoCursor being wrapped */
    protected $mongoCursor;

    /**
     * A callable for logging statements.
     *
     * @var mixed
     */
    protected $loggerCallable;

    /**
     * The query array that was used when creating this cursor.
     *
     * @var array
     */
    protected $query = array();

    /**
     * The array of fields that were selected when creating this cursor.
     *
     * @var array
     */
    protected $fields = array();

    /**
     * Create a new MongoCursor which wraps around a given PHP MongoCursor.
     *
     * @param MongoCursor $mongoCursor The cursor being wrapped.
     * @param mixed $loggerCallable Logger callable.
     * @param array $query The query array that was used to create this cursor.
     * @param array $query The fields selected on this cursor.
     */
    public function __construct(\MongoCursor $mongoCursor, $loggerCallable, array $query, array $fields)
    {
        $this->mongoCursor = $mongoCursor;
        $this->loggerCallable = $loggerCallable;
        $this->query = $query;
        $this->fields = $fields;
    }

    /**
     * Gets the query array that was used when creating this cursor.
     *
     * @return array $query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Gets the array of fields that were selected when creating this cursor.
     *
     * @return array $fields
     */
    public function getFields()
    {
        return $this->fields;
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

    /**
     * Log something using the configured logger callable.
     *
     * @param array $log The array of data to log.
     */
    public function log(array $log)
    {
        $log['query'] = $this->query;
        $log['fields'] = $this->fields;
        call_user_func_array($this->loggerCallable, array($log));
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
        return $this->mongoCursor->explain();
    }

    /** @proxy */
    public function fields(array $f)
    {
        $this->mongoCursor->fields($f);
        return $this;
    }

    /** @proxy */
    public function getNext()
    {
        $next = $this->mongoCursor->getNext();
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
        return $this->mongoCursor->hasNext();
    }

    /** @proxy */
    public function hint(array $keyPattern)
    {
        $this->mongoCursor->hint($keyPattern);
        return $this;
    }

    /** @proxy */
    public function immortal($liveForever = true)
    {
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
        return $this->mongoCursor->rewind();
    }

    /** @proxy */
    public function next()
    {
        return $this->mongoCursor->next();
    }

    /** @proxy */
    public function reset()
    {
        return $this->mongoCursor->reset();
    }

    /** @proxy */
    public function count($foundOnly = false)
    {
        return $this->mongoCursor->count($foundOnly);
    }

    /** @proxy */
    public function addOption($key, $value)
    {
        $this->mongoCursor->addOption($key, $value);
        return $this;
    }

    /** @proxy */
    public function batchSize($num)
    {
        $htis->mongoCursor->batchSize($num);
        return $this;
    }

    /** @proxy */
    public function limit($num)
    {
        $this->mongoCursor->limit($num);
        return $this;
    }

    /** @proxy */
    public function skip($num)
    {
        $this->mongoCursor->skip($num);
        return $this;
    }

    /** @proxy */
    public function slaveOkay($okay = true)
    {
        $this->mongoCursor->slaveOkay($okay);
        return $this;
    }

    /** @proxy */
    public function snapshot()
    {
        $this->mongoCursor->snapshot();
        return $this;
    }

    /** @proxy */
    public function sort($fields)
    {
        if ($this->loggerCallable) {
            $this->log(array(
                'sort' => true,
                'sortFields' => $fields
            ));
        }

        $this->mongoCursor->sort($fields);
        return $this;
    }

    /** @proxy */
    public function tailable($tail = true)
    {
        $this->mongoCursor->tailable($tail);
        return $this;
    }

    /** @proxy */
    public function timeout($ms)
    {
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
        return iterator_to_array($this);
    }

    /**
     * Get the first single result from the cursor.
     *
     * @return array $document  The single document.
     */
    public function getSingleResult()
    {
        $result = null;
        $this->valid() ?: $this->next();
        if ($this->valid()) {
            $result = $this->current();
        }
        $this->reset();
        return $result ? $result : null;
    }
}