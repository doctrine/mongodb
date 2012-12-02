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

use Doctrine\Common\EventManager,
    Doctrine\ODM\Event\EventArgs;

/**
 * Wrapper for the PHP MongoCollection class.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
 */

class LoggableCollection extends Collection implements Loggable
{
    /**
     * A callable for logging statements.
     *
     * @var mixed
     */
    protected $loggerCallable;

    /**
     * Create a new MongoCollection instance that wraps a PHP MongoCollection instance
     * for a given ClassMetadata instance.
     *
     * @param Connection $connection The Doctrine Connection instance.
     * @param string $name The name of the collection.
     * @param Database $database The Database instance.
     * @param EventManager $evm The EventManager instance.
     * @param string $cmd Mongo cmd character.
     * @param Closure $loggerCallable The logger callable.
     * @param boolean|integer $numRetries Number of times to retry queries.
     */
    public function __construct(Connection $connection, $name, Database $database, EventManager $evm, $cmd, $loggerCallable, $numRetries = 0)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        $this->loggerCallable = $loggerCallable;
        parent::__construct($connection, $name, $database, $evm, $cmd, $numRetries);
    }

    /**
     * Log something using the configured logger callable.
     *
     * @param array $log The array of data to log.
     */
    public function log(array $log)
    {
        $log['db'] = $this->database->getName();
        $log['collection'] = $this->getName();
        call_user_func_array($this->loggerCallable, array($log));
    }

    /** @override */
    public function batchInsert(array &$a, array $options = array())
    {
        $this->log(array(
            'batchInsert' => true,
            'num' => count($a),
            'data' => $a,
            'options' => $options
        ));

        return parent::batchInsert($a, $options);
    }

    /** @override */
    public function update($query, array $newObj, array $options = array())
    {
        $this->log(array(
            'update' => true,
            'query' => $query,
            'newObj' => $newObj,
            'options' => $options
        ));

        return parent::update($query, $newObj, $options);
    }

    /** @override */
    public function find(array $query = array(), array $fields = array())
    {
        $this->log(array(
            'find' => true,
            'query' => $query,
            'fields' => $fields
        ));

        return parent::find($query, $fields);
    }

    /** @override */
    public function findOne(array $query = array(), array $fields = array())
    {
        $this->log(array(
            'findOne' => true,
            'query' => $query,
            'fields' => $fields
        ));

        return parent::findOne($query, $fields);
    }

    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        $this->log(array(
            'count' => true,
            'query' => $query,
            'limit' => $limit,
            'skip' => $skip
        ));

        return parent::count($query, $limit, $skip);
    }

    public function createDBRef(array $a)
    {
        $this->log(array(
            'createDBRef' => true,
            'reference' => $a
        ));

        return parent::createDBRef($a);
    }

    public function deleteIndex($keys)
    {
        $this->log(array(
            'deleteIndex' => true,
            'keys' => $keys
        ));

        return parent::deleteIndex($keys);
    }

    public function deleteIndexes()
    {
        $this->log(array(
            'deleteIndexes' => true
        ));

        return parent::deleteIndexes();
    }

    public function drop()
    {
        $this->log(array(
            'drop' => true
        ));

        return parent::drop();
    }

    public function ensureIndex(array $keys, array $options = array())
    {
        $this->log(array(
            'ensureIndex' => true,
            'keys' => $keys,
            'options' => $options
        ));

        return parent::ensureIndex($keys, $options);
    }

    public function getDBRef(array $reference)
    {
        $this->log(array(
            'getDBRef' => true,
            'reference' => $reference
        ));

        return parent::getDBRef($reference);
    }

    public function group($keys, array $initial, $reduce, array $options = array())
    {
        $this->log(array(
            'group' => true,
            'keys' => $keys,
            'initial' => $initial,
            'reduce' => $reduce,
            'options' => $options
        ));

        return parent::group($keys, $initial, $reduce, $options);
    }

    public function insert(array &$a, array $options = array())
    {
        $this->log(array(
            'insert' => true,
            'document' => $a,
            'options' => $options
        ));

        return parent::insert($a, $options);
    }

    public function remove(array $query, array $options = array())
    {
        $this->log(array(
            'remove' => true,
            'query' => $query,
            'options' => $options
        ));

        return parent::remove($query, $options);
    }

    public function save(array &$a, array $options = array())
    {
        $this->log(array(
            'save' => true,
            'document' => $a,
            'options' => $options
        ));

        return parent::save($a, $options);
    }

    public function validate($scanData = false)
    {
        $this->log(array(
            'validate' => true,
            'scanData' => $scanData
        ));

        return parent::validate($scanData);
    }

    /** @override */
    protected function wrapCursor(\MongoCursor $cursor, $query, $fields)
    {
        return new LoggableCursor($this->connection, $this, $cursor, $this->loggerCallable, $query, $fields, $this->numRetries);
    }
}
