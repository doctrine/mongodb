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

    /**
     * The database name.
     *
     * @var string
     */
    protected $databaseName;

    /**
     * The collection name.
     *
     * @var string
     */
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

    public function getLogger()
    {
        return $this->logger;
    }

    public function setLogger(MethodLogger $logger)
    {
        $this->logger = $logger;
    }

    public function getDatabaseName()
    {
        return $this->databaseName;
    }

    public function setDatabaseName($databaseName)
    {
        $this->databaseName = $databaseName;
    }

    public function getCollectionName()
    {
        return $this->collectionName;
    }

    public function setCollectionName($collectionName)
    {
        $this->collectionName = $collectionName;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    public function getFields()
    {
        return $this->fields;
    }

    public function setFields(array $fields)
    {
        $this->fields = $fields;
    }

    /** @override */
    protected function callDelegate($method, array $arguments = array())
    {
        if (!$this->logger) {
            return parent::callDelegate($method, $arguments);
        }

        $this->logger->startMethod(MethodLogger::CONTEXT_CURSOR, $method, $arguments, $this->databaseName, $this->collectionName, $this->query, $this->fields);
        $result = parent::callDelegate($method, $arguments);
        $this->logger->stopMethod();

        return $result;
    }
}
