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
class EagerCursor implements Iterator
{
    /**
     * The Cursor instance being wrapped.
     *
     * @var Cursor
     */
    protected $cursor;

    /**
     * The Cursor results.
     *
     * @var array
     */
    protected $data = array();

    /**
     * Whether the internal data has been initialized.
     *
     * @var boolean
     */
    protected $initialized = false;

    /**
     * Constructor.
     *
     * @param Cursor $cursor
     */
    public function __construct(Cursor $cursor)
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
     * @return Cursor
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
        return key($this->data);
    }

    /**
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        $this->initialize();
        next($this->data);
    }

    /**
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        $this->initialize();
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
        return key($this->data) !== null;
    }
}
