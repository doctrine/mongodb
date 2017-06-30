UPGRADE from 1.2 to 1.3
=======================

Pull requests completed for the 1.3.0 release:

 * [#227](https://github.com/doctrine/mongodb/pull/227): Specify time limit operation on a mongodb cursor
   * Adds `maxTimeMS()` method to query builder and cursor
 * [#233](https://github.com/doctrine/mongodb/pull/233): Allow Event Listeners the ability to modify context information in the event
   * Allows changes to the options for the following events: preAggregate, preBatchInsert, preDistinct, preFind, preFindAndRemove, preFindAndUpdate, preFindOne, preGetDBRef, preGroup, preInsert, preMapReduce, preNear, preRemove, postRemove, preSave, preUpdate, postUpdate
 * [#234](https://github.com/doctrine/mongodb/pull/234): Add support for `$comment` operator
   * Adds `comment()` method to query builder
 * [#235](https://github.com/doctrine/mongodb/pull/235): Add support for `$setOnInsert` operator
   * Adds `setOnInsert()` method to query builder
 * [#238](https://github.com/doctrine/mongodb/pull/238): Bump PHP and mongo version requirements
 * [#240](https://github.com/doctrine/mongodb/pull/240): Add new MongoDB 3.2 features to aggregation builder
   * Adds `sample()`, `indexStats()` and `lookup()` methods to aggregation builder
   * Adds `avg()`, `max()`, `min()`, `stdDevPop()`, `stdDevSamp()`, `sum()` methods to project stage
   * Adds `minDistance()` method to geoNear stage
   * Adds `includeArrayIndex()` and `preserveNullAndEmptyArrays()` methods to unwind stage
 * [#241](https://github.com/doctrine/mongodb/pull/241): Add query operators introduced with MongoDB 3.2
   * Adds `bitsAllClear()`, `bitsAllSet()`, `bitsAnyClear()`, `bitsAnySet()`, `caseSensitive()`, `diacriticSensitive()` methods to query builder
 * [#251](https://github.com/doctrine/mongodb/pull/251): Corrected fluent interface docblocks
 * [#255](https://github.com/doctrine/mongodb/pull/255): Add expr method to aggregation expression object
   * Adds `expr()` method to the aggregation Expr class
 * [#256](https://github.com/doctrine/mongodb/pull/256): Allow using operators in group stages
   * Adds all methods from the `Operator` class to the group stage
