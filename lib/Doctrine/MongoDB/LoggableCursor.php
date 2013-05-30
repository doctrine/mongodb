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
 * Wrapper for the PHP MongoCursor class.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class LoggableCursor extends Cursor implements Loggable
{
    /**
     * A callable for logging statements.
     *
     * @var callable
     */
    protected $loggerCallable;

    /**
     * Create a new LoggableCursor, which wraps around a given PHP MongoCursor.
     *
     * @param Connection  $connection     Connection used to create this cursor
     * @param Collection  $collection     Collection used to create this cursor
     * @param MongoCursor $mongoCursor    Cursor being wrapped
     * @param callable    $loggerCallable Logger callable
     * @param array       $query          Query criteria
     * @param array       $fields         Selected fields (projection)
     * @param integer     $numRetries     Number of times to retry queries
     */
    public function __construct(Connection $connection, Collection $collection, \MongoCursor $mongoCursor, $loggerCallable, array $query, array $fields, $numRetries = 0)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        parent::__construct($connection, $collection, $mongoCursor, $query, $fields, $numRetries);
        $this->loggerCallable = $loggerCallable;
    }

    /**
     * Gets the logger callable.
     *
     * @return mixed The logger callable
     */
    public function getLoggerCallable()
    {
        return $this->loggerCallable;
    }

    /**
     * Log something using the configured logger callable.
     *
     * @param array $data
     */
    public function log(array $data)
    {
        $data['query'] = $this->query;
        $data['fields'] = $this->fields;
        call_user_func($this->loggerCallable, $data);
    }

    /**
     * @see Cursor::sort()
     */
    public function sort($fields)
    {
        $this->log(array(
            'sort' => true,
            'sortFields' => $fields
        ));

        return parent::sort($fields);
    }

    /**
     * @see Cursor::skip()
     */
    public function skip($num)
    {
        $this->log(array(
            'skip' => true,
            'skipNum' => $num,
        ));

        return parent::skip($num);
    }

    /**
     * @see Cursor::limit()
     */
    public function limit($num)
    {
        $this->log(array(
            'limit' => true,
            'limitNum' => $num,
        ));

        return parent::limit($num);
    }

    /**
     * @see Cursor::hint()
     */
    public function hint(array $keyPattern)
    {
        $this->log(array(
            'hint' => true,
            'keyPattern' => $keyPattern,
        ));

        return parent::hint($keyPattern);
    }

    /**
     * @see Cursor::snapshot()
     */
    public function snapshot()
    {
        $this->log(array(
            'snapshot' => true,
        ));

        return parent::snapshot();
    }
}
