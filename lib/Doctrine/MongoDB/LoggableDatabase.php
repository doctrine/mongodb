<?php

namespace Doctrine\MongoDB;

use Doctrine\Common\EventManager;

class LoggableDatabase extends Database
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
     * @param MongoDB $mongoDB  The MongoDB instance to wrap.
     */
    public function __construct(\MongoDB $mongoDB, EventManager $evm, $cmd, $loggerCallable)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        parent::__construct($mongoDB, $evm, $cmd);
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

    /** @proxy */
    public function authenticate($username, $password)
    {
        $this->log(array(
            'authenticate' => true,
            'username' => $username,
            'password' => $password
        ));

        return parent::authenticate($username, $password);
    }

    /** @proxy */
    public function command(array $data)
    {
        $this->log(array(
            'command' => true,
            'data' => $data
        ));

        return parent::command($data);
    }

    /** @proxy */
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

    /** @proxy */
    public function createDBRef($collection, $a)
    {
        $this->log(array(
            'createDBRef' => true,
            'collection' => $collection,
            'reference' => $a
        ));

        return parent::createDBRef($collection, $a);
    }

    /** @proxy */
    public function drop()
    {
        $this->log(array(
            'drop' => true
        ));

        return parent::drop();
    }

    /** @proxy */
    public function execute($code, array $args = array())
    {
        $this->log(array(
            'execute' => true,
            'code' => $code,
            'args' => $args
        ));

        return parent::execute($code, $args);
    }

    /** @proxy */
    public function getDBRef(array $ref)
    {
        $this->log(array(
            'getDBRef' => true,
            'reference' => $ref
        ));

        return parent::getDBRef($ref);
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
            $collection, $this, $this->eventManager, $this->cmd, $this->loggerCallable
        );
    }

}
