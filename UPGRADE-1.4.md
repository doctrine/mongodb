Upgrade from 1.3 to 1.4
=======================

PHP version support
-------------------

 * Support for PHP 5.5 has been dropped as it has reached its end of life.
 * PHP 7 and PHP 7.1 are supported using [mongo-php-adapter](https://github.com/alcaeus/mongo-php-adapter).

Query builder
-------------

 * The `update()` and `multiple()` methods have been deprecated. Use `updateOne`
   or `updateMany` instead.
 * The `addAnd()`, `addNor()` and `addOr()` methods now accept multiple parameters.
   Before:
   ```php
   $builder
       ->addAnd($someExpression)
       ->addAnd($otherExpression);
   ```

   After:
   ```php
   $builder->addAnd($someExpression, $otherExpression);
   ```

Aggregation builder
-------------------

 * The `addAnd()` and `addOr()` methods now accept multiple parameters.

Connection
----------

 * Passing driver options to the connection class is now supported. You can pass
   a stream context as shown in the PHP documentation:
   ```php
   $context = stream_context_create([
       'mongodb' => [
           'log_cmd_insert' => function () {
               // Logic goes here...
           }
       ],
       'ssl' => [
           'allow_self_signed' => false,
       ]
   ]);
   $connection = new \Doctrine\MongoDB\Connection(null, [], null, null, ['context' => $context]);
