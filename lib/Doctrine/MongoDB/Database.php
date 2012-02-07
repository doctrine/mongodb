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
 * Wrapper for the PHP MongoDB class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class Database
{
    /**
     * Doctrine mongodb connection instance.
     *
     * @var Doctrine\MongoDB\Connection
     */
    protected $connection;

    /**
     * The name of the database
     *
     * @var string $Name
     */
    protected $name;

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
     * Number of times to retry queries.
     *
     * @var mixed
     */
    protected $numRetries;

    /**
     * Create a new MongoDB instance which wraps a PHP MongoDB instance.
     *
     * @param Connection $connection The Doctrine Connection instance.
     * @param string $name The name of the database.
     * @param EventManager $evm  The EventManager instance.
     * @param string $cmd  The MongoDB cmd character.
     * @param boolean|integer $numRetries Number of times to retry queries.
     */
    public function __construct(Connection $connection, $name, EventManager $evm, $cmd, $numRetries = 0)
    {
        $this->connection = $connection;
        $this->name = $name;
        $this->eventManager = $evm;
        $this->cmd = $cmd;
        $this->numRetries = (integer) $numRetries;
    }

    /**
     * Gets the name of this database
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Get the MongoDB instance being wrapped.
     *
     * @return MongoDB $mongoDB
     */
    public function getMongoDB()
    {
        return $this->connection->getMongo()->selectDB($this->name);
    }

    /** @proxy */
    public function authenticate($username, $password)
    {
        return $this->getMongoDB()->authenticate($username, $password);
    }

    /** @proxy */
    public function command(array $data)
    {
        return $this->getMongoDB()->command($data);
    }

    /** @proxy */
    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        if ($this->eventManager->hasListeners(Events::preCreateCollection)) {
            $this->eventManager->dispatchEvent(Events::preCreateCollection, new CreateCollectionEventArgs($this, $name, $capped, $size, $max));
        }

        $this->getMongoDB()->createCollection($name, $capped, $size, $max);

        $result = $this->selectCollection($name);

        if ($this->eventManager->hasListeners(Events::postCreateCollection)) {
            $this->eventManager->dispatchEvent(Events::postCreateCollection, new EventArgs($this, $prefix));
        }

        return $result;
    }

    /** @proxy */
    public function createDBRef($collection, $a)
    {
        return $this->getMongoDB()->createDBRef($collection, $a);
    }

    /** @proxy */
    public function drop()
    {
        if ($this->eventManager->hasListeners(Events::preDropDatabase)) {
            $this->eventManager->dispatchEvent(Events::preDropDatabase, new EventArgs($this));
        }

        $result = $this->getMongoDB()->drop();

        if ($this->eventManager->hasListeners(Events::postDropDatabase)) {
            $this->eventManager->dispatchEvent(Events::postDropDatabase, new EventArgs($this));
        }

        return $result;
    }

    /** @proxy */
    public function dropCollection($coll)
    {
        return $this->getMongoDB()->dropCollection($coll);
    }

    /** @proxy */
    public function execute($code, array $args = array())
    {
        return $this->getMongoDB()->execute($code, $args);
    }

    /** @proxy */
    public function forceError()
    {
        return $this->getMongoDB()->forceError();
    }

    /** @proxy */
    public function __get($name)
    {
        return $this->getMongoDB()->__get($name);
    }

    /** @proxy */
    public function getDBRef(array $ref)
    {
        return $this->getMongoDB()->getDBRef($ref);
    }

    /** @proxy */
    public function getGridFS($prefix = 'fs')
    {
        if ($this->eventManager->hasListeners(Events::preGetGridFS)) {
            $this->eventManager->dispatchEvent(Events::preGetGridFS, new EventArgs($this, $prefix));
        }

        $gridFS = $this->doGetGridFs($prefix);

        if ($this->eventManager->hasListeners(Events::preGetGridFS)) {
            $this->eventManager->dispatchEvent(Events::preGetGridFS, new EventArgs($this, $gridFS));
        }

        return $gridFS;
    }

    protected function doGetGridFs($name)
    {
        return new GridFS(
            $this->connection, $name, $this, $this->eventManager, $this->cmd
        );
    }

    /** @proxy */
    public function setSlaveOkay($ok = true)
    {
        return $this->getMongoDB()->setSlaveOkay($ok);
    }

    /** @proxy */
    public function getSlaveOkay()
    {
        return $this->getMongoDB()->getSlaveOkay();
    }

    /** @proxy */
    public function getProfilingLevel()
    {
        return $this->getMongoDB()->getProfilingLevel();
    }

    /** @proxy */
    public function lastError()
    {
        return $this->getMongoDB()->lastError();
    }

    /** @proxy */
    public function listCollections()
    {
        return $this->getMongoDB()->listCollections();
    }

    /** @proxy */
    public function prevError()
    {
        return $this->getMongoDB()->prevError();
    }

    /** @proxy */
    public function repair($preserveClonedFiles = false, $backupOriginalFiles = false)
    {
        return $this->getMongoDB()->repair($preserveClonedFiles, $backupOriginalFiles);
    }

    /** @proxy */
    public function resetError()
    {
        return $this->getMongoDB()->resetError();
    }

    /** @proxy */
    public function selectCollection($name)
    {
        if ($this->eventManager->hasListeners(Events::preSelectCollection)) {
            $this->eventManager->dispatchEvent(Events::preSelectCollection, new EventArgs($this, $name));
        }

        $collection = $this->doSelectCollection($name);

        if ($this->eventManager->hasListeners(Events::postSelectCollection)) {
            $this->eventManager->dispatchEvent(Events::postSelectCollection, new EventArgs($this, $collection));
        }

        return $collection;
    }

    /**
     * Method which creates a Doctrine\MongoDB\Collection instance.
     *
     * @param string $name
     * @return Collection $coll
     */
    protected function doSelectCollection($name)
    {
        return new Collection(
            $this->connection, $name, $this, $this->eventManager, $this->cmd, $this->numRetries
        );
    }

    /** @proxy */
    public function setProfilingLevel($level)
    {
        return $this->getMongoDB()->setProfilingLevel($level);
    }

    public function __toString()
    {
        return $this->name;
    }
}