<?php
/**
 * Created by PhpStorm.
 * User: woodworker
 * Date: 2/3/14
 * Time: 10:59 PM
 */

namespace Doctrine\MongoDB\Logging;


class LoggerChain implements QueryLogger {

    /**
     * @var array|QueryLogger[]
     */
    private $logger = array();

    /**
     * add logger to chain
     *
     * @param QueryLogger $queryLogger
     */
    public function addLogger(QueryLogger $queryLogger){
        $this->logger[] = $queryLogger;
    }

    public function startQuery($parameter)
    {
        foreach($this->logger as $logger)
        {
            $logger->startQuery($parameter);
        }
    }

    public function stopQuery()
    {
        foreach($this->logger as $logger)
        {
            $logger->stopQuery();
        }
    }
}