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

use Doctrine\MongoDB\Logging\MethodLogger;

/**
 * Wrapper for the PHP MongoCursor class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class LoggableCursor extends Cursor
{
    /**
     * A logger.
     *
     * @var MethodLogger
     */
    protected $logger;

    protected $databaseName;
    protected $collectionName;

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
     * @param MethodLogger $logger A logger.
     * @param string $databaseName The database name.
     * @param string $collectionName The collection name.
     * @param array $query The query array that was used to create this cursor.
     * @param array $fields The fields selected on this cursor.
     */
    public function __construct(\MongoCursor $mongoCursor, MethodLogger $logger, $databaseName, $collectionName, array $query, array $fields)
    {
        parent::__construct($mongoCursor);

        $this->databaseName = $databaseName;
        $this->collectionName = $collectionName;
        $this->logger = $logger;
        $this->query = $query;
        $this->fields = $fields;
    }

    /**
     * Gets the logger.
     *
     * @return mixed The logger
     */
    public function getLogger()
    {
        return $this->logger;
    }

    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    public function getCollectionName()
    {
        return $this->collectionName;
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

    /** @proxy */
    public function sort($fields)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_CURSOR, __FUNCTION__, array(
            'query' => $this->query,
            'queryFields' => $this->fields,
            'fields' => $fields,
        ), $this->databaseName, $this->collectionName);

        $retval = parent::sort($fields);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function skip($num)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_CURSOR, __FUNCTION__, array(
            'query' => $this->query,
            'queryFields' => $this->fields,
            'num' => $num,
        ), $this->databaseName, $this->collectionName);

        $retval = parent::skip($num);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function limit($num)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_CURSOR, __FUNCTION__, array(
            'query' => $this->query,
            'queryFields' => $this->fields,
            'num' => $num,
        ), $this->databaseName, $this->collectionName);

        $retval = parent::limit($num);

        $this->logger->stopMethod();

        return $this->retval;
    }

    /** @proxy */
    public function hint(array $keyPattern)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_CURSOR, __FUNCTION__, array(
            'query' => $this->query,
            'queryFields' => $this->fields,
            'keyPattern' => $keyPattern,
        ), $this->databaseName, $this->collectionName);

        $retval = parent::hint($keyPattern);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function snapshot()
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_CURSOR, __FUNCTION__, array(
            'query' => $this->query,
            'queryFields' => $this->fields,
        ), $this->databaseName, $this->collectionName);

        $retval = parent::snapshot();

        $this->logger->stopMethod();

        return $retval;
    }
}
