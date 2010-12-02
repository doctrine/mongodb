Doctrine MongoDB
----------------

The Doctrine MongoDB project is a simple layer that wraps the PECL Mongo extension and
adds new functionality like logging and improves the API user friendliness.

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