Doctrine MongoDB
----------------

The Doctrine MongoDB project is a simple layer that wraps the PECL Mongo extension and
adds new functionality like logging, events, a query builder and improves the API user 
friendliness.

It is very easy to use, you only need a new Connection instance:

    use Doctrine\MongoDB\Connection;

    $connection = new Connection();

If the default setup is not good for you then you can pass some optional arguments like
the following:

    use Doctrine\MongoDB\Connection;
    use Doctrine\MongoDB\Configuration;
    use Doctrine\Common\EventManager;

    $config = new Configuration();
    $evm = new EventManager();
    $conn = new Connection('mongodb://localhost', array('connect' => false), $config, $evm);

Now it is almost identical to the PECL extensions API:

    $coll = $conn->selectDatabase('dbname')->selectCollection('users');
    $user = array(
        'username' => 'jwage'
    );
    $coll->insert($user);

    echo $user['_id']; // new id assigned in _id key

It comes with some additional functionality like an object oriented query builder:

    $qb = $coll->createQueryBuilder()
        ->field('username')->equals('jwage');

    $query = $qb->getQuery();
    $user = $query->getSingleResult();

It also adds the ability to connect to events that get triggered internally. The list of
available events are:

* preBatchInsert
* postBatchInsert
* preSave
* postSave
* preInsert
* postInsert
* preUpdate
* postUpdate
* preRemove
* postRemove
* preFind
* postFind
* preFindOne
* postFindOne
* preFindAndRemove
* postFindAndRemove
* preFindAndUpdate
* postFindAndUpdate
* preGroup
* postGroup
* preGetDBRef
* postGetDBRef
* preCreateDBRef
* postCreateDBRef
* preDistinct
* postDistinct
* preNear
* postNear
* preCreateCollection
* postCreateCollection
* preSelectDatabase
* postSelectDatabase
* preDropDatabase
* postDropDatabase
* preSelectCollection
* postSelectCollection
* preDropCollection
* postDropCollection
* preGetGridFS
* postGetGridFS
* preConnect
* postConnect

You can connect to these events using the EventManager:

    use Doctrine\MongoDB\Events;
    use Doctrine\MongoDB\Events\EventArgs;

    $connectionEvents = new ConnectionEvents();
    $evm->addEventListener(Events::preConnect, $connectionEvents);

Now we just need to write the simple PHP class we initialized above:

    class ConnectionEvents
    {
        public function preConnect(EventArgs $eventArgs)
        {
            // do something before the connection to mongodb is initialized
        }
    }