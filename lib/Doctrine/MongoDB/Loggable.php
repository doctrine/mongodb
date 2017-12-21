<?php

namespace Doctrine\MongoDB;

/**
 * Loggable interface.
 *
 * @since  1.0
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
interface Loggable
{
    /**
     * Log something using the configured logger callable.
     *
     * @param array $log
     */
    public function log(array $log);
}
