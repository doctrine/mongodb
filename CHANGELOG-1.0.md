CHANGELOG for 1.0.x
===================

This changelog references the relevant changes (bug and security fixes) done
in 1.0.x patch versions.

To get the diff for a specific change, go to
https://github.com/doctrine/mongodb/commit/XXX where XXX is the commit hash.
To get the diff between two versions, go to
https://github.com/doctrine/mongodb/compare/XXX...YYY where XXX and YYY are
the older and newer versions, respectively.

To generate a changelog summary since the last version, run
`git log --no-merges --oneline XXX...1.0.x`

1.0.5 (2014-01-09)

 * 4a8822b: Cursor::getSingleResult() should not use keys in toArray() 

1.0.4 (2013-11-26)
------------------

 * fabcf49: Allow 1.5.x driver versions in composer.json
 * c7b6ef9: Convert "safe" write option to "w" for drivers 1.3.0+
 * 89c7f44: Check for null $mongo property in Connected::getStatus()
 * 9b71337: Support MongoClient in Connection::isConnected()
 * 009ea85: Remove executable bit from class files
 * f70e2a7: Remove code duplication in LoggableCursor and make docs consistent
 * d68f7bd: Make Cursor return values consistent with MongoCursor
 * 8bf686d: Clean up Cursor and EagerCursor docs and tests

1.0.3 (2013-05-23)
------------------

 * 531dc00: Allow 1.4.x driver versions in composer.json
 * bcdf464: Test driver 1.3.7 and PHP 5.5 in Travis
 * cbd7ad9: Force driver install for Travis, and test against 1.3.4

1.0.2 (2013-03-04)
------------------

 * ec10f4d: Test MongoCursor read preferences via slaveOkay()
 * 1592926: Add missing use statement in Cursor class
 * 1432c87: Test latest Mongo driver in Travis CI builds
 * 24a1e89: Make ReadPreferenceTest skip message consistent </ocd>
 * 51805f8: Skip test if mongo pecl extension is < 1.3.0

1.0.1 (2013-01-10)
------------------

 * adf94c7: Only convert tag sets if necessary for setReadPreference()
 * a0534a0: Only convert numeric read preference types (driver <=1.3.2)
 * b317c8e: Fix bad reference to exception class in ReadPreference
