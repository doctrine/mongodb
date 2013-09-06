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
use Doctrine\MongoDB\Event\CreateCollectionEventArgs;
use Doctrine\MongoDB\Event\EventArgs;
use Doctrine\MongoDB\Event\MutableEventArgs;
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
     * The Connection instance used for accessing the wrapped MongoDB class and
     * creating Collection instances.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The database name.
     *
     * @var string
     */
    protected $name;

    /**
     * The EventManager used to dispatch events.
     *
     * @var \Doctrine\Common\EventManager
     */
    protected $eventManager;

    /**
     * Number of times to retry queries.
     *
     * @var integer
     */
    protected $numRetries;

    /**
     * Constructor.
     *
     * @param Connection      $connection Connection used to create Collections
     * @param string          $name       The database name
     * @param EventManager    $evm        EventManager instance
     * @param boolean|integer $numRetries Number of times to retry queries
     */
    public function __construct(Connection $connection, $name, EventManager $evm, $numRetries = 0)
    {
        $this->connection = $connection;
        $this->name = $name;
        $this->eventManager = $evm;
        $this->numRetries = (integer) $numRetries;
    }

    /**
     * Wrapper method for MongoDB::authenticate().
     *
     * @see http://php.net/manual/en/mongodb.authenticate.php
     * @param string $username
     * @param string $password
     * @return array
     */
    public function authenticate($username, $password)
    {
        return $this->getMongoDB()->authenticate($username, $password);
    }

    /**
     * Wrapper method for MongoDB::command().
     *
     * @see http://php.net/manual/en/mongodb.command.php
     * @param array $data
     * @param array $options
     * @return array
     */
    public function command(array $data, array $options = array())
    {
        return $this->getMongoDB()->command($data, $options);
    }

    /**
     * Wrapper method for MongoDB::createCollection().
     *
     * This method will dispatch preCreateCollection and postCreateCollection
     * events.
     *
     * @see http://php.net/manual/en/mongodb.createcollection.php
     * @param string        $name            Collection name
     * @param boolean|array $cappedOrOptions Capped collection indicator or an
     *                                       options array (for driver 1.4+)
     * @param integer       $size            Storage size for fixed collections
     *                                       (ignored if options array is used)
     * @param integer       $max             Max documents for fixed collections
     *                                       (ignored if options array is used)
     * @return Collection
     */
    public function createCollection($name, $cappedOrOptions = false, $size = 0, $max = 0)
    {
        $options = is_array($cappedOrOptions)
            ? array_merge(array('capped' => false, 'size' => 0, 'max' => 0), $cappedOrOptions)
            : array('capped' => $cappedOrOptions, 'size' => $size, 'max' => $max);

        if ($this->eventManager->hasListeners(Events::preCreateCollection)) {
            $this->eventManager->dispatchEvent(Events::preCreateCollection, new CreateCollectionEventArgs($this, $name, $options));
        }

        $result = $this->doCreateCollection($name, $options);

        if ($this->eventManager->hasListeners(Events::postCreateCollection)) {
            $this->eventManager->dispatchEvent(Events::postCreateCollection, new EventArgs($this, $result));
        }

        return $result;
    }

    /**
     * Wrapper method for MongoDB::createDBRef().
     *
     * @see http://php.net/manual/en/mongodb.createdbref.php
     * @param string $collection
     * @param mixed  $a
     * @return array
     */
    public function createDBRef($collection, $a)
    {
        return $this->getMongoDB()->createDBRef($collection, $a);
    }

    /**
     * Wrapper method for MongoDB::drop().
     *
     * This method will dispatch preDropDatabase and postDropDatabase events.
     *
     * @see http://php.net/manual/en/mongodb.drop.php
     * @return array
     */
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

    /**
     * Wrapper method for MongoDB::dropCollection().
     *
     * @see http://php.net/manual/en/mongodb.dropcollection.php
     * @param string $coll
     * @return array
     */
    public function dropCollection($coll)
    {
        return $this->getMongoDB()->dropCollection($coll);
    }

    /**
     * Wrapper method for MongoDB::execute().
     *
     * @see http://php.net/manual/en/mongodb.execute.php
     * @return array
     */
    public function execute($code, array $args = array())
    {
        return $this->getMongoDB()->execute($code, $args);
    }

    /**
     * Wrapper method for MongoDB::forceError().
     *
     * @deprecated 1.1 Deprecated in driver; will be removed for 1.2
     * @see http://php.net/manual/en/mongodb.forceerror.php
     * @return array
     */
    public function forceError()
    {
        return $this->getMongoDB()->forceError();
    }

    /**
     * Wrapper method for MongoDB::getDBRef().
     *
     * This method will dispatch preGetDBRef and postGetDBRef events.
     *
     * @see http://php.net/manual/en/mongodb.getdbref.php
     * @param array $reference
     * @return array|null
     */
    public function getDBRef(array $reference)
    {
        if ($this->eventManager->hasListeners(Events::preGetDBRef)) {
            $this->eventManager->dispatchEvent(Events::preGetDBRef, new EventArgs($this, $reference));
        }

        $result = $this->doGetDBRef($reference);

        if ($this->eventManager->hasListeners(Events::postGetDBRef)) {
            $eventArgs = new MutableEventArgs($this, $result);
            $this->eventManager->dispatchEvent(Events::postGetDBRef, $eventArgs);
            $result = $eventArgs->getData();
        }

        return $result;
    }

    /**
     * Wrapper method for MongoDB::getGridFS().
     *
     * This method will dispatch preGetGridFS and postGetGridFS events.
     *
     * @see http://php.net/manual/en/mongodb.getgridfs.php
     * @param string $prefix
     * @return GridFS
     */
    public function getGridFS($prefix = 'fs')
    {
        if ($this->eventManager->hasListeners(Events::preGetGridFS)) {
            $this->eventManager->dispatchEvent(Events::preGetGridFS, new EventArgs($this, $prefix));
        }

        $gridfs = $this->doGetGridFS($prefix);

        if ($this->eventManager->hasListeners(Events::postGetGridFS)) {
            $this->eventManager->dispatchEvent(Events::postGetGridFS, new EventArgs($this, $gridfs));
        }

        return $gridfs;
    }

    /**
     * Return a new MongoDB instance for this database.
     *
     * @return \MongoDB
     */
    public function getMongoDB()
    {
        return $this->connection->getMongo()->selectDB($this->name);
    }

    /**
     * Return the name of this database.
     *
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Wrapper method for MongoDB::getProfilingLevel().
     *
     * @see http://php.net/manual/en/mongodb.getprofilinglevel.php
     * @return integer
     */
    public function getProfilingLevel()
    {
        return $this->getMongoDB()->getProfilingLevel();
    }

    /**
     * Wrapper method for MongoDB::setProfilingLevel().
     *
     * @see http://php.net/manual/en/mongodb.setprofilinglevel.php
     * @param integer $level
     * @return integer
     */
    public function setProfilingLevel($level)
    {
        return $this->getMongoDB()->setProfilingLevel($level);
    }

    /**
     * Wrapper method for MongoDB::getReadPreference().
     *
     * @see http://php.net/manual/en/mongodb.getreadpreference.php
     * @return array
     */
    public function getReadPreference()
    {
        return $this->getMongoDB()->getReadPreference();
    }

    /**
     * Wrapper method for MongoDB::setReadPreference().
     *
     * @see http://php.net/manual/en/mongodb.setreadpreference.php
     * @param string $readPreference
     * @param array  $tags
     * @return boolean
     */
    public function setReadPreference($readPreference, array $tags = null)
    {
        if (isset($tags)) {
            return $this->getMongoDB()->setReadPreference($readPreference, $tags);
        }

        return $this->getMongoDB()->setReadPreference($readPreference);
    }

    /**
     * Get whether secondary read queries are allowed for this database.
     *
     * This method wraps getSlaveOkay() for driver versions before 1.3.0. For
     * newer drivers, this method considers any read preference other than
     * PRIMARY as a true "slaveOkay" value.
     *
     * @see http://php.net/manual/en/mongodb.getreadpreference.php
     * @see http://php.net/manual/en/mongodb.getslaveokay.php
     * @return boolean
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

    /**
     * Set whether secondary read queries are allowed for this database.
     *
     * This method wraps setSlaveOkay() for driver versions before 1.3.0. For
     * newer drivers, this method wraps setReadPreference() and specifies
     * SECONDARY_PREFERRED.
     *
     * @see http://php.net/manual/en/mongodb.setreadpreference.php
     * @see http://php.net/manual/en/mongodb.setslaveokay.php
     * @param boolean $ok
     * @return boolean Previous slaveOk value
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
     * Wrapper method for MongoDB::lastError().
     *
     * @see http://php.net/manual/en/mongodb.lasterror.php
     * @return array
     */
    public function lastError()
    {
        return $this->getMongoDB()->lastError();
    }

    /**
     * Wrapper method for MongoDB::listCollections().
     *
     * @see http://php.net/manual/en/mongodb.listcollections.php
     * @return array
     */
    public function listCollections()
    {
        return $this->getMongoDB()->listCollections();
    }

    /**
     * Wrapper method for MongoDB::prevError().
     *
     * @deprecated 1.1 Deprecated in driver; will be removed for 1.2
     * @see http://php.net/manual/en/mongodb.preverror.php
     * @return array
     */
    public function prevError()
    {
        return $this->getMongoDB()->prevError();
    }

    /**
     * Wrapper method for MongoDB::repair().
     *
     * @see http://php.net/manual/en/mongodb.repair.php
     * @param boolean $preserveClonedFiles
     * @param boolean $backupOriginalFiles
     * @return array
     */
    public function repair($preserveClonedFiles = false, $backupOriginalFiles = false)
    {
        return $this->getMongoDB()->repair($preserveClonedFiles, $backupOriginalFiles);
    }

    /**
     * Wrapper method for MongoDB::resetError().
     *
     * @deprecated 1.1 Deprecated in driver; will be removed for 1.2
     * @see http://php.net/manual/en/mongodb.reseterror.php
     * @return array
     */
    public function resetError()
    {
        return $this->getMongoDB()->resetError();
    }

    /**
     * Wrapper method for MongoDB::selectCollection().
     *
     * This method will dispatch preSelectCollection and postSelectCollection
     * events.
     *
     * @see http://php.net/manual/en/mongodb.selectcollection.php
     * @param string $name
     * @return Collection
     */
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
     * Wrapper method for MongoDB::__get().
     *
     * @see http://php.net/manual/en/mongodb.get.php
     * @param string $name
     * @return \MongoCollection
     */
    public function __get($name)
    {
        return $this->getMongoDB()->__get($name);
    }

    /**
     * Wrapper method for MongoDB::__toString().
     *
     * @see http://www.php.net/manual/en/mongodb.--tostring.php
     * @return string
     */
    public function __toString()
    {
        return $this->name;
    }

    /**
     * Resolves a database reference.
     *
     * @see Database::getDBRef()
     * @param array $reference
     * @return array|null
     */
    protected function doGetDBRef(array $reference)
    {
        $database = $this;
        return $this->retry(function() use ($database, $reference) {
            return $database->getMongoDB()->getDBRef($reference);
        });
    }

    /**
     * Creates a collection.
     *
     * @see Database::createCollection()
     * @param string $name
     * @param array $options
     * @return Collection
     */
    protected function doCreateCollection($name, array $options)
    {
        if (version_compare(phpversion('mongo'), '1.4.0', '>=')) {
            $this->getMongoDB()->createCollection($name, $options);
        } else {
            $this->getMongoDB()->createCollection($name, $options['capped'], $options['size'], $options['max']);
        }

        return $this->doSelectCollection($name);
    }

    /**
     * Return a new GridFS instance.
     *
     * @see Database::getGridFS()
     * @param string $prefix
     * @return GridFS
     */
    protected function doGetGridFS($prefix)
    {
        return new GridFS($this->connection, $prefix, $this, $this->eventManager);
    }

    /**
     * Return a new Collection instance.
     *
     * @see Database::selectCollection()
     * @param string $name
     * @return Collection
     */
    protected function doSelectCollection($name)
    {
        return new Collection($this->connection, $name, $this, $this->eventManager, $this->numRetries);
    }

    /**
     * Conditionally retry a closure if it yields an exception.
     *
     * If the closure does not return successfully within the configured number
     * of retries, its first exception will be thrown.
     *
     * This method should not be used for write operations.
     *
     * @param \Closure $retry
     * @return mixed
     */
    protected function retry(\Closure $retry)
    {
        if ($this->numRetries) {
            $firstException = null;
            for ($i = 0; $i <= $this->numRetries; $i++) {
                try {
                    return $retry();
                } catch (\MongoException $e) {
                    if (!$firstException) {
                        $firstException = $e;
                    }
                    if ($i === $this->numRetries) {
                        throw $firstException;
                    }
                }
            }
        } else {
            return $retry();
        }
    }
}
