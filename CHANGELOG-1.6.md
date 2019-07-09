CHANGELOG for 1.6.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 1.6.x patch versions.

1.6.4 (2019-07-10)
------------------

 * [#333](https://github.com/doctrine/mongodb/pull/333): Fix wrong syntax for replaceRoot stage

1.6.3 (2018-07-20)
------------------

 * [#326](https://github.com/doctrine/mongodb/pull/326): Fix wrong element deletion in popFirst and popLast

1.6.2 (2018-04-30)
------------------

 * [#324](https://github.com/doctrine/mongodb/pull/324): Use primary read preference when reading newly peristed GridFS files

1.6.1 (2017-10-09)
------------------

 * [#306](https://github.com/doctrine/mongodb/pull/306): Fix wrong stage name for `$bucketAuto` pipeline stage
 * [#307](https://github.com/doctrine/mongodb/pull/307): Convert empty match stages to object

1.6.0 (2017-08-09)
------------------

 * [#298](https://github.com/doctrine/mongodb/pull/298): Fix BC break when converting expression objects
 * [#301](https://github.com/doctrine/mongodb/pull/301): Test against PHP 7.2
