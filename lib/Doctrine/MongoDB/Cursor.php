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
     * Create a new MongoCursor which wraps around a given PHP MongoCursor.
     *
     * @param MongoCursor $mongoCursor The cursor being wrapped.
     */
    public function __construct(\MongoCursor $mongoCursor)
    {
        $this->mongoCursor = $mongoCursor;
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
        $this->mongoCursor->batchSize($num);
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
        foreach ($fields as $fieldName => $order) {
            if (is_string($order)) {
                $order = strtolower($order) === 'asc' ? 1 : -1;
            }
            $order = (int) $order;
            $fields[$fieldName] = $order;
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
