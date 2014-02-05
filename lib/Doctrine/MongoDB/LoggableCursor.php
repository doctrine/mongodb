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
     * @var Logging\QueryLogger
     */
    protected $queryLogger;

    /**
     * Constructor.
     *
     * @param Collection   $collection     Collection used to create this Cursor
     * @param \MongoCursor $mongoCursor    MongoCursor being wrapped
     * @param array        $query          Query criteria
     * @param array        $fields         Selected fields (projection)
     * @param integer      $numRetries     Number of times to retry queries
     * @param callable     $loggerCallable Logger callable
     * @param Logging\QueryLogger $queryLogger QueryLogger object
     */
    public function __construct(Collection $collection, \MongoCursor $mongoCursor, array $query, array $fields, $numRetries, $loggerCallable, Logging\QueryLogger $queryLogger = null)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        $this->loggerCallable = $loggerCallable;
        parent::__construct($collection, $mongoCursor, $query, $fields, $numRetries);
        $this->queryLogger = $queryLogger;
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
        if ($this->loggerCallable) {
            call_user_func($this->loggerCallable, $data);
        }

        if($this->queryLogger instanceof Logging\QueryLogger){
            $this->queryLogger->startQuery($data);
        }
    }

    private function logAfter() {
        if($this->queryLogger instanceof Logging\QueryLogger){
            $this->queryLogger->stopQuery();
        }
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
    public function hint(array $keyPattern)
    {
        $log = array(
            'hint' => true,
            'keyPattern' => $keyPattern,
        );

        $this->log($log);
        $data = parent::hint($keyPattern);
        $this->logAfter();
        return $data;
    }

    /**
     * @see Cursor::limit()
     */
    public function limit($num)
    {
        $log = array(
            'limit' => true,
            'limitNum' => $num,
        );

        $this->log($log);
        $data = parent::limit($num);
        $this->logAfter();
        return $data;
    }

    /**
     * @see Cursor::skip()
     */
    public function skip($num)
    {
        $log = array(
            'skip' => true,
            'skipNum' => $num,
        );

        $this->log($log);
        $data = parent::skip($num);
        $this->logAfter();
        return $data;
    }

    /**
     * @see Cursor::snapshot()
     */
    public function snapshot()
    {
        $log = array(
            'snapshot' => true,
        );

        $this->log($log);
        $data = parent::snapshot();
        $this->logAfter();
        return $data;
    }

    /**
     * @see Cursor::sort()
     */
    public function sort($fields)
    {
        $log = array(
            'sort' => true,
            'sortFields' => $fields,
        );

        $this->log($log);
        $data = parent::sort($fields);
        $this->logAfter();
        return $data;
    }
}
