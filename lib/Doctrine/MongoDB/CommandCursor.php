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

use BadMethodCallException;
use MongoCommandCursor;

/**
 * Wrapper for the PHP MongoCommandCursor class.
 *
 * @since  1.2
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class CommandCursor implements Iterator
{
    /**
     * The MongoCommandCursor instance being wrapped.
     *
     * @var MongoCommandCursor
     */
    private $mongoCommandCursor;

    /**
     * Number of times to retry queries.
     *
     * @var integer
     */
    private $numRetries;

    private $batchSize;
    private $timeout;

    /**
     * Constructor.
     *
     * @param MongoCommandCursor $mongoCommandCursor MongoCommandCursor instance being wrapped
     * @param integer            $numRetries         Number of times to retry queries
     */
    public function __construct(MongoCommandCursor $mongoCommandCursor, $numRetries = 0)
    {
        $this->mongoCommandCursor = $mongoCommandCursor;
        $this->numRetries = (integer) $numRetries;
    }

    /**
     * Wrapper method for MongoCommandCursor::batchSize().
     *
     * @see http://php.net/manual/en/mongocommandcursor.batchsize.php
     * @param integer $num
     * @return $this
     */
    public function batchSize($num)
    {
        $this->batchSize = (integer) $num;
        $this->mongoCommandCursor->batchSize($num);

        return $this;
    }

    /**
     * Recreates the command cursor and counts its results.
     *
     * @see http://php.net/manual/en/countable.count.php
     * @return integer
     */
    public function count()
    {
        $cursor = $this;

        return $this->retry(function() use ($cursor) {
            return iterator_count($cursor);
        });
    }

    /**
     * Wrapper method for MongoCommandCursor::current().
     *
     * @see http://php.net/manual/en/iterator.current.php
     * @see http://php.net/manual/en/mongocommandcursor.current.php
     * @return array|null
     */
    public function current()
    {
        return $this->mongoCommandCursor->current();
    }

    /**
     * Wrapper method for MongoCommandCursor::dead().
     *
     * @see http://php.net/manual/en/mongocommandcursor.dead.php
     * @return boolean
     */
    public function dead()
    {
        return $this->mongoCommandCursor->dead();
    }

    /**
     * Returns the MongoCommandCursor instance being wrapped.
     *
     * @return \MongoCommandCursor
     */
    public function getMongoCommandCursor()
    {
        return $this->mongoCommandCursor;
    }

    /**
     * Rewind the cursor and return its first result.
     *
     * @see Iterator::getSingleResult()
     * @return array|null
     */
    public function getSingleResult()
    {
        $result = null;
        foreach ($this as $result) {
            break;
        }
        /* Avoid rewinding the cursor here, as that would re-execute the
         * command prematurely and later iteration will rewind on its own.
         */

        return $result;
    }

    /**
     * Wrapper method for MongoCommandCursor::info().
     *
     * @see http://php.net/manual/en/mongocommandcursor.info.php
     * @return array
     */
    public function info()
    {
        return $this->mongoCommandCursor->info();
    }

    /**
     * Wrapper method for MongoCommandCursor::key().
     *
     * @see http://php.net/manual/en/iterator.key.php
     * @see http://php.net/manual/en/mongocommandcursor.key.php
     * @return integer
     */
    public function key()
    {
        return $this->mongoCommandCursor->key();
    }

    /**
     * Wrapper method for MongoCommandCursor::next().
     *
     * @see http://php.net/manual/en/iterator.next.php
     * @see http://php.net/manual/en/mongocommandcursor.next.php
     */
    public function next()
    {
        $cursor = $this;

        $this->retry(function() use ($cursor) {
            $cursor->getMongoCommandCursor()->next();
        });
    }

    /**
     * Wrapper method for MongoCommandCursor::rewind().
     *
     * @see http://php.net/manual/en/iterator.rewind.php
     * @see http://php.net/manual/en/mongocommandcursor.rewind.php
     * @return array
     */
    public function rewind()
    {
        $cursor = $this;

        return $this->retry(function() use ($cursor) {
            return $cursor->getMongoCommandCursor()->rewind();
        });
    }

    /**
     * Wrapper method for MongoCommandCursor::timeout().
     *
     * @see http://php.net/manual/en/mongocommandcursor.timeout.php
     * @param integer $ms
     * @return $this
     * @throws BadMethodCallException if MongoCommandCursor::timeout() is not available
     */
    public function timeout($ms)
    {
        if ( ! method_exists('MongoCommandCursor', 'timeout')) {
            throw new BadMethodCallException('MongoCommandCursor::timeout() is not available');
        }

        $this->timeout = (integer) $ms;
        $this->mongoCommandCursor->timeout($ms);

        return $this;
    }

    /**
     * Return the cursor's results as an array.
     *
     * @see Iterator::toArray()
     * @return array
     */
    public function toArray()
    {
        $cursor = $this;

        return $this->retry(function() use ($cursor) {
            return iterator_to_array($cursor);
        });
    }

    /**
     * Wrapper method for MongoCommandCursor::valid().
     *
     * @see http://php.net/manual/en/iterator.valid.php
     * @see http://php.net/manual/en/mongocommandcursor.valid.php
     * @return boolean
     */
    public function valid()
    {
        return $this->mongoCommandCursor->valid();
    }

    /**
     * Conditionally retry a closure if it yields an exception.
     *
     * If the closure does not return successfully within the configured number
     * of retries, its first exception will be thrown.
     *
     * @param \Closure $retry
     * @return mixed
     */
    protected function retry(\Closure $retry)
    {
        if ($this->numRetries < 1) {
            return $retry();
        }

        $firstException = null;

        for ($i = 0; $i <= $this->numRetries; $i++) {
            try {
                return $retry();
            } catch (\MongoCursorException $e) {
            } catch (\MongoConnectionException $e) {
            }

            if ($firstException === null) {
                $firstException = $e;
            }
            if ($i === $this->numRetries) {
                throw $firstException;
            }
        }
    }
}
