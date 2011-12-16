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

use Doctrine\Common\EventManager,
    Doctrine\MongoDB\Logging\MethodLogger;

/**
 * Wrapper for the PHP MongoCollection class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class LoggableCollection extends Collection
{
    /**
     * The logger.
     *
     * @var MethodLogger
     */
    protected $logger;

    /**
     * Create a new MongoCollection instance that wraps a PHP MongoCollection instance
     * for a given ClassMetadata instance.
     *
     * @param MongoCollection $mongoCollection The MongoCollection instance.
     * @param Database $database The Database instance.
     * @param EventManager $evm The EventManager instance.
     * @param string $cmd Mongo cmd character.
     * @param MethodLogger $logger The logger.
     */
    public function __construct(\MongoCollection $mongoCollection, Database $database, EventManager $evm, $cmd, MethodLogger $logger)
    {
        parent::__construct($mongoCollection, $database, $evm, $cmd);

        $this->logger = $logger;
    }

    /** @override */
    public function batchInsert(array &$a, array $options = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'data' => $a,
            'options' => $options,
        ), $this->database->getName(), $this->getName());

        $retval = parent::batchInsert($a, $options);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @override */
    public function update($query, array $newObj, array $options = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'query' => $query,
            'newObj' => $newObj,
            'options' => $options,
        ), $this->database->getName(), $this->getName());

        $retval = parent::update($query, $newObj, $options);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @override */
    public function find(array $query = array(), array $fields = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'query' => $query,
            'fields' => $fields,
        ), $this->database->getName(), $this->getName());

        $retval = parent::find($query, $fields);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @override */
    public function findOne(array $query = array(), array $fields = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'query' => $query,
            'fields' => $fields,
        ), $this->database->getName(), $this->getName());

        $retval = parent::findOne($query, $fields);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'query' => $query,
            'limit' => $limit,
            'skip' => $skip,
        ), $this->database->getName(), $this->getName());

        $retval = parent::count($query, $limit, $skip);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function createDBRef(array $a)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array('data' => $a), $this->database->getName(), $this->getName());

        $retval = parent::createDBRef($a);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function deleteIndex($keys)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array('keys' => $keys), $this->database->getName(), $this->getName());

        $retval = parent::deleteIndex($keys);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function deleteIndexes()
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(), $this->database->getName(), $this->getName());

        $retval = parent::deleteIndexes();

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function drop()
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(), $this->database->getName(), $this->getName());

        $retval = parent::drop();

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function ensureIndex(array $keys, array $options = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'keys' => $keys,
            'options' => $options,
        ), $this->database->getName(), $this->getName());

        $retval = parent::ensureIndex($keys, $options);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function getDBRef(array $reference)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array('reference' => $reference), $this->database->getName(), $this->getName());

        $retval = parent::getDBRef($reference);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function group($keys, array $initial, $reduce, array $options = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'keys' => $keys,
            'initial' => $initial,
            'reduce' => $reduce,
            'options' => $options,
        ), $this->database->getName(), $this->getName());

        $retval = parent::group($keys, $initial, $reduce, $options);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function insert(array &$a, array $options = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'data' => $a,
            'options' => $options,
        ), $this->database->getName(), $this->getName());

        $retval = parent::insert($a, $options);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function remove(array $query, array $options = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'query' => $query,
            'options' => $options,
        ), $this->database->getName(), $this->getName());

        $retval = parent::remove($query, $options);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function save(array &$a, array $options = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array(
            'data' => $a,
            'options' => $options,
        ), $this->database->getName(), $this->getName());

        $retval = parent::save($a, $options);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function validate($scanData = false)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, __FUNCTION__, array('scanData' => $scanData), $this->database->getName(), $this->getName());

        $retval = parent::validate($scanData);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @override */
    protected function wrapCursor(\MongoCursor $cursor, $query, $fields)
    {
        return new LoggableCursor($cursor, $this->logger, $this->database->getName(), $this->getName(), $query, $fields);
    }
}
