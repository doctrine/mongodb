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
 * Wrapper for the MongoDB class.
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
     * The Connection instance to which this database belongs.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * The EventManager used to dispatch events.
     *
     * @var EventManager
     */
    protected $eventManager;

    /**
     * The MongoDB instance being wrapped.
     *
     * @var \MongoDB
     */
    protected $mongoDB;

    /**
     * Number of times to retry queries.
     *
     * @var integer
     */
    protected $numRetries;

    /**
     * Constructor.
     *
     * @param Connection      $connection Connection to which this database belongs
     * @param \MongoDB        $mongoDB    MongoDB instance being wrapped
     * @param EventManager    $evm        EventManager instance
     * @param boolean|integer $numRetries Number of times to retry queries
     */
    public function __construct(Connection $connection, \MongoDB $mongoDB, EventManager $evm, $numRetries = 0)
    {
        $this->connection = $connection;
        $this->mongoDB = $mongoDB;
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
        return $this->mongoDB->authenticate($username, $password);
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
        $options = isset($options['timeout']) ? $this->convertSocketTimeout($options) : $options;

        return $this->mongoDB->command($data, $options);
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

        $options['capped'] = (boolean) $options['capped'];
        $options['size'] = (integer) $options['size'];
        $options['max'] = (integer) $options['max'];

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
        return $this->mongoDB->createDBRef($collection, $a);
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

        $result = $this->mongoDB->drop();

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
        return $this->mongoDB->dropCollection($coll);
    }

    /**
     * Wrapper method for MongoDB::execute().
     *
     * @see http://php.net/manual/en/mongodb.execute.php
     * @return array
     */
    public function execute($code, array $args = array())
    {
        return $this->mongoDB->execute($code, $args);
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
        return $this->mongoDB->forceError();
    }

    /**
     * Return the connection for this database.
     *
     * @return Connection
     */
    public function getConnection()
    {
        return $this->connection;
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
     * Return the MongoDB instance being wrapped.
     *
     * @return \MongoDB
     */
    public function getMongoDB()
    {
        return $this->mongoDB;
    }

    /**
     * Return the name of this database.
     *
     * @return string
     */
    public function getName()
    {
        return $this->mongoDB->__toString();
    }

    /**
     * Wrapper method for MongoDB::getProfilingLevel().
     *
     * @see http://php.net/manual/en/mongodb.getprofilinglevel.php
     * @return integer
     */
    public function getProfilingLevel()
    {
        return $this->mongoDB->getProfilingLevel();
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
        return $this->mongoDB->setProfilingLevel($level);
    }

    /**
     * Wrapper method for MongoDB::getReadPreference().
     *
     * For driver versions between 1.3.0 and 1.3.3, the return value will be
     * converted for consistency with {@link Database::setReadPreference()}.
     *
     * @see http://php.net/manual/en/mongodb.getreadpreference.php
     * @return array
     */
    public function getReadPreference()
    {
        return ReadPreference::convertReadPreference($this->mongoDB->getReadPreference());
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
            return $this->mongoDB->setReadPreference($readPreference, $tags);
        }

        return $this->mongoDB->setReadPreference($readPreference);
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
            return $this->mongoDB->getSlaveOkay();
        }

        $readPref = $this->getReadPreference();

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
            return $this->mongoDB->setSlaveOkay($ok);
        }

        $prevSlaveOkay = $this->getSlaveOkay();

        if ($ok) {
            // Preserve existing tags for non-primary read preferences
            $readPref = $this->getReadPreference();
            $tags = ! empty($readPref['tagsets']) ? $readPref['tagsets'] : array();
            $this->mongoDB->setReadPreference(\MongoClient::RP_SECONDARY_PREFERRED, $tags);
        } else {
            $this->mongoDB->setReadPreference(\MongoClient::RP_PRIMARY);
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
        return $this->mongoDB->lastError();
    }

    /**
     * Wrapper method for MongoDB::listCollections().
     *
     * @see http://php.net/manual/en/mongodb.listcollections.php
     * @return array
     */
    public function listCollections()
    {
        return $this->mongoDB->listCollections();
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
        return $this->mongoDB->prevError();
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
        return $this->mongoDB->repair($preserveClonedFiles, $backupOriginalFiles);
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
        return $this->mongoDB->resetError();
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
        return $this->mongoDB->__get($name);
    }

    /**
     * Wrapper method for MongoDB::__toString().
     *
     * @see http://www.php.net/manual/en/mongodb.--tostring.php
     * @return string
     */
    public function __toString()
    {
        return $this->mongoDB->__toString();
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
        $mongoDB = $this->mongoDB;
        return $this->retry(function() use ($mongoDB, $reference) {
            return $mongoDB->getDBRef($reference);
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
            $this->mongoDB->createCollection($name, $options);
        } else {
            $this->mongoDB->createCollection($name, $options['capped'], $options['size'], $options['max']);
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
        $mongoGridFS = $this->mongoDB->getGridFS($prefix);

        return new GridFS($this, $mongoGridFS, $this->eventManager);
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
        $mongoCollection = $this->mongoDB->selectCollection($name);

        return new Collection($this, $mongoCollection, $this->eventManager, $this->numRetries);
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
        if ($this->numRetries < 1) {
            return $retry();
        }

        $firstException = null;

        for ($i = 0; $i <= $this->numRetries; $i++) {
            try {
                return $retry();
            } catch (\MongoException $e) {
                if ($firstException === null) {
                    $firstException = $e;
                }
                if ($i === $this->numRetries) {
                    throw $firstException;
                }
            }
        }
    }

    /**
     * Convert "timeout" write option to "socketTimeoutMS" for driver version
     * 1.5.0+.
     *
     * @param array $options
     * @return array
     */
    protected function convertSocketTimeout(array $options)
    {
        if (version_compare(phpversion('mongo'), '1.5.0', '<')) {
            return $options;
        }

        if (isset($options['timeout']) && ! isset($options['socketTimeoutMS'])) {
            $options['socketTimeoutMS'] = $options['timeout'];
            unset($options['timeout']);
        }

        return $options;
    }
}
