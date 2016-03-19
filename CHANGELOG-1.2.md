CHANGELOG for 1.2.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 1.2.x patch versions.

1.2.2 (2016-03-19)
------------------

 * [#250](https://github.com/doctrine/mongodb/pull/250): Fix wrong syntax for dateToString operator

1.2.1 (2015-11-24)
------------------

 * [#229](https://github.com/doctrine/mongodb/pull/229): Fix EagerCursor::skip() method calling limit method of base cursor instead of skip method
 * [#231](https://github.com/doctrine/mongodb/pull/231): Remove count method declaration in CursorInterface to fix fatal error for PHP 5.3
 * [#237](https://github.com/doctrine/mongodb/pull/237): Fix bug where timeout is set to 1 ms
