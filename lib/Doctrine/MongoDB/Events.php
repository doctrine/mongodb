<?php

namespace Doctrine\MongoDB;

/**
 * Container class for all Doctrine MongoDB events.
 *
 * This class cannot be instantiated.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
final class Events
{
    private function __construct() {}

    const preAggregate = 'collectionPreAggregate';
    const postAggregate = 'collectionPostAggregate';

    const preBatchInsert = 'collectionPreBatchInsert';
    const postBatchInsert = 'collectionPostBatchInsert';

    const preCreateCollection = 'preCreateCollection';
    const postCreateCollection = 'postCreateCollection';

    const preConnect = 'preConnect';
    const postConnect = 'postConnect';

    const preDistinct = 'collectionPreDistinct';
    const postDistinct = 'collectionPostDistinct';

    const preDropCollection = 'preDropCollection';
    const postDropCollection = 'postDropCollection';

    const preDropDatabase = 'preDropDatabase';
    const postDropDatabase = 'postDropDatabase';

    const preFind = 'collectionPreFind';
    const postFind = 'collectionPostFind';

    const preFindAndRemove = 'collectionPreFindAndRemove';
    const postFindAndRemove = 'collectionPostFindAndRemove';

    const preFindAndUpdate = 'collectionPreFindAndUpdate';
    const postFindAndUpdate = 'collectionPostFindAndUpdate';

    const preFindOne = 'collectionPreFindOne';
    const postFindOne = 'collectionPostFindOne';

    const preGetDBRef = 'collectionPreGetDBRef';
    const postGetDBRef = 'collectionPostGetDBRef';

    const preGetGridFS = 'preGetGridFS';
    const postGetGridFS = 'postGetGridFS';

    const preGroup = 'collectionPreGroup';
    const postGroup = 'collectionPostGroup';

    const preInsert = 'collectionPreInsert';
    const postInsert = 'collectionPostInsert';

    const preMapReduce = 'preMapReduce';
    const postMapReduce = 'postMapReduce';

    const preNear = 'collectionPreNear';
    const postNear = 'collectionPostNear';

    const preRemove = 'collectionPreRemove';
    const postRemove = 'collectionPostRemove';

    const preSave = 'collectionPreSave';
    const postSave = 'collectionPostSave';

    const preSelectCollection = 'preSelectCollection';
    const postSelectCollection = 'postSelectCollection';

    const preSelectDatabase = 'preSelectDatabase';
    const postSelectDatabase = 'postSelectDatabase';

    const preUpdate = 'collectionPreUpdate';
    const postUpdate = 'collectionPostUpdate';
}
