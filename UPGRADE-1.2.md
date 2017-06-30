UPGRADE from 1.1 to 1.2
=======================

Pull requests completed for the 1.2.0 release:

 * [#171](https://github.com/doctrine/mongodb/pull/171): Implement `$minDistance` query operator and geoNear option
   * Adds `minDistance()` method to query builder
 * [#183](https://github.com/doctrine/mongodb/pull/183): Rewrite `Collection::update()` "multi" option to "multiple"
 * [#184](https://github.com/doctrine/mongodb/pull/184): Query builder support for $text operator in MongoDB 2.6
   * Adds `language()`, `text()`, `selectMeta()`, and `sortMeta()` methods to query builder
 * [#186](https://github.com/doctrine/mongodb/pull/186): Fixed `Connection::convertWriteTimeout()` docs
 * [#192](https://github.com/doctrine/mongodb/pull/192): Support `$meta` expressions in `Cursor::sort()`
   * This is typically used for sorting by text search scores
 * [#197](https://github.com/doctrine/mongodb/pull/197): Support aggregation command cursors and client options
   * Introduces a CommandCursor class, which decorates a MongoCommandCursor
   * Command cursors require driver 1.5+
   * Socket timeouts for command cursors require driver 1.6+
 * [#201](https://github.com/doctrine/mongodb/pull/201): Fix issue when an operator follows `equals()`
 * [#203](https://github.com/doctrine/mongodb/pull/203): Ensure Query projection option is renamed for findAndModify
 * [#205](https://github.com/doctrine/mongodb/pull/205): Don't pass empty arrays to array_combine()
 * [#209](https://github.com/doctrine/mongodb/pull/209): Add a common cursor interface
   * The Cursor and EagerCursor classes implement this interface
 * [#212](https://github.com/doctrine/mongodb/pull/212): Query builder support for new update operators in MongoDB 2.6
   * Adds `bitAnd()`, `bitOr()`, `bitXor()`, `currentDate()`, `max()`, `min()`, and `mul()` methods to query builder
 * [#213](https://github.com/doctrine/mongodb/pull/213): Add aggregation builder
   * The builder may be created with `Collection::createAggregationBuilder()`
   * Top-level builder methods correspond to pipeline operators
 * [#214](https://github.com/doctrine/mongodb/pull/214): Removes parameter type hint from `Builder::geoWithin()`
   * Now accepts an array or `GeoJson\Geometry\Geometry` instance
 * [#215](https://github.com/doctrine/mongodb/pull/215): Add test environment preferring lowest package dependencies
 * [#222](https://github.com/doctrine/mongodb/pull/222): Add support for `MongoCollection::parallelCollectionScan()`
 * [#223](https://github.com/doctrine/mongodb/pull/223): Support `$useKeys` option when EagerCursor converts to an array
 * [#224](https://github.com/doctrine/mongodb/pull/224): Support client and socket timeout options in `Collection::count()`
   * `Collection::count()` now supports an options array as its second argument (`$limit` integer is still supported)
   * Socket timeout (i.e. `socketTimeoutMS`) may be specified alongside command options
 * [#225](https://github.com/doctrine/mongodb/pull/225): Include all cursor methods in cursor interface
   * Expands the common CursorInterface to support all methods in Cursor and EagerCursor
   * Allows ODM to be more agnostic when decorating a Cursor or EagerCursor
 * [#226](https://github.com/doctrine/mongodb/pull/226): Implement getter for cursor `useIdentifierKeys` option
