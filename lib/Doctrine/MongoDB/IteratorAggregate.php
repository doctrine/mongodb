<?php

namespace Doctrine\MongoDB;

/**
 * IteratorAggregate interface.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 * @author Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
interface IteratorAggregate extends \IteratorAggregate, \Countable
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
