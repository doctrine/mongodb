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
    Doctrine\MongoDB\Event\EventArgs;

/**
 * Wrapper for the PHP MongoClient class.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Connection
{
    /**
     * @var MongoClient $mongo
     */
    protected $mongo;

    /**
     * @var string $server
     */
    protected $server;

    /**
     * @var array $options
     */
    protected $options = array();

    /**
     * @var Doctrine\MongoDB\Configuration
     */
    protected $config;

    /**
     * The event manager that is the central point of the event system.
     *
     * @var Doctrine\Common\EventManager
     */
    protected $eventManager;

    /**
     * Mongo command prefix
     *
     * @var string
     */
    protected $cmd;

    /**
     * Create a new MongoClient wrapper instance.
     *
     * @param mixed $server A string server name, an existing MongoClient or Mongo instance, or null
     * @param array $options
     */
    public function __construct($server = null, array $options = array(), Configuration $config = null, EventManager $evm = null)
    {
        if ($server instanceof \MongoClient || $server instanceof \Mongo) {
            $this->mongo = $server;
        } elseif ($server !== null) {
            $this->server = $server;
            $this->options = $options;
        }
        $this->config = $config ? $config : new Configuration();
        $this->eventManager = $evm ? $evm : new EventManager();
        $this->cmd = $this->config->getMongoCmd();
    }

    public function initialize($reinitialize = false)
    {
        if ($reinitialize === true || $this->mongo === null) {
            if ($this->eventManager->hasListeners(Events::preConnect)) {
                $this->eventManager->dispatchEvent(Events::preConnect, new EventArgs($this));
            }

            $server  = $this->server;
            $options = $this->options;
            $this->mongo = $this->retry(function() use($server, $options) {
                if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
                    return new \Mongo($server ?: 'mongodb://localhost:27017', $options);
                }

                return new \MongoClient($server ?: 'mongodb://localhost:27017', $options);
            });

            if ($this->eventManager->hasListeners(Events::postConnect)) {
                $this->eventManager->dispatchEvent(Events::postConnect, new EventArgs($this));
            }
        }
    }

    /**
     * Returns current server string if one was set.
     *
     * @return string|null
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Gets the status of the connection.
     *
     * @return string
     */
    public function getStatus()
    {
        return $this->mongo->status;
    }

    /**
     * Checks whether the connection is initialized and connected.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->mongo !== null && $this->mongo instanceof \Mongo && $this->mongo->connected;
    }

    /**
     * Log something using the configured logger callable.
     *
     * @param array $log The array of data to log.
     */
    public function log(array $log)
    {
        call_user_func_array($this->config->getLoggerCallable(), array($log));
    }

    /**
     * Set the PHP MongoClient instance to wrap.
     *
     * @param MongoClient $mongo The PHP Mongo instance
     */
    public function setMongo($mongo)
    {
        if ( ! ($mongo instanceof \MongoClient || $mongo instanceof \Mongo)) {
            throw new \InvalidArgumentException('MongoClient or Mongo instance required');
        }

        $this->mongo = $mongo;
    }

    /**
     * Returns the PHP Mongo instance being wrapped.
     *
     * @return MongoClient
     */
    public function getMongo()
    {
        return $this->mongo;
    }

    /**
     * Gets the EventManager used by the Connection.
     *
     * @return Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Gets the Configuration used by the Connection.
     *
     * @return Doctrine\MongoDB\Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    public function close()
    {
        $this->initialize();
        return $this->mongo->close();
    }

    public function connect()
    {
        $this->initialize();

        $mongo = $this->mongo;
        return $this->retry(function() use($mongo) {
            return $mongo->connect();
        });
    }

    public function dropDatabase($database)
    {
        if ($this->eventManager->hasListeners(Events::preDropDatabase)) {
            $this->eventManager->dispatchEvent(Events::preDropDatabase, new EventArgs($this, $database));
        }

        $this->initialize();
        $result = $this->mongo->dropDB($database);

        if ($this->eventManager->hasListeners(Events::postDropDatabase)) {
            $this->eventManager->dispatchEvent(Events::postDropDatabase, new EventArgs($this, $result));
        }

        return $result;
    }

    public function __get($key)
    {
        $this->initialize();
        return $this->mongo->$key;
    }

    public function listDatabases()
    {
        $this->initialize();
        return $this->mongo->listDBs();
    }

    public function selectCollection($db, $collection)
    {
        $this->initialize();
        return $this->selectDatabase($db)->selectCollection($collection);
    }

    public function selectDatabase($name)
    {
        if ($this->eventManager->hasListeners(Events::preSelectDatabase)) {
            $this->eventManager->dispatchEvent(Events::preSelectDatabase, new EventArgs($this, $name));
        }

        $this->initialize();
        $database = $this->wrapDatabase($name);

        if ($this->eventManager->hasListeners(Events::postSelectDatabase)) {
            $this->eventManager->dispatchEvent(Events::postSelectDatabase, new EventArgs($this, $database));
        }

        return $database;
    }

    /**
     * Method which creates a Doctrine\MongoDB\Database instance.
     *
     * @param string $name
     * @return Database $database
     */
    protected function wrapDatabase($name)
    {
        $numRetries = $this->config->getRetryQuery();
        if (null !== $this->config->getLoggerCallable()) {
            return new LoggableDatabase(
                $this, $name, $this->eventManager, $this->cmd, $numRetries, $this->config->getLoggerCallable()
            );
        }
        return new Database(
            $this, $name, $this->eventManager, $this->cmd, $numRetries
        );
    }

    protected function retry(\Closure $retry)
    {
        if (!$numRetries = $this->config->getRetryConnect()) {
            return $retry();
        }

        $firstException = null;
        for ($i = 0; $i <= $numRetries; $i++) {
            try {
                return $retry();
            } catch (\MongoException $e) {
                if (!$firstException) {
                    $firstException = $e;
                }
                if ($i === $numRetries) {
                    throw $firstException;
                }
            }
        }

        throw $e;
    }

    public function __toString()
    {
        $this->initialize();
        return $this->mongo->__toString();
    }
}
