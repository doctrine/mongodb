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
use Doctrine\MongoDB\Event\EventArgs;

/**
 * Wrapper for the PHP MongoClient class.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Connection
{
    /**
     * The PHP MongoClient instance being wrapped.
     *
     * @var \MongoClient
     */
    protected $mongo;

    /**
     * Server string used to construct the MongoClient instance (optional).
     *
     * @var string
     */
    protected $server;

    /**
     * Options used to construct the MongoClient instance (optional).
     *
     * @var array
     */
    protected $options = array();

    /**
     * The Configuration for this connection.
     *
     * @var Configuration
     */
    protected $config;

    /**
     * The EventManager used to dispatch events.
     *
     * @var \Doctrine\Common\EventManager
     */
    protected $eventManager;

    /**
     * Constructor.
     *
     * If $server is an existing MongoClient instance, the $options parameter
     * will not be used.
     *
     * @param string|\MongoClient $server  Server string or MongoClient instance
     * @param array               $options MongoClient constructor options
     * @param Configuration       $config  Configuration instance
     * @param EventManager        $evm     EventManager instance
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
    }

    /**
     * Wrapper method for MongoClient::close().
     *
     * @see http://php.net/manual/en/mongoclient.close.php
     * @return boolean
     */
    public function close()
    {
        $this->initialize();
        return $this->mongo->close();
    }

    /**
     * Wrapper method for MongoClient::connect().
     *
     * @see http://php.net/manual/en/mongoclient.connect.php
     * @return boolean
     */
    public function connect()
    {
        $this->initialize();

        $mongo = $this->mongo;
        return $this->retry(function() use($mongo) {
            return $mongo->connect();
        });
    }

    /**
     * Wrapper method for MongoClient::dropDB().
     *
     * This method will dispatch preDropDatabase and postDropDatabase events.
     *
     * @see http://php.net/manual/en/mongoclient.dropdb.php
     * @param string $database
     * @return array
     */
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

    /**
     * Get the Configuration used by this Connection.
     *
     * @return Configuration
     */
    public function getConfiguration()
    {
        return $this->config;
    }

    /**
     * Get the EventManager used by this Connection.
     *
     * @return \Doctrine\Common\EventManager
     */
    public function getEventManager()
    {
        return $this->eventManager;
    }

    /**
     * Get the PHP MongoClient instance being wrapped.
     *
     * @return \MongoClient
     */
    public function getMongo()
    {
        return $this->mongo;
    }

    /**
     * Set the PHP MongoClient instance to wrap.
     *
     * @param \MongoClient $mongo
     */
    public function setMongo($mongo)
    {
        if ( ! ($mongo instanceof \MongoClient || $mongo instanceof \Mongo)) {
            throw new \InvalidArgumentException('MongoClient or Mongo instance required');
        }

        $this->mongo = $mongo;
    }

    /**
     * Wrapper method for MongoClient::getReadPreference().
     *
     * @see http://php.net/manual/en/mongoclient.getreadpreference.php
     * @return array
     */
    public function getReadPreference()
    {
        return $this->mongo->getReadPreference();
    }

    /**
     * Wrapper method for MongoClient::setReadPreference().
     *
     * @see http://php.net/manual/en/mongoclient.setreadpreference.php
     * @param string $readPreference
     * @param array  $tags
     * @return boolean
     */
    public function setReadPreference($readPreference, array $tags = null)
    {
        if (isset($tags)) {
            return $this->mongo->setReadPreference($readPreference, $tags);
        }

        return $this->mongo->setReadPreference($readPreference);
    }

    /**
     * Get the server string.
     *
     * @return string|null
     */
    public function getServer()
    {
        return $this->server;
    }

    /**
     * Gets the $status property of the wrapped MongoClient instance.
     *
     * @deprecated 1.1 No longer used in driver; Will be removed for 1.2
     * @return string
     */
    public function getStatus()
    {
        return $this->mongo->status;
    }

    /**
     * Construct a new MongoClient instance.
     *
     * This method will dispatch preConnect and postConnect events.
     *
     * @param boolean $reinitialize
     */
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
     * Checks whether the connection is initialized and connected.
     *
     * @return boolean
     */
    public function isConnected()
    {
        return $this->mongo !== null && $this->mongo instanceof \Mongo && $this->mongo->connected;
    }

    /**
     * Wrapper method for MongoClient::listDBs().
     *
     * @see http://php.net/manual/en/mongoclient.listdbs.php
     * @return array
     */
    public function listDatabases()
    {
        $this->initialize();
        return $this->mongo->listDBs();
    }

    /**
     * Log something using the configured logger callable (if available).
     *
     * @param array $log
     */
    public function log(array $log)
    {
        if (null !== $this->config->getLoggerCallable()) {
            call_user_func_array($this->config->getLoggerCallable(), array($log));
        }
    }

    /**
     * Wrapper method for MongoClient::selectCollection().
     *
     * @see http://php.net/manual/en/mongoclient.selectcollection.php
     * @param string $db
     * @param string $collection
     * @return Collection
     */
    public function selectCollection($db, $collection)
    {
        $this->initialize();
        return $this->selectDatabase($db)->selectCollection($collection);
    }

    /**
     * Wrapper method for MongoClient::selectDatabase().
     *
     * This method will dispatch preSelectDatabase and postSelectDatabase
     * events.
     *
     * @see http://php.net/manual/en/mongoclient.selectdatabase.php
     * @param string $name
     * @return Database
     */
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
     * Wrapper method for MongoClient::__get().
     *
     * @see http://php.net/manual/en/mongoclient.get.php
     * @param string $database
     * @return \MongoDB
     */
    public function __get($database)
    {
        $this->initialize();
        return $this->mongo->$database;
    }

    /**
     * Wrapper method for MongoClient::__toString().
     *
     * @see http://php.net/manual/en/mongoclient.tostring.php
     * @return string
     */
    public function __toString()
    {
        $this->initialize();
        return $this->mongo->__toString();
    }

    /**
     * Conditionally retry a closure if it yields an exception.
     *
     * If the closure does not return successfully within the configured number
     * of retries, its first exception will be thrown.
     *
     * @param \Closure $retry
     * @return mixed
     */
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

    /**
     * Creates a Database instance.
     *
     * If a logger callable was defined, a LoggableDatabase will be returned.
     *
     * @param string $name
     * @return Database
     */
    protected function wrapDatabase($name)
    {
        $numRetries = $this->config->getRetryQuery();
        if (null !== $this->config->getLoggerCallable()) {
            return new LoggableDatabase(
                $this, $name, $this->eventManager, $numRetries, $this->config->getLoggerCallable()
            );
        }
        return new Database($this, $name, $this->eventManager, $numRetries);
    }
}
