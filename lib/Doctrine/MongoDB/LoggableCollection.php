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

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Traits\LoggableCollectionTrait;

/**
 * Wrapper for the MongoCollection class with logging functionality.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class LoggableCollection extends Collection implements Loggable
{
    use LoggableCollectionTrait;

    /**
     * Constructor.
     *
     * @param Database         $database        Database to which this collection belongs
     * @param \MongoCollection $mongoCollection MongoCollection instance being wrapped
     * @param EventManager     $evm             EventManager instance
     * @param integer          $numRetries      Number of times to retry queries
     * @param callable         $loggerCallable  The logger callable
     */
    public function __construct(Database $database, \MongoCollection $mongoCollection, EventManager $evm, $numRetries, $loggerCallable)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        $this->loggerCallable = $loggerCallable;
        parent::__construct($database, $mongoCollection, $evm, $numRetries);
    }

    /**
     * Wraps a MongoCursor instance with a LoggableCursor.
     *
     * @see Collection::wrapCursor()
     * @param \MongoCursor $cursor
     * @param array        $query
     * @param array        $fields
     * @return LoggableCursor
     */
    protected function wrapCursor(\MongoCursor $cursor, $query, $fields)
    {
        return new LoggableCursor($this, $cursor, $query, $fields, $this->numRetries, $this->loggerCallable);
    }
}
