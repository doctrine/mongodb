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
    Doctrine\MongoDB\Logging\MethodLogger;

/**
 * Wrapper for the PHP MongoDB class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class LoggableDatabase extends Database
{
    /**
     * A logger.
     *
     * @var MethodLogger
     */
    protected $logger;

    /**
     * Create a new MongoDB instance which wraps a PHP MongoDB instance.
     *
     * @param MongoDB $mongoDB  The MongoDB instance to wrap.
     * @param EventManager $evm  The EventManager instance.
     * @param string $cmd  The MongoDB cmd character.
     * @param MethodLogger $logger A logger
     */
    public function __construct(\MongoDB $mongoDB, EventManager $evm, $cmd, MethodLogger $logger)
    {
        parent::__construct($mongoDB, $evm, $cmd);

        $this->logger = $logger;
    }

    /** @proxy */
    public function authenticate($username, $password)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_DATABASE, __FUNCTION__, array('username' => $username), $this->getName());

        $retval = parent::authenticate($username, $password);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function command(array $data)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_DATABASE, __FUNCTION__, array('data' => $data), $this->getName());

        $retval = parent::command($data);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_DATABASE, __FUNCTION__, array(
            'name' => $name,
            'capped' => $capped,
            'size' => $size,
            'max' => $max,
        ), $this->getName());

        $retval = parent::createCollection($name, $capped, $size, $max);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function createDBRef($collection, $a)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_DATABASE, __FUNCTION__, array(
            'collection' => $collection,
            'data' => $a,
        ), $this->getName());

        $retval = parent::createDBRef($collection, $a);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function drop()
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_DATABASE, __FUNCTION__, array(), $this->getName());

        $retval = parent::drop();

        $this->logger->stopMethod();

        return $return;
    }

    /** @proxy */
    public function execute($code, array $args = array())
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_DATABASE, __FUNCTION__, array(
            'code' => $code,
            'args' => $args,
        ), $this->getName());

        $retval = parent::execute($code, $args);

        $this->logger->stopMethod();

        return $retval;
    }

    /** @proxy */
    public function getDBRef(array $ref)
    {
        $this->logger->startMethod(MethodLogger::CONTEXT_DATABASE, __FUNCTION__, array('ref' => $ref), $this->getName());

        $retval = parent::getDBRef($ref);

        $this->logger->stopMethod();

        return $retval;
    }

    /**
     * Method which wraps a MongoCollection with a Doctrine\MongoDB\Collection instance.
     *
     * @param MongoCollection $collection
     * @return Collection $coll
     */
    protected function wrapCollection(\MongoCollection $collection)
    {
        return new LoggableCollection(
            $collection, $this, $this->eventManager, $this->cmd, $this->logger
        );
    }

}
