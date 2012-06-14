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
 * Wrapper for the PHP MongoDB class.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class LoggableDatabase extends Database implements Loggable
{
    /**
     * A callable for logging statements.
     *
     * @var mixed
     */
    protected $loggerCallable;

    /**
     * Create a new MongoDB instance which wraps a PHP MongoDB instance.
     *
     * @param Connection $connection The Doctrine Connection instance.
     * @param string $name The name of the database.
     * @param EventManager $evm  The EventManager instance.
     * @param string $cmd  The MongoDB cmd character.
     * @param mixed $numRetries Number of times to retry queries.
     * @param Closure $loggerCallable  Logger callback function.
     */
    public function __construct(Connection $connection, $name, EventManager $evm, $cmd, $numRetries, $loggerCallable)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        parent::__construct($connection, $name, $evm, $cmd, $numRetries);
        $this->loggerCallable = $loggerCallable;
    }

    /**
     * Log something using the configured logger callable.
     *
     * @param array $log The array of data to log.
     */
    public function log(array $log)
    {
        $log['db'] = $this->getName();
        call_user_func_array($this->loggerCallable, array($log));
    }

    public function authenticate($username, $password)
    {
        $this->log(array(
            'authenticate' => true,
            'username' => $username,
            'password' => $password
        ));

        return parent::authenticate($username, $password);
    }

    public function command(array $data, array $options = array())
    {
        $this->log(array(
            'command' => true,
            'data' => $data,
            'options' => $options
        ));

        return parent::command($data, $options);
    }

    public function createCollection($name, $capped = false, $size = 0, $max = 0)
    {
        $this->log(array(
            'createCollection' => true,
            'capped' => $capped,
            'size' => $size,
            'max' => $max
        ));

        return parent::createCollection($name, $capped, $size, $max);
    }

    public function createDBRef($collection, $a)
    {
        $this->log(array(
            'createDBRef' => true,
            'collection' => $collection,
            'reference' => $a
        ));

        return parent::createDBRef($collection, $a);
    }

    public function drop()
    {
        $this->log(array(
            'dropDatabase' => true
        ));

        return parent::drop();
    }

    public function execute($code, array $args = array())
    {
        $this->log(array(
            'execute' => true,
            'code' => $code,
            'args' => $args
        ));

        return parent::execute($code, $args);
    }

    public function getDBRef(array $ref)
    {
        $this->log(array(
            'getDBRef' => true,
            'reference' => $ref
        ));

        return parent::getDBRef($ref);
    }

    /**
     * Method which creates a Doctrine\MongoDB\Collection instance.
     *
     * @param string $name
     * @return Collection $coll
     */
    protected function doSelectCollection($name)
    {
        return new LoggableCollection(
            $this->connection, $name, $this, $this->eventManager, $this->cmd, $this->loggerCallable
        );
    }
}