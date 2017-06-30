UPGRADE from 1.0 to 1.1
=======================

Pull requests completed for the 1.1.0 release:

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
