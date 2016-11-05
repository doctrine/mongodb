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
     * Constructor.
     *
     * @param Connection   $connection     Connection used to create Collections
     * @param \MongoDB     $mongoDB        MongoDB instance being wrapped
     * @param EventManager $evm            EventManager instance
     * @param integer      $numRetries     Number of times to retry queries
     * @param callable     $loggerCallable The logger callable
     */
    public function __construct(Connection $connection, \MongoDB $mongoDB, EventManager $evm, $numRetries, $loggerCallable)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        parent::__construct($connection, $mongoDB, $evm, $numRetries);
        $this->loggerCallable = $loggerCallable;
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
        call_user_func($this->loggerCallable, $log);
    }

    /**
     * @see Database::authenticate()
     */
    public function authenticate($username, $password)
    {
        $this->log([
            'authenticate' => true,
            'username' => $username,
            'password' => $password,
        ]);

        return parent::authenticate($username, $password);
    }

    /**
     * @see Database::command()
     */
    public function command(array $data, array $options = [], &$hash = null)
    {
        $this->log([
            'command' => true,
            'data' => $data,
            'options' => $options,
        ]);

        if (func_num_args() > 2) {
            return parent::command($data, $options, $hash);
        }

        return parent::command($data, $options);
    }

    /**
     * @see Database::createCollection()
     */
    public function createCollection($name, $cappedOrOptions = false, $size = 0, $max = 0)
    {
        $options = is_array($cappedOrOptions)
            ? array_merge(['capped' => false, 'size' => 0, 'max' => 0], $cappedOrOptions)
            : ['capped' => $cappedOrOptions, 'size' => $size, 'max' => $max];

        $this->log([
            'createCollection' => true,
            'name' => $name,
            'options' => $options,
            /* @deprecated 1.1 Replaced by options; will be removed for 2.0 */
            'capped' => $options['capped'],
            'size' => $options['size'],
            'max' => $options['max'],
        ]);

        return parent::createCollection($name, $options);
    }

    /**
     * @see Database::drop()
     */
    public function drop()
    {
        $this->log(['dropDatabase' => true]);

        return parent::drop();
    }

    /**
     * @see Database::execute()
     */
    public function execute($code, array $args = [])
    {
        $this->log([
            'execute' => true,
            'code' => $code,
            'args' => $args,
        ]);

        return parent::execute($code, $args);
    }

    /**
     * @see Database::getDBRef()
     */
    public function getDBRef(array $ref)
    {
        $this->log([
            'getDBRef' => true,
            'reference' => $ref,
        ]);

        return parent::getDBRef($ref);
    }

    /**
     * @see Database::doGetGridFS()
     */
    protected function doGetGridFS($prefix)
    {
        $mongoGridFS = $this->mongoDB->getGridFS($prefix);

        return new LoggableGridFS($this, $mongoGridFS, $this->eventManager, $this->numRetries, $this->loggerCallable);
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

        return new LoggableCollection($this, $mongoCollection, $this->eventManager, $this->numRetries, $this->loggerCallable);
    }
}
