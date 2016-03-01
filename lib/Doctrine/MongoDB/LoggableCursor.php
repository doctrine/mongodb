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
 * Wrapper for the MongoCursor class with logging functionality.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class LoggableCursor extends Cursor implements Loggable
{
    /**
     * The logger callable.
     *
     * @var callable
     */
    protected $loggerCallable;

    /**
     * Constructor.
     *
     * @param Collection   $collection     Collection used to create this Cursor
     * @param \MongoCursor $mongoCursor    MongoCursor being wrapped
     * @param array        $query          Query criteria
     * @param array        $fields         Selected fields (projection)
     * @param integer      $numRetries     Number of times to retry queries
     * @param callable     $loggerCallable Logger callable
     */
    public function __construct(Collection $collection, \MongoCursor $mongoCursor, array $query, array $fields, $numRetries, $loggerCallable)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        $this->loggerCallable = $loggerCallable;
        parent::__construct($collection, $mongoCursor, $query, $fields, $numRetries);
    }

    /**
     * Log something using the configured logger callable.
     *
     * @see Loggable::log()
     * @param array $data
     */
    public function log(array $data)
    {
        $data['query'] = $this->query;
        $data['fields'] = $this->fields;
        call_user_func($this->loggerCallable, $data);
    }

    /**
     * Get the logger callable.
     *
     * @return callable
     */
    public function getLoggerCallable()
    {
        return $this->loggerCallable;
    }

    /**
     * @see Cursor::hint()
     */
    public function hint($keyPattern)
    {
        $this->log([
            'hint' => true,
            'keyPattern' => $keyPattern,
        ]);

        return parent::hint($keyPattern);
    }

    /**
     * @see Cursor::limit()
     */
    public function limit($num)
    {
        $this->log([
            'limit' => true,
            'limitNum' => $num,
        ]);

        return parent::limit($num);
    }

    /**
     * @see Cursor::maxTimeMS()
     */
    public function maxTimeMS($ms)
    {
        $this->log([
            'maxTimeMS' => true,
            'maxTimeMSNum' => $ms,
        ]);

        return parent::maxTimeMS($ms);
    }

    /**
     * @see Cursor::skip()
     */
    public function skip($num)
    {
        $this->log([
            'skip' => true,
            'skipNum' => $num,
        ]);

        return parent::skip($num);
    }

    /**
     * @see Cursor::snapshot()
     */
    public function snapshot()
    {
        $this->log([
            'snapshot' => true,
        ]);

        return parent::snapshot();
    }

    /**
     * @see Cursor::sort()
     */
    public function sort($fields)
    {
        $this->log([
            'sort' => true,
            'sortFields' => $fields,
        ]);

        return parent::sort($fields);
    }
}
