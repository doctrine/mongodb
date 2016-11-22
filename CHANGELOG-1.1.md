CHANGELOG for 1.1.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 1.1.x patch versions.

1.1.8 (2015-02-24)
------------------

 * [#207](https://github.com/doctrine/mongodb/pull/207): Bump doctrine/common dependency

1.1.7 (2015-01-30)
------------------

 * [#188](https://github.com/doctrine/mongodb/pull/188): Added last stable mongo extension version to travis build matrix
 * [#193](https://github.com/doctrine/mongodb/pull/193): Do not allow PHP 5.6 test failures
 * [#195](https://github.com/doctrine/mongodb/pull/195): Fix `Connection::isConnected()` for driver versions 1.5.0+
 * [#196](https://github.com/doctrine/mongodb/pull/196): Fix handling of client options (e.g. socket timeout) in command helpers
 * [#198](https://github.com/doctrine/mongodb/pull/198): Convert deprecated "timeout" option for `Database::command()`
 * [#199](https://github.com/doctrine/mongodb/pull/199): Remove "timeout" conversion from Collection command wrappers

1.1.6 (2014-04-29)
------------------

 * [#172](https://github.com/doctrine/mongodb/pull/172): `Collection::ensureIndex()` should convert write options
 * [#174](https://github.com/doctrine/mongodb/pull/174): MongoDB 2.6: Ensure $in argument is a real BSON array
 * [#175](https://github.com/doctrine/mongodb/pull/175): Convert timeout options for driver >= 1.5.0
 * [#176](https://github.com/doctrine/mongodb/pull/176): Add new PHP and driver versions to Travis CI
 * [#177](https://github.com/doctrine/mongodb/pull/177): Respect `$options` when `$server` is null in Connection constructor
 * [#178](https://github.com/doctrine/mongodb/pull/178): Convert deprecated MongoClient constructor options for driver >= 1.4.0
 * [#179](https://github.com/doctrine/mongodb/pull/179): Cast `createCollection()` options for MongoDB 2.6 compatibility

1.1.5 (2014-03-28)
------------------

 * [#166](https://github.com/doctrine/mongodb/pull/166): Use `current()` in `EagerCursor::getSingleResult()`
 * [#167](https://github.com/doctrine/mongodb/pull/167): Revert "Fix Query construction in EagerCursor preparation test"

1.1.4 (2014-03-28)
------------------

 * [#163](https://github.com/doctrine/mongodb/pull/163): Revert "Allow string or array Cursor::hint() argument"
 * [#164](https://github.com/doctrine/mongodb/pull/164): Allow string or array `Cursor::hint()` argument

1.1.3 (2014-03-27)
------------------

 * [#157](https://github.com/doctrine/mongodb/pull/157): Fix createCollection event dispatching
 * [#159](https://github.com/doctrine/mongodb/pull/159): Allow string or array `Cursor::hint()` argument
 * [#161](https://github.com/doctrine/mongodb/pull/161): Fix `EagerCursor::getSingleResult()` behavior

1.1.2 (2014-01-09)
------------------

 * [#150](https://github.com/doctrine/mongodb/pull/150): `Cursor::getSingleResult()` should not use keys in `toArray()`

1.1.1 (2013-12-05)
------------------

 * [#148](https://github.com/doctrine/mongodb/pull/148): Reset cursor before and after `getSingleResult()`
 * [#149](https://github.com/doctrine/mongodb/pull/149): Use integer-casted argument in `Cursor::batchSize()`

1.1.0 (2013-12-02)
------------------

 * [#92](https://github.com/doctrine/mongodb/pull/92): Add get/setReadPreference() methods on core classes
 * [#97](https://github.com/doctrine/mongodb/pull/97): Implement `Collection::aggregate()` helper
 * [#103](https://github.com/doctrine/mongodb/pull/103): Support array argument in `Builder::select()` and `exclude()`
 * [#105](https://github.com/doctrine/mongodb/pull/105): Internal cursor improvements for hint, sort, read preferences, and recreation
   * `Cursor::hint()` and `sort()` should overwrite previous values
   * Integrate `setReadPreference()` with `recreate()` and make it chainable
 * [#106](https://github.com/doctrine/mongodb/pull/106): Events refactoring and support for modifying data in EventArgs
   * Support MongoDB::createCollection() options array (driver 1.4+)
   * Introduce MutableEventArgs object for post-event listeners
 * [#107](https://github.com/doctrine/mongodb/pull/107): Adding $nor query operator for Builder and Expr
 * [#118](https://github.com/doctrine/mongodb/pull/118): Documentation cleanup
 * [#109](https://github.com/doctrine/mongodb/pull/109): Support GeoJSON and 2dsphere queries
   * Rename `Query::TYPE_GEO_LOCATION` to `TYPE_GEO_NEAR`
   * Query builder and expression methods for 2dsphere geo-spatial operators
   * Integration with [GeoJSON](http://github.com/jmikola/geojson) library
 * [#121](https://github.com/doctrine/mongodb/pull/121): Reorder methods alphabetically
 * [#123](https://github.com/doctrine/mongodb/pull/123): ArrayIterator improvements
   * Allow `offsetSet()` to append values
   * Fix `valid()` return value if an array element is `false`
 * [#124](https://github.com/doctrine/mongodb/pull/124): Deprecated passing scalar $query argument to `Collection::update()`
 * [#122](https://github.com/doctrine/mongodb/pull/122): Command method refactoring
   * Command methods now throw ResultException on error
   * `group()` now returns an ArrayIterator instead of the raw command response
   * Ensure all `mapReduce()` options are prepared as MongoCode objects (where applicable)
   * Handle external database output strategy for `mapReduce()`
 * [#126](https://github.com/doctrine/mongodb/pull/126): DBRef database/collection method improvements
   * `createDBRef()` should not use array type hinting (arg can be a document or an ID)
   * `createDBRef()` should not dispatch events, nor should it be logged
   * Add event dispatching to `Database::getDBRef()` (Collection already had this)
 * [#127](https://github.com/doctrine/mongodb/pull/127): Improve events/logging for Database methods
   * Fixed post-event dispatching for `getGridFS()` and `createCollection()`
   * Added tests for Database event dispatching
   * Fixed logging for `createCollection()` and make LoggableDatabase's API consistent with the base class
 * [#128](https://github.com/doctrine/mongodb/pull/128): Query builder improvements
   * Do not filter out falsey values in Query/Builder `debug()` methods
   * Support values and expressions for $pull in query builder
   * Remove recursive merging in `Expr::addManyToSet()`
   * Implement `Expr::each()` and allow it to be used with `addToSet()`
   * Deprecate `Expr::addManyToSet()` in favor of `addToSet()` and `each()`
   * Support $each/$slice/$sort operators with `push()`
   * `Expr::push()` should ensure $each operator appears first
   * `Expr::where()` should not alter the current field in the builder
   * `Builder::mapReduceOptions()` and `out()` methods should require mapReduce command
   * Support GeoJSON in `Builder::geoNear()` and set spherical default
   * `Builder::map()` should init full query array, default to inline mapReduce output
   * Rename `Query::DISTINCT_FIELD` to `DISTINCT`
   * Throw exception for invalid query types in Query constructor
   * Query should allow a single cursor hint to be specified
   * Query should apply "limit" option for mapReduce commands
   * Add array type-hint to `Builder::all()` and `Expr::all()`
   * `Query::getIterator()` should not execute if an exception is guaranteed
   * Deprecate `Query::iterate()` alias in favor of `getIterator()`
 * [#130](https://github.com/doctrine/mongodb/pull/130): Add $rename update operator for Builder and Expr
 * [#131](https://github.com/doctrine/mongodb/pull/131): Remove $cmd args/properties and deprecate `mongoCmd` option
 * [#132](https://github.com/doctrine/mongodb/pull/132): Make full command result accessible in ArrayIterator
 * [#140](https://github.com/doctrine/mongodb/pull/140): Add `initialize()` calls in Connection methods to avoid use of null objects
 * [#139](https://github.com/doctrine/mongodb/pull/139): `Builder::sort()` should default to ascending order
 * [#143](https://github.com/doctrine/mongodb/pull/143): Query builder read prefs and wrap driver classes directly
   * Wrap driver classes directly and remove Connection reinit logic
   * Convert inconsistent return values for `getReadPreference()` from pre-1.3.3 drivers
   * Don't throw InvalidArgumentException in `Cursor::setReadPreference()`
   * Support read preferences in Query Builder
 * [#147](https://github.com/doctrine/mongodb/pull/147): `Expr::mod()` should take explicit divisor/remainder args

Additional commits included in 1.1.0:

 * [d51a44d](https://github.com/doctrine/mongodb/commit/d51a44d): Support $elemMatch in query projections (closes [#101](https://github.com/doctrine/mongodb/pull/101))
 * [e92f0f2](https://github.com/doctrine/mongodb/commit/e92f0f2): Remove unused $options argument in `Expr::equals()`
 * [773423a](https://github.com/doctrine/mongodb/commit/773423a): Use driver's return value in `Collection::batchInsert()` (closes [#93](https://github.com/doctrine/mongodb/pull/93))
 * [114a0ae](https://github.com/doctrine/mongodb/commit/114a0ae): Fix cursor creation when MapReduce's db output option is used
 * [8bc1466](https://github.com/doctrine/mongodb/commit/8bc1466): Ensure `Cursor::limit()` argument is casted to an integer
 * [6afee47](https://github.com/doctrine/mongodb/commit/6afee47): Deprecate `Connection::getStatus()` (to be removed in 1.2)
 * [05258d4](https://github.com/doctrine/mongodb/commit/05258d4): Deprecate Database force/prev/resetError() methods (to be removed in 1.2)
 * [25a8025](https://github.com/doctrine/mongodb/commit/25a8025): Restore `Query::TYPE_GEO_LOCATION` constant for BC, but deprecate it
