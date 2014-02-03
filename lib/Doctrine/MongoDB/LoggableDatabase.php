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
    public function __construct(Connection $connection, \MongoDB $mongoDB, EventManager $evm, $numRetries, $loggerCallable, Logging\QueryLogger $queryLogger = null)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
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
        if($this->loggerCallable){
            call_user_func_array($this->loggerCallable, array($log));
        }

        if($this->queryLogger instanceof Logging\QueryLogger){
            $this->queryLogger->startQuery($log);
        }
    }

    /**
     * @param array $log
     * @param callable $callback
     * @return mixed
     */
    protected function logMethod($log, $callback) {
        $this->log($log);
        $data = call_user_func($callback);
        if($this->queryLogger instanceof Logging\QueryLogger){
            $this->queryLogger->stopQuery();
        }
        return $data;
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

        return $this->logMethod($log, function () use ($username, $password) {
            return parent::authenticate($username, $password);
        });
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

        return $this->logMethod($log, function () use ($data, $options) {
            return parent::command($data, $options);
        });
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

        return $this->logMethod($log, function () use ($name, $options) {
            return parent::createCollection($name, $options);
        });
    }

    /**
     * @see Database::drop()
     */
    public function drop()
    {
        $log = array('dropDatabase' => true);

        return $this->logMethod($log,
            function () {
                return parent::drop();
            });
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

        return $this->logMethod($log, function () use ($code, $args) {
            return parent::execute($code, $args);
        });
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

        return $this->logMethod($log, function () use ($ref) {
            return parent::getDBRef($ref);
        });
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
