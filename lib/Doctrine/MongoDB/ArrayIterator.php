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
     * @var array
     */
    private $commandResult;

    /**
     * Constructor.
     *
     * @param array $elements
     */
    public function __construct(array $elements = [])
    {
        $this->elements = $elements;
    }

    /**
     * @see http://php.net/manual/en/countable.count.php
     */
    public function count()
    {
        return count($this->elements);
    }

    /**
     * @see http://php.net/manual/en/iterator.current.php
     */
    public function current()
    {
        return current($this->elements);
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
     * Get the full result document for the MongoDB command (if available).
     *
     * @since  1.1
     * @return array|null
     */
    public function getCommandResult()
    {
        return $this->commandResult;
    }

    /**
     * Set the full result document for the MongoDB command.
     *
     * @since  1.1
     * @param array $commandResult
     */
    public function setCommandResult(array $commandResult)
    {
        $this->commandResult = $commandResult;
    }

    /**
     * @see Iterator::getSingleResult()
     */
    public function getSingleResult()
    {
        reset($this->elements);
        $result = key($this->elements) !== null ? current($this->elements) : null;
        reset($this->elements);

        return $result;
    }

    /**
     * @see http://php.net/manual/en/iterator.key.php
     */
    public function key()
    {
        return key($this->elements);
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
     * @see http://php.net/manual/en/iterator.next.php
     */
    public function next()
    {
        next($this->elements);
    }

    /**
     * @see http://php.net/manual/en/arrayaccess.offsetexists.php
     */
    public function offsetExists($offset)
    {
        return isset($this->elements[$offset]);
    }

    /**
     * @see http://php.net/manual/en/arrayaccess.offsetget.php
     */
    public function offsetGet($offset)
    {
        return isset($this->elements[$offset]) ? $this->elements[$offset] : null;
    }

    /**
     * @see http://php.net/manual/en/arrayaccess.offsetset.php
     */
    public function offsetSet($offset, $value)
    {
        if ($offset === null) {
            $this->elements[] = $value;
        } else {
            $this->elements[$offset] = $value;
        }
    }

    /**
     * @see http://php.net/manual/en/arrayaccess.offsetunset.php
     */
    public function offsetUnset($offset)
    {
        unset($this->elements[$offset]);
    }

    /**
     * Alias of {@link ArrayIterator::rewind()}.
     */
    public function reset()
    {
        reset($this->elements);
    }

    /**
     * @see http://php.net/manual/en/iterator.rewind.php
     */
    public function rewind()
    {
        reset($this->elements);
    }

    /**
     * @see Iterator::toArray()
     */
    public function toArray()
    {
        return $this->elements;
    }

    /**
     * @see http://php.net/manual/en/iterator.valid.php
     */
    public function valid()
    {
        return key($this->elements) !== null;
    }
}
