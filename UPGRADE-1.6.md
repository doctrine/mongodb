Upgrade from 1.5 to 1.6
=======================

Aggregation builder
-------------------

 * Fixes a BC break when converting expression objects in the
 `Doctrine\MongoDB\Aggregation\Expr` class.
 * Adds extension points to customize expression conversion in the following stages:
    * `$bucket`
    * `$bucketAuto`
    * `$graphLookup`
    * `$replaceRoot`

Version support
---------------
 * Support for MongoDB server versions below 3.0 has been dropped. These are no
 longer supported by MongoDB. We recommend you upgrade to a recent version
 * The 1.5 version of the legacy driver is no longer supported. This library now
 requires at least version 1.6.7 of the legacy driver.
 * PHP 7.2 is also supported using [mongo-php-adapter](https://github.com/alcaeus/mongo-php-adapter)
