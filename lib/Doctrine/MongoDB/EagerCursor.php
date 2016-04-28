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

/**
 * EagerCursor wraps a Cursor instance and fetches all of its results upon
 * initialization.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class EagerCursor implements CursorInterface
{
    /**
     * The Cursor instance being wrapped.
     *
     * @var CursorInterface
     */
    protected $cursor;

    /**
     * The Cursor results.
     *
     * @var array
     */
    protected $data = [];

    /**
     * Whether the internal data has been initialized.
     *
     * @var boolean
     */
    protected $initialized = false;

    /**
     * Whether the cursor has started iterating.
     *
     * This is necessary for getNext() and hasNext() to work properly.
     *
     * @var boolean
     */
    private $iterating = false;

    /**
     * Constructor.
     *
     * @param CursorInterface $cursor
     */
    public function __construct(CursorInterface $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * @see http://php.net/manual/en/countable.count.php
     */
    public function count()
    {
        $this->initialize();

        return count($this->data);
    }

    /**
     * @see http://php.net/manual/en/iterator.current.php
     */
    public function current()
    {
        $this->initialize();

        return current($this->data);
    }

    /**
     * Return the wrapped Cursor.
     *
     * @return CursorInterface
     */
    public function getCursor()
    {
        return $this->cursor;
    }

    /**
     * @see Iterator::getSingleResult()
     */
    public function getSingleResult()
    {
        $this->rewind();

        if ($this->valid()) {
            return $this->current();
        }

        return null;
    }

    /**
     * Initialize the internal data by converting the Cursor to an array.
     */
    public function initialize()
    {
        if ($this->initialized === false) {
            $this->data = $this->cursor->toArray();
        }
        $this->initialized = true;
    }

    /**
     * Return whether the internal data has been initialized.
     *
     * @return boolean
     */
    public function isInitialized()
    {
        return $this->initialized;
    }

    /**
     * @see http://php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        $this->initialize();
        $this->iterating = true;

        return key($this->data);
    }

    /**
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        $this->initialize();
        $this->iterating = true;
        next($this->data);
    }

    /**
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        $this->initialize();
        $this->iterating = false;
        reset($this->data);
    }

    /**
     * @see Iterator::toArray()
     */
    public function toArray()
    {
        $this->initialize();

        return $this->data;
    }

    /**
     * @see http://php.net/manual/en/iterator.valid.php
     */
    public function valid()
    {
        $this->initialize();
        $this->iterating = true;

        return key($this->data) !== null;
    }

    /**
     * {@inheritdoc}
     */
    public function addOption($key, $value)
    {
        $this->cursor->addOption($key, $value);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function batchSize($num)
    {
        $this->cursor->batchSize($num);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function dead()
    {
        return $this->cursor->dead();
    }

    /**
     * {@inheritdoc}
     */
    public function explain()
    {
        return $this->cursor->explain();
    }

    /**
     * {@inheritdoc}
     */
    public function fields(array $f)
    {
        $this->cursor->fields($f);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function getCollection()
    {
        return $this->cursor->getCollection();
    }

    /**
     * {@inheritDoc}
     */
    public function getFields()
    {
        return $this->cursor->getFields();
    }

    /**
     * {@inheritDoc}
     */
    public function getNext()
    {
        $this->initialize();

        $next = ($this->iterating) ? next($this->data) : current($this->data);
        $this->iterating = true;

        return ($next !== false) ? $next : null;
    }

    /**
     * {@inheritDoc}
     */
    public function getQuery()
    {
        return $this->cursor->getQuery();
    }

    /**
     * {@inheritdoc}
     */
    public function getReadPreference()
    {
        return $this->cursor->getReadPreference();
    }

    /**
     * {@inheritdoc}
     */
    public function setReadPreference($readPreference, array $tags = null)
    {
        $this->cursor->setReadPreference($readPreference, $tags);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getUseIdentifierKeys()
    {
        return $this->cursor->getUseIdentifierKeys();
    }

    /**
     * {@inheritdoc}
     */
    public function setUseIdentifierKeys($useIdentifierKeys)
    {
        $this->cursor->setUseIdentifierKeys($useIdentifierKeys);

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function hasNext()
    {
        $this->initialize();

        $wasIterating = $this->iterating;
        $hasNext = $this->getNext() !== null;

        // Reset the internal cursor if we weren't iterating
        if ($wasIterating) {
            prev($this->data);
        } else {
            $this->iterating = false;
        }

        return $hasNext;
    }

    /**
     * {@inheritdoc}
     */
    public function hint($keyPattern)
    {
        $this->cursor->hint($keyPattern);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function immortal($liveForever = true)
    {
        $this->cursor->immortal($liveForever);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function info()
    {
        return $this->cursor->info();
    }

    /**
     * {@inheritdoc}
     */
    public function limit($num)
    {
        $this->cursor->limit($num);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function maxTimeMS($ms)
    {
        // Need to use method_exists - adding to CursorInterface is not allowed
        // due to SemVer restrictions
        if (method_exists($this->cursor, 'maxTimeMS')) {
            $this->cursor->maxTimeMS($ms);
        }

        return $this;
    }

    /**
     * {@inheritDoc}
     */
    public function recreate()
    {
        $this->initialized = false;
        $this->data = [];
        $this->iterating = false;
        $this->cursor->recreate();
    }

    /**
     * {@inheritdoc}
     */
    public function reset()
    {
        $this->cursor->reset();
    }

    /**
     * {@inheritdoc}
     */
    public function skip($num)
    {
        $this->cursor->skip($num);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function slaveOkay($ok = true)
    {
        $this->cursor->slaveOkay($ok);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function snapshot()
    {
        $this->cursor->snapshot();

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function sort($fields)
    {
        $this->cursor->sort($fields);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function tailable($tail = true)
    {
        $this->cursor->tailable($tail);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function timeout($ms)
    {
        $this->cursor->timeout($ms);

        return $this;
    }
}
