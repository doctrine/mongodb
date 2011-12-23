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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\MongoDB;

/**
 * Wrapper for the PHP MongoCursor class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class LoggableCursor extends Cursor implements Loggable
{
    /**
     * A callable for logging statements.
     *
     * @var mixed
     */
    protected $loggerCallable;

    /**
     * The query array that was used when creating this cursor.
     *
     * @var array
     */
    protected $query = array();

    /**
     * The array of fields that were selected when creating this cursor.
     *
     * @var array
     */
    protected $fields = array();

    /**
     * Create a new MongoCursor which wraps around a given PHP MongoCursor.
     *
     * @param MongoCursor $mongoCursor The cursor being wrapped.
     * @param mixed $loggerCallable Logger callable.
     * @param array $query The query array that was used to create this cursor.
     * @param array $query The fields selected on this cursor.
     */
    public function __construct(\MongoCursor $mongoCursor, $loggerCallable, array $query, array $fields)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        parent::__construct($mongoCursor);
        $this->loggerCallable = $loggerCallable;
        $this->query = $query;
        $this->fields = $fields;
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
     * Gets the query array that was used when creating this cursor.
     *
     * @return array $query
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * Gets the array of fields that were selected when creating this cursor.
     *
     * @return array $fields
     */
    public function getFields()
    {
        return $this->fields;
    }

    /**
     * Log something using the configured logger callable.
     *
     * @param array $log The array of data to log.
     */
    public function log(array $log)
    {
        $log['query'] = $this->query;
        $log['fields'] = $this->fields;
        call_user_func_array($this->loggerCallable, array($log));
    }

    /** @proxy */
    public function sort($fields)
    {
        $this->log(array(
            'sort' => true,
            'sortFields' => $fields
        ));

        return parent::sort($fields);
    }

    /** @proxy */
    public function skip($num)
    {
        $this->log(array(
            'skip' => true,
            'skipNum' => $num,
        ));

        return parent::skip($num);
    }

    /** @proxy */
    public function limit($num)
    {
        $this->log(array(
            'limit' => true,
            'limitNum' => $num,
        ));

        return parent::limit($num);
    }

    /** @proxy */
    public function hint(array $keyPattern)
    {
        $this->log(array(
            'hint' => true,
            'keyPattern' => $keyPattern,
        ));

        return parent::hint($keyPattern);
    }

    /** @proxy */
    public function snapshot()
    {
        $this->log(array(
            'snapshot' => true,
        ));

        return parent::snapshot();
    }
}
