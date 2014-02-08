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

/**
 * Wrapper for the MongoDB class with logging functionality.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class LoggableDatabase extends Database implements Loggable
{
    /**
     * The logger callable.
     *
     * @var callable
     */
    protected $loggerCallable;

    /**
     * @var Logging\QueryLogger
     */
    protected $queryLogger;

    /**
     * Constructor.
     *
     * @param Connection   $connection     Connection used to create Collections
     * @param \MongoDB     $mongoDB        MongoDB instance being wrapped
     * @param EventManager $evm            EventManager instance
     * @param integer      $numRetries     Number of times to retry queries
     * @param callable     $loggerCallable The logger callable
     * @param Logging\QueryLogger $queryLogger The QueryLogger object
     */
    public function __construct(Connection $connection, \MongoDB $mongoDB, EventManager $evm, $numRetries, $loggerCallable = null, Logging\QueryLogger $queryLogger = null)
    {
        if ( ! is_callable($loggerCallable) && !($queryLogger instanceof Logging\QueryLogger)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback or $queryLogger must be an instance of Doctrine\MongoDB\Logging\QueryLogger');
        }
        parent::__construct($connection, $mongoDB, $evm, $numRetries);
        $this->loggerCallable = $loggerCallable;
        $this->queryLogger = $queryLogger;
    }

    /**
     * Log something using the configured logger callable.
     *
     * @see Loggable::log()
     * @param array $log
     */
    public function log(array $log)
    {
        $log['db'] = $this->getName();
        if ($this->loggerCallable) {
            call_user_func_array($this->loggerCallable, array($log));
        }

        if ($this->queryLogger instanceof Logging\QueryLogger) {
            $this->queryLogger->startQuery($log);
        }
    }

    private function logAfter()
    {
        if ($this->queryLogger instanceof Logging\QueryLogger) {
            $this->queryLogger->stopQuery();
        }
    }

    /**
     * @see Database::authenticate()
     */
    public function authenticate($username, $password)
    {
        $log = array(
            'authenticate' => true,
            'username' => $username,
            'password' => $password,
        );

        $this->log($log);
        $data = parent::authenticate($username, $password);
        $this->logAfter();
        return $data;
    }

    /**
     * @see Database::command()
     */
    public function command(array $data, array $options = array())
    {
        $log = array(
            'command' => true,
            'data' => $data,
            'options' => $options,
        );

        $this->log($log);
        $data = parent::command($data, $options);
        $this->logAfter();
        return $data;
    }

    /**
     * @see Database::createCollection()
     */
    public function createCollection($name, $cappedOrOptions = false, $size = 0, $max = 0)
    {
        $options = is_array($cappedOrOptions)
            ? array_merge(array('capped' => false, 'size' => 0, 'max' => 0), $cappedOrOptions)
            : array('capped' => $cappedOrOptions, 'size' => $size, 'max' => $max);

        $log = array(
            'createCollection' => true,
            'name' => $name,
            'options' => $options,
            /* @deprecated 1.1 Replaced by options; will be removed for 2.0 */
            'capped' => $options['capped'],
            'size' => $options['size'],
            'max' => $options['max'],
        );

        $this->log($log);
        $data = parent::createCollection($name, $options);
        $this->logAfter();
        return $data;
    }

    /**
     * @see Database::drop()
     */
    public function drop()
    {
        $log = array('dropDatabase' => true);

        $this->log($log);
        $data = parent::drop();
        $this->logAfter();
        return $data;
    }

    /**
     * @see Database::execute()
     */
    public function execute($code, array $args = array())
    {
        $log = array(
            'execute' => true,
            'code' => $code,
            'args' => $args,
        );

        $this->log($log);
        $data = parent::execute($code, $args);
        $this->logAfter();
        return $data;
    }

    /**
     * @see Database::getDBRef()
     */
    public function getDBRef(array $ref)
    {
        $log = array(
            'getDBRef' => true,
            'reference' => $ref,
        );

        $this->log($log);
        $data = parent::getDBRef($ref);
        $this->logAfter();
        return $data;
    }

    /**
     * Return a new LoggableCollection instance.
     *
     * @see Database::doSelectCollection()
     * @param string $name
     * @return LoggableCollection
     */
    protected function doSelectCollection($name)
    {
        $mongoCollection = $this->mongoDB->selectCollection($name);

        return new LoggableCollection($this, $mongoCollection, $this->eventManager, $this->numRetries, $this->loggerCallable, $this->queryLogger);
    }
}
