CHANGELOG for 1.1.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 1.1.x patch versions.

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
