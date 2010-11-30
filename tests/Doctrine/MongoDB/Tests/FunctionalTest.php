<?php

namespace Doctrine\MongoDB\Tests;

use Doctrine\MongoDB\Configuration;
use Doctrine\MongoDB\Connection;
use Doctrine\MongoDB\GridFSFile;

class FunctionalTest extends BaseTest
{
    public function testFunctional()
    {
        $config = new Configuration();
        $config->setLoggerCallable(function($msg) {
            //print_r($msg);
        });
        $conn = new Connection(null, array(), $config);
        $db = $conn->selectDB('doctrine_mongodb');

        /*
        $coll = $db->selectCollection('users');

        $document = array('test' => 'jwage');
        $coll->insert($document);

        $coll->update(array('_id' => $document['_id']), array('$set' => array('test' => 'jon')));

        $cursor = $coll->find();
        print_r($cursor->getSingleResult());
        */

        $files = $db->getGridFS('files');
        $file = array(
            'title' => 'test file',
            'testing' => 'ok',
            'file' => new GridFSFile(__DIR__.'/FunctionalTest.php')
        );
        $files->insert($file, array('safe' => true));
print_r($file);
        $files->update(array('_id' => $file['_id']), array('$set' => array('title' => 'fuck', 'file' => new GridFSFile(__DIR__.'/BaseTest.php'))));
    }
}