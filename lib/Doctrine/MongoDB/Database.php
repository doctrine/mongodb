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
    /** The PHP MongoDB instance being wrapped */
    protected $mongoDB;

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
     * Create a new MongoDB instance which wraps a PHP MongoDB instance.
     *
     * @param MongoDB $mongoDB  The MongoDB instance to wrap.
     * @param EventManager $evm  The EventManager instance.
     * @param string $cmd  The MongoDB cmd character.
     */
    public function __construct(\MongoDB $mongoDB, EventManager $evm, $cmd)
    {
        $this->mongoDB = $mongoDB;
        $this->eventManager = $evm;
        $this->cmd = $cmd;
    }

    /**
     * Gets the name of this database
     *
     * @return string $name
     */
    public function getName()
    {
        return $this->__toString();
    }

    /**
     * Get the MongoDB instance being wrapped.
     *
     * @return MongoDB $mongoDB
     */
    public function getMongoDB()
    {
        return $this->mongoDB;
    }

    /** @proxy */
    public function authenticate($username, $password)
    {
        return $this->mongoDB->authenticate($username, $password);
    }

    /** @proxy */
    public function command(array $data)
    {
        return $this->mongoDB->command($data);
    }

    /** @proxy */
    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        if ($this->eventManager->hasListeners(Events::preCreateCollection)) {
            $this->eventManager->dispatchEvent(Events::preCreateCollection, new CreateCollectionEventArgs($this, $name, $capped, $size, $max));
        }

        $result = $this->mongoDB->createCollection($name, $capped, $size, $max);

        if ($this->eventManager->hasListeners(Events::postCreateCollection)) {
            $this->eventManager->dispatchEvent(Events::postCreateCollection, new EventArgs($this, $prefix));
        }

        return $result;
    }

    /** @proxy */
    public function createDBRef($collection, $a)
    {
        return $this->mongoDB->createDBRef($collection, $a);
    }

    /** @proxy */
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

    /** @proxy */
    public function dropCollection($coll)
    {
        return $this->mongoDB->dropCollection($coll);
    }

    /** @proxy */
    public function execute($code, array $args = array())
    {
        return $this->mongoDB->execute($code, $args);
    }

    /** @proxy */
    public function forceError()
    {
        return $this->mongoDB->forceError();
    }

    /** @proxy */
    public function __get($name)
    {
        return $this->mongoDB->__get($name);
    }

    /** @proxy */
    public function getDBRef(array $ref)
    {
        return $this->mongoDB->getDBRef($ref);
    }

    /** @proxy */
    public function getGridFS($prefix = 'fs')
    {
        if ($this->eventManager->hasListeners(Events::preGetGridFS)) {
            $this->eventManager->dispatchEvent(Events::preGetGridFS, new EventArgs($this, $prefix));
        }

        $gridFS = $this->mongoDB->getGridFS($prefix);
        $gridFS = $this->wrapGridFS($gridFS);

        if ($this->eventManager->hasListeners(Events::preGetGridFS)) {
            $this->eventManager->dispatchEvent(Events::preGetGridFS, new EventArgs($this, $gridFS));
        }

        return $gridFS;
    }

    protected function wrapGridFS(\MongoGridFS $gridFS)
    {
        return new GridFS(
            $gridFS, $this, $this->eventManager, $this->cmd
        );
    }

    /** @proxy */
    public function getProfilingLevel()
    {
        return $this->mongoDB->getProfilingLevel();
    }

    /** @proxy */
    public function lastError()
    {
        return $this->mongoDB->lastError();
    }

    /** @proxy */
    public function listCollections()
    {
        return $this->mongoDB->listCollections();
    }

    /** @proxy */
    public function prevError()
    {
        return $this->mongoDB->prevError();
    }

    /** @proxy */
    public function repair($preserveClonedFiles = false, $backupOriginalFiles = false)
    {
        return $this->mongoDB->repair($preserveClonedFiles, $backupOriginalFiles);
    }

    /** @proxy */
    public function resetError()
    {
        return $this->mongoDB->resetError();
    }

    /** @proxy */
    public function selectCollection($name)
    {
        if ($this->eventManager->hasListeners(Events::preSelectCollection)) {
            $this->eventManager->dispatchEvent(Events::preSelectCollection, new EventArgs($this, $name));
        }

        $collection = $this->mongoDB->selectCollection($name);
        $collection = $this->wrapCollection($collection);

        if ($this->eventManager->hasListeners(Events::postSelectCollection)) {
            $this->eventManager->dispatchEvent(Events::postSelectCollection, new EventArgs($this, $collection));
        }

        return $collection;
    }

    /**
     * Method which wraps a MongoCollection with a Doctrine\MongoDB\Collection instance.
     *
     * @param MongoCollection $collection
     * @return Collection $coll
     */
    protected function wrapCollection(\MongoCollection $collection)
    {
        return new Collection(
            $collection, $this, $this->eventManager, $this->cmd
        );
    }

    /** @proxy */
    public function setProfilingLevel($level)
    {
        return $this->mongoDB->setProfilingLevel($level);
    }

    /** @proxy */
    public function __toString()
    {
        return $this->mongoDB->__toString();
    }
}