<?php

namespace Doctrine\MongoDB;

/**
 * Iterator interface.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
interface Iterator extends \Iterator, \Countable
{
    /**
     * Return the first element or null if no elements exist.
     *
     * @return array|object|null
     */
    function getSingleResult();

    /**
     * Return all elements as an array.
     *
     * @return array
     */
    function toArray();
}
