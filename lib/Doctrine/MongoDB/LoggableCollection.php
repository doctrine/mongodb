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

/**
 * Wrapper for the MongoCollection class with logging functionality.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class LoggableCollection extends Collection implements Loggable
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
     * @param Database         $database        Database to which this collection belongs
     * @param \MongoCollection $mongoCollection MongoCollection instance being wrapped
     * @param EventManager     $evm             EventManager instance
     * @param integer          $numRetries      Number of times to retry queries
     * @param callable         $loggerCallable  The logger callable
     * @param Logging\QueryLogger $queryLogger  The QueryLogger object
     */
    public function __construct(Database $database, \MongoCollection $mongoCollection, EventManager $evm, $numRetries, $loggerCallable, Logging\QueryLogger $queryLogger = null)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        $this->loggerCallable = $loggerCallable;
        $this->queryLogger = $queryLogger;
        parent::__construct($database, $mongoCollection, $evm, $numRetries);
    }

    /**
     * Log something using the configured logger callable.
     *
     * @see Loggable::log()
     * @param array $log
     */
    public function log(array $log)
    {
        $log['db'] = $this->database->getName();
        $log['collection'] = $this->getName();
        if($this->loggerCallable){
            call_user_func_array($this->loggerCallable, array($log));
        }

        if($this->queryLogger instanceof Logging\QueryLogger){
            $this->queryLogger->startQuery($log);
        }
    }

    /**
     * @param array $log
     * @param callable $callback
     * @return mixed
     */
    protected function logMethod($log, $callback) {
        $this->log($log);
        $data = call_user_func($callback);
        if ($this->queryLogger instanceof Logging\QueryLogger) {
            $this->queryLogger->stopQuery();
        }
        return $data;
    }

    /**
     * @see Collection::batchInsert()
     */
    public function batchInsert(array &$a, array $options = array())
    {
        $log = array(
            'batchInsert' => true,
            'num' => count($a),
            'data' => $a,
            'options' => $options,
        );

        return $this->logMethod($log, function () use (&$a, $options) {
            return parent::batchInsert($a, $options);
        });
    }

    /**
     * @see Collection::count()
     */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        $log = array(
            'count' => true,
            'query' => $query,
            'limit' => $limit,
            'skip' => $skip,
        );

        return $this->logMethod($log, function () use ($query, $limit, $skip) {
            return parent::count($query, $limit, $skip);
        });
    }

    /**
     * @see Collection::deleteIndex()
     */
    public function deleteIndex($keys)
    {
        $log = array(
            'deleteIndex' => true,
            'keys' => $keys,
        );

        return $this->logMethod($log, function () use ($keys) {
            return parent::deleteIndex($keys);
        });
    }

    /**
     * @see Collection::deleteIndexes()
     */
    public function deleteIndexes()
    {
        $log = array('deleteIndexes' => true);

        return $this->logMethod($log, function () {
            return parent::deleteIndexes();
        });
    }

    /**
     * @see Collection::drop()
     */
    public function drop()
    {
        $log = array('drop' => true);

        return $this->logMethod($log, function () {
            return parent::drop();
        });
    }

    /**
     * @see Collection::ensureIndex()
     */
    public function ensureIndex(array $keys, array $options = array())
    {
        $log = array(
            'ensureIndex' => true,
            'keys' => $keys,
            'options' => $options,
        );

        return $this->logMethod($log, function () use ($keys, $options) {
            return parent::ensureIndex($keys, $options);
        });
    }

    /**
     * @see Collection::find()
     */
    public function find(array $query = array(), array $fields = array())
    {
        $log = array(
            'find' => true,
            'query' => $query,
            'fields' => $fields,
        );

        return $this->logMethod($log, function () use ($query, $fields) {
            return parent::find($query, $fields);
        });
    }

    /**
     * @see Collection::findOne()
     */
    public function findOne(array $query = array(), array $fields = array())
    {
        $log = array(
            'findOne' => true,
            'query' => $query,
            'fields' => $fields,
        );

        return $this->logMethod($log, function () use ($query, $fields) {
            return parent::findOne($query, $fields);
        });
    }

    /**
     * @see Collection::getDBRef()
     */
    public function getDBRef(array $reference)
    {
        $log = array(
            'getDBRef' => true,
            'reference' => $reference,
        );

        return $this->logMethod($log, function () use ($reference) {
            return parent::getDBRef($reference);
        });
    }

    /**
     * @see Collection::group()
     */
    public function group($keys, array $initial, $reduce, array $options = array())
    {
        $log = array(
            'group' => true,
            'keys' => $keys,
            'initial' => $initial,
            'reduce' => $reduce,
            'options' => $options,
        );

        return $this->logMethod($log, function () use ($keys, $initial, $reduce, $options) {
            return parent::group($keys, $initial, $reduce, $options);
        });
    }

    /**
     * @see Collection::insert()
     */
    public function insert(array &$a, array $options = array())
    {
        $log = array(
            'insert' => true,
            'document' => $a,
            'options' => $options,
        );

        return $this->logMethod($log, function () use (&$a, $options) {
            return parent::insert($a, $options);
        });
    }

    /**
     * @see Collection::remove()
     */
    public function remove(array $query, array $options = array())
    {
        $log = array(
            'remove' => true,
            'query' => $query,
            'options' => $options,
        );

        return $this->logMethod($log, function () use ($query, $options) {
            return parent::remove($query, $options);
        });
    }

    /**
     * @see Collection::save()
     */
    public function save(array &$a, array $options = array())
    {
        $log = array(
            'save' => true,
            'document' => $a,
            'options' => $options,
        );

        return $this->logMethod($log, function () use (&$a, $options) {
            return parent::save($a, $options);
        });
    }

    /**
     * @see Collection::update()
     */
    public function update($query, array $newObj, array $options = array())
    {
        $log = array(
            'update' => true,
            'query' => $query,
            'newObj' => $newObj,
            'options' => $options,
        );

        return $this->logMethod($log, function () use ($query, $newObj, $options) {
            return parent::update($query, $newObj, $options);
        });
    }

    /**
     * @see Collection::validate()
     */
    public function validate($scanData = false)
    {
        $log = array(
            'validate' => true,
            'scanData' => $scanData,
        );

        return $this->logMethod($log, function () use ($scanData) {
            return parent::validate($scanData);
        });
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
        return new LoggableCursor($this, $cursor, $query, $fields, $this->numRetries, $this->loggerCallable, $this->queryLogger);
    }
}
