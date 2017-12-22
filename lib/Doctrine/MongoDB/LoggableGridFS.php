<?php

namespace Doctrine\MongoDB;

use Doctrine\Common\EventManager;
use Doctrine\MongoDB\Traits\LoggableCollectionTrait;

class LoggableGridFS extends GridFS implements Loggable
{
    use LoggableCollectionTrait;

    /**
     * @param Database     $database    Database to which this collection belongs
     * @param \MongoGridFS $mongoGridFS MongoGridFS instance being wrapped
     * @param EventManager $evm         EventManager instance
     * @param integer      $numRetries  Number of times to retry queries
     * @param callable     $loggerCallable  The logger callable
     */
    public function __construct(Database $database, \MongoGridFS $mongoGridFS, EventManager $evm, $numRetries = 0, $loggerCallable = null)
    {
        if ( ! is_callable($loggerCallable)) {
            throw new \InvalidArgumentException('$loggerCallable must be a valid callback');
        }

        parent::__construct($database, $mongoGridFS, $evm, $numRetries, $loggerCallable);

        $this->loggerCallable = $loggerCallable;
    }

    /*
     * @see GridFS::storeFile()
     */
    public function storeFile($file, array &$document, array $options = [])
    {
        $this->log([
            'storeFile' => true,
            'count' => count($document),
            'options' => $options,
        ]);

        return parent::storeFile($file, $document, $options);
    }
}
