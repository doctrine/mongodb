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
