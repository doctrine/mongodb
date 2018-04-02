Introduction
============

The Doctrine MongoDB project is an abstraction layer on top of the legacy PHP driver that the Doctrine MongoDB ODM project is built on top of.

.. caution::

    This project has been deprecated and the MongoDB ODM project will soon no longer depend on it. The project is also in bug-fixes-only mode.

Connecting
----------

Creating new connections is easy using the ``Doctrine\MongoDB\Connection`` class:

.. code-block:: php
    use Doctrine\MongoDB\Connection;

    $connection = new Connection('mongodb://localhost');

Databases
---------

With the connection you can start selecting databases using the ``selectDatabase`` method:

.. code-block:: php
    $database = $connection->selectDatabase('my_project_database');

Collections
-----------

Now you are ready to select a collection and insert some data using the ``insert`` method:

.. code-block:: php
    $users = $database->selectCollection('users');

    $user = [
        'username' => 'jwage',
    ];

    $users->insert($user);

Reading
-------

Reading data is easy using the ``find`` and ``findOne`` methods:

.. code-block:: php
    $user = $users->findOne(['username' => 'jwage']);

Updating
--------

Updating a record is simple using the ``update`` method:

.. code-block:: php
    $users->update(['username' => 'jwage'], ['$set' => ['isActive' => true]]);

Deleting
-------

Delete data from the collection using the ``remove`` method:

.. code-block:: php
    $collection->remove(['username' => 'jwage']);

