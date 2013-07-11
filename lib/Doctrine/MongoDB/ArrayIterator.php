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

use ArrayAccess;

/**
 * ArrayIterator is used to encapsulate document results from commands.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class ArrayIterator implements Iterator, ArrayAccess
{
    /**
     * @var array
     */
    private $elements;

    /**
     * Constructor.
     *
     * @param array $elements
     */
    public function __construct(array $elements = array())
    {
        $this->elements = $elements;
    }

    /**
     * Return the first element in the array, or false if the array is empty.
     *
     * @see http://php.net/manual/en/function.reset.php
     * @return array|object|boolean
     */
    public function first()
    {
        return reset($this->elements);
    }

    /**
     * Return the last element in the array, or false if the array is empty.
     *
     * @see http://php.net/manual/en/function.end.php
     * @return array|object|boolean
     */
    public function last()
    {
        return end($this->elements);
    }

    /**
     * @see http://php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        return key($this->elements);
    }

    /**
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        next($this->elements);
    }

    /**
     * @see http://php.net/manual/en/iterator.current.php
     */
    public function current()
    {
        return current($this->elements);
    }

    /**
     * @see http://php.net/manual/en/countable.count.php
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        reset($this->elements);
    }

    /**
     * Alias of {@link ArrayIterator::rewind()}.
     */
    public function reset()
    {
        reset($this->elements);
    }

    /**
     * @see http://php.net/manual/en/iterator.valid.php
     */
    public function valid()
    {
        return current($this->elements) !== false;
    }

    /**
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        $this->elements[$offset] = $value;
    }

    /**
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    /**
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }

    /**
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    /**
     * @see Iterator::toArray()
     */
    public function toArray()
    {
        return $this->elements;
    }

    /**
     * @see Iterator::getSingleResult()
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
