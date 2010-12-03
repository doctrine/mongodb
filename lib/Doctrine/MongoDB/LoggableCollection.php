<?php

namespace Doctrine\MongoDB;

use Doctrine\Common\EventManager,
    Doctrine\ODM\Event\EventArgs;

class LoggableCollection extends Collection
{
    /**
     * A callable for logging statements.
     *
     * @var mixed
     */
    protected $loggerCallable;

    /**
     * Create a new MongoCollection instance that wraps a PHP MongoCollection instance
     * for a given ClassMetadata instance.
     *
     * @param MongoCollection $mongoCollection The MongoCollection instance.
     * @param Database $database The Database instance.
     * @param EventManager $evm The EventManager instance.
     * @param string $cmd Mongo cmd character.
     * @param Closure $loggerCallable The logger callable.
     */
    public function __construct(\MongoCollection $mongoCollection, Database $database, EventManager $evm, $cmd, $loggerCallable)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }
        $this->loggerCallable = $loggerCallable;
        parent::__construct($mongoCollection, $database, $evm, $cmd);
    }

    /**
     * Log something using the configured logger callable.
     *
     * @param array $log The array of data to log.
     */
    public function log(array $log)
    {
        $log['db'] = $this->database->getName();
        $log['collection'] = $this->getName();
        call_user_func_array($this->loggerCallable, array($log));
    }

    /** @override */
    public function batchInsert(array &$a, array $options = array())
    {
        $this->log(array(
            'batchInsert' => true,
            'num' => count($a),
            'data' => $a,
            'options' => $options
        ));

        return parent::batchInsert($a, $options);
    }

    /** @override */
    public function update($query, array $newObj, array $options = array())
    {
        $this->log(array(
            'update' => true,
            'query' => $query,
            'newObj' => $newObj,
            'options' => $options
        ));

        return parent::update($query, $newObj, $options);
    }

    /** @override */
    public function find(array $query = array(), array $fields = array())
    {
        $this->log(array(
            'find' => true,
            'query' => $query,
            'fields' => $fields
        ));

        return parent::find($query, $fields);
    }

    /** @override */
    public function findOne(array $query = array(), array $fields = array())
    {
        $this->log(array(
            'findOne' => true,
            'query' => $query,
            'fields' => $fields
        ));

        return parent::findOne($query, $fields);
    }

    /** @proxy */
    public function count(array $query = array(), $limit = 0, $skip = 0)
    {
        $this->log(array(
            'count' => true,
            'query' => $query,
            'limit' => $limit,
            'skip' => $skip
        ));

        return parent::count($query, $limit, $skip);
    }

    /** @proxy */
    public function createDBRef(array $a)
    {
        $this->log(array(
            'createDBRef' => true,
            'reference' => $a
        ));

        return parent::createDBRef($a);
    }

    /** @proxy */
    public function deleteIndex($keys)
    {
        $this->log(array(
            'deleteIndex' => true,
            'keys' => $keys
        ));

        return parent::deleteIndex($keys);
    }

    /** @proxy */
    public function deleteIndexes()
    {
        $this->log(array(
            'deleteIndexes' => true
        ));

        return parent::doDeleteIndexes();
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
    public function ensureIndex(array $keys, array $options)
    {
        $this->log(array(
            'ensureIndex' => true,
            'keys' => $keys,
            'options' => $options
        ));

        return parent::ensureIndex($keys, $options);
    }

    /** @proxy */
    public function getDBRef(array $reference)
    {
        $this->log(array(
            'getDBRef' => true,
            'reference' => $reference
        ));

        return parent::getDBRef($reference);
    }

    /** @proxy */
    public function group($keys, array $initial, $reduce, array $options = array())
    {
        $this->log(array(
            'group' => true,
            'keys' => $keys,
            'initial' => $initial,
            'reduce' => $reduce,
            'options' => $options
        ));

        return parent::group($keys, $initial, $reduce, $options);
    }

    /** @proxy */
    public function insert(array &$a, array $options = array())
    {
        $this->log(array(
            'insert' => true,
            'document' => $a,
            'options' => $options
        ));

        return parent::insert($a, $options);
    }

    /** @proxy */
    public function remove(array $query, array $options = array())
    {
        $this->log(array(
            'remove' => true,
            'query' => $query,
            'options' => $options
        ));

        return parent::remove($query, $options);
    }

    /** @proxy */
    public function save(array &$a, array $options = array())
    {
        $this->log(array(
            'save' => true,
            'document' => $a,
            'options' => $options
        ));

        return parent::save($a, $options);
    }

    /** @proxy */
    public function validate($scanData = false)
    {
        $this->log(array(
            'validate' => true,
            'scanData' => $scanData
        ));

        return parent::validate($scanData);
    }

}
