<?php

namespace Doctrine\MongoDB;

/**
 * Configuration class for creating a Connection.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class Configuration
{
    /**
     * Array of attributes for this configuration instance.
     *
     * @var array
     */
    protected $attributes = [
        'mongoCmd' => '$',
        'retryConnect' => 0,
        'retryQuery' => 0,
    ];

    /**
     * Gets the logger callable.
     *
     * @return callable
     */
    public function getLoggerCallable()
    {
        return isset($this->attributes['loggerCallable']) ? $this->attributes['loggerCallable'] : null;
    }

    /**
     * Set the logger callable.
     *
     * @param callable $loggerCallable
     */
    public function setLoggerCallable($loggerCallable)
    {
        $this->attributes['loggerCallable'] = $loggerCallable;
    }

    /**
     * Get the MongoDB command prefix.
     *
     * @deprecated 1.1 No longer supported; will be removed for 1.2
     * @return string
     */
    public function getMongoCmd()
    {
        trigger_error('MongoDB command prefix option is no longer used', E_USER_DEPRECATED);
        return $this->attributes['mongoCmd'];
    }

    /**
     * Set the MongoDB command prefix.
     *
     * @deprecated 1.1 No longer supported; will be removed for 1.2
     * @param string $cmd
     */
    public function setMongoCmd($cmd)
    {
        trigger_error('MongoDB command prefix option is no longer used', E_USER_DEPRECATED);
        $this->attributes['mongoCmd'] = $cmd;
    }

    /**
     * Get the number of times to retry connection attempts after an exception.
     *
     * @return integer
     */
    public function getRetryConnect()
    {
        return $this->attributes['retryConnect'];
    }

    /**
     * Set the number of times to retry connection attempts after an exception.
     *
     * @param boolean|integer $retryConnect
     */
    public function setRetryConnect($retryConnect)
    {
        $this->attributes['retryConnect'] = (integer) $retryConnect;
    }

    /**
     * Get the number of times to retry queries after an exception.
     *
     * @return integer
     */
    public function getRetryQuery()
    {
        return $this->attributes['retryQuery'];
    }

    /**
     * Set the number of times to retry queries after an exception.
     *
     * @param boolean|integer $retryQuery
     */
    public function setRetryQuery($retryQuery)
    {
        $this->attributes['retryQuery'] = (integer) $retryQuery;
    }
}
