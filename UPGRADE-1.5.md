Upgrade from 1.4 to 1.5
=======================

Aggregation builder
-------------------

 * Add support for aggregation pipeline stages added in MongoDB 3.4:
   * `$addFields`
   * `$bucket`
   * `$bucketAuto`
   * `$collStats`
   * `$count`
   * `$facet`
   * `$graphLookup`
   * `$replaceRoot`
   * `$sortByCount`
 * Add support for aggregation expression operators added in MongoDB 3.4:
   * `$in`
   * `$indexOfArray`
   * `$range`
   * `$reverseArray`
   * `$reduce`
   * `$zip`
   * `$indexOfBytes`
   * `$indexOfCP`
   * `$split`
   * `$strLenBytes`
   * `$strLenCP`
   * `$substrBytes`
   * `$substrCP`
   * `$switch`
   * `$isoDayOfWeek`
   * `$isoWeek`
   * `$isoWeekYear`
   * `$type`
 * The `$project` stage now supports field exclusion via the new `excludeFields` 
 method.

Deprecations
------------
 * The `excludeIdField` method in the `$project` aggregation pipeline stage has 
 been deprecated in favor of the new `excludeFields` method.
 * The protected methods relating to the `$switch` aggregation expression 
 operator in `Doctrine\MongoDB\Aggregation\Expr`are deprecated. They will be 
 renamed as follows when the aggregation builder is moved to Doctrine MongoDB 
 ODM:
   * `caseInternal` => `case`
   * `defaultInternal` => `default`
   * `switchInternal` => `switch`
   * `thenInternal` => `then`
 * The magic `__call` method in `Doctrine\MongoDB\Aggregation\Expr` will also be
 removed.
