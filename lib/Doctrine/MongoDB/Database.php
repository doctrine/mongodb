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
use Doctrine\MongoDB\Util\ReadPreference;

/**
 * Wrapper for the PHP MongoDB class.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
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

    public function authenticate($username, $password)
    {
        return $this->getMongoDB()->authenticate($username, $password);
    }

    public function command(array $data, array $options = array())
    {
        return $this->getMongoDB()->command($data, $options);
    }

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

    public function createDBRef($collection, $a)
    {
        return $this->getMongoDB()->createDBRef($collection, $a);
    }

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

    public function dropCollection($coll)
    {
        return $this->getMongoDB()->dropCollection($coll);
    }

    public function execute($code, array $args = array())
    {
        return $this->getMongoDB()->execute($code, $args);
    }

    public function forceError()
    {
        return $this->getMongoDB()->forceError();
    }

    public function __get($name)
    {
        return $this->getMongoDB()->__get($name);
    }

    public function getDBRef(array $ref)
    {
        return $this->getMongoDB()->getDBRef($ref);
    }

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

    /**
     * Set whether secondary read queries are allowed for this database.
     *
     * This method wraps setSlaveOkay() for driver versions before 1.3.0. For
     * newer drivers, this method wraps setReadPreference() and specifies
     * SECONDARY_PREFERRED.
     */
    public function setSlaveOkay($ok = true)
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            return $this->getMongoDB()->setSlaveOkay($ok);
        }

        $prevSlaveOkay = $this->getSlaveOkay();

        if ($ok) {
            // Preserve existing tags for non-primary read preferences
            $readPref = $this->getMongoDB()->getReadPreference();
            $tags = !empty($readPref['tagsets']) ? ReadPreference::convertTagSets($readPref['tagsets']) : array();
            $this->getMongoDB()->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, $tags);
        } else {
            $this->getMongoDB()->setReadPreference(\MongoClient::RP_PRIMARY);
        }

        return $prevSlaveOkay;
    }

    /**
     * Get whether secondary read queries are allowed for this database.
     *
     * This method wraps getSlaveOkay() for driver versions before 1.3.0. For
     * newer drivers, this method considers any read preference other than
     * PRIMARY as a true "slaveOkay" value.
     */
    public function getSlaveOkay()
    {
        if (version_compare(phpversion('mongo'), '1.3.0', '<')) {
            return $this->getMongoDB()->getSlaveOkay();
        }

        $readPref = $this->getMongoDB()->getReadPreference();

        if (is_numeric($readPref['type'])) {
            $readPref['type'] = ReadPreference::convertNumericType($readPref['type']);
        }

        return \MongoClient::RP_PRIMARY !== $readPref['type'];
    }

    public function getProfilingLevel()
    {
        return $this->getMongoDB()->getProfilingLevel();
    }

    public function lastError()
    {
        return $this->getMongoDB()->lastError();
    }

    public function listCollections()
    {
        return $this->getMongoDB()->listCollections();
    }

    public function prevError()
    {
        return $this->getMongoDB()->prevError();
    }

    public function repair($preserveClonedFiles = false, $backupOriginalFiles = false)
    {
        return $this->getMongoDB()->repair($preserveClonedFiles, $backupOriginalFiles);
    }

    public function resetError()
    {
        return $this->getMongoDB()->resetError();
    }

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

    public function setProfilingLevel($level)
    {
        return $this->getMongoDB()->setProfilingLevel($level);
    }

    public function __toString()
    {
        return $this->name;
    }
}
