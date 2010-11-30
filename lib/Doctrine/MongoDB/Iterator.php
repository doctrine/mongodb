<?php

namespace Doctrine\MongoDB;

use Countable, Iterator as BaseIterator;

interface Iterator extends BaseIterator, Countable
{
    function toArray();
    function getSingleResult();
}