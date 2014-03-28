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
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class EagerCursor implements Iterator
{
    /**
     * @var Cursor
     */
    protected $cursor;

    /**
     * @var array
     */
    protected $data = array();

    /**
     * @var boolean
     */
    protected $initialized = false;

    /**
     * Constructor.
     *
     * @param Cursor $cursor Cursor to wrap
     */
    public function __construct(Cursor $cursor)
    {
        $this->cursor = $cursor;
    }

    /**
     * Return the wrapped cursor.
     *
     * @return Cursor
     */
    public function getCursor()
    {
        return $this->cursor;
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
     * Initialize the internal data by converting the cursor to an array.
     */
    public function initialize()
    {
        if ($this->initialized === false) {
            $this->data = $this->cursor->toArray();
        }
        $this->initialized = true;
    }

    /**
     * @see \Iterator::rewind()
     */
    public function rewind()
    {
        $this->initialize();
        reset($this->data);
    }

    /**
     * @see \Iterator::current()
     */
    public function current()
    {
        $this->initialize();
        return current($this->data);
    }

    /**
     * @see \Iterator::key()
     */
    public function key()
    {
        $this->initialize();
        return key($this->data);
    }

    /**
     * @see \Iterator::next()
     */
    public function next()
    {
        $this->initialize();
        next($this->data);
    }

    /**
     * @see \Iterator::valid()
     */
    public function valid()
    {
        $this->initialize();
        return key($this->data) !== null;
    }

    /**
     * @see \Countable::count()
     */
    public function count()
    {
        $this->initialize();
        return count($this->data);
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
}
