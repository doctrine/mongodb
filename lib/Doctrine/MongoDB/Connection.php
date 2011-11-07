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
    Doctrine\MongoDB\Event\EventArgs;

/**
 * Wrapper for the PHP Mongo class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Connection
{
    /** The PHP Mongo instance. */
    protected $mongo;

    /** The server string */
    protected $server;

    /** The array of server options to use when connecting */
    protected $options = array();

    /** The Configuration instance */
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
     * Whether or not this connection has been connected or not.
     *
     * @var boolean
     */
    protected $connected = false;

    /**
     * Create a new Mongo wrapper instance.
     *
     * @param mixed $server A string server name, an existing Mongo instance or can be omitted.
     * @param array $options
     */
    public function __construct($server = null, array $options = array(), Configuration $config = null, EventManager $evm = null)
    {
        if ($server instanceof \Mongo) {
            $this->mongo = $server;
        } elseif ($server !== null) {
            $this->server = $server;
            $this->options = $options;
        }
        $this->config = $config ? $config : new Configuration();
        $this->eventManager = $evm ? $evm : new EventManager();
        $this->cmd = $this->config->getMongoCmd();
    }

    public function initialize()
    {
        if ($this->mongo === null) {
            if ($this->eventManager->hasListeners(Events::preConnect)) {
                $this->eventManager->dispatchEvent(Events::preConnect, new EventArgs($this));
            }

            if ($this->server) {
                $this->mongo = new \Mongo($this->server, $this->options);
            } else {
                $this->mongo = new \Mongo();
            }

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
     * Set the PHP Mongo instance to wrap.
     *
     * @param Mongo $mongo The PHP Mongo instance
     */
    public function setMongo(\Mongo $mongo)
    {
        $this->mongo = $mongo;
    }

    /**
     * Returns the PHP Mongo instance being wrapped.
     *
     * @return Mongo
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

    /** @proxy */
    public function close()
    {
        $this->initialize();
        return $this->mongo->close();
    }

    /** @proxy */
    public function connect()
    {
        $this->initialize();
        return $this->mongo->connect();
    }

    /** @proxy */
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

    /** @proxy */
    public function __get($key)
    {
        $this->initialize();
        return $this->mongo->$key;
    }

    /** @proxy */
    public function listDatabases()
    {
        $this->initialize();
        return $this->mongo->listDBs();
    }

    /** @proxy */
    public function selectCollection($db, $collection)
    {
        $this->initialize();
        return $this->selectDatabase($db)->selectCollection($collection);
    }

    /** @proxy */
    public function selectDatabase($name)
    {
        if ($this->eventManager->hasListeners(Events::preSelectDatabase)) {
            $this->eventManager->dispatchEvent(Events::preSelectDatabase, new EventArgs($this, $name));
        }

        $this->initialize();
        $db = $this->mongo->selectDB($name);
        $database = $this->wrapDatabase($db);

        if ($this->eventManager->hasListeners(Events::postSelectDatabase)) {
            $this->eventManager->dispatchEvent(Events::postSelectDatabase, new EventArgs($this, $database));
        }

        return $database;
    }

    /**
     * Method which wraps a MongoDB with a Doctrine\MongoDB\Database instance.
     *
     * @param MongoDB $database
     * @return Database $database
     */
    protected function wrapDatabase(\MongoDB $database)
    {
        if (null !== $this->config->getLoggerCallable()) {
            return new LoggableDatabase(
                $database, $this->eventManager, $this->cmd, $this->config->getLoggerCallable()
            );
        }
        return new Database(
            $database, $this->eventManager, $this->cmd
        );
    }

    /** @proxy */
    public function __toString()
    {
        $this->initialize();
        return $this->mongo->__toString();
    }
}
