<?php
/*
 *  $Id$
 *
 * THIS SOFTWARE IS PROVIDED BY THE COPYRIGHT HOLDERS AND CONTRIBUTORS
 * "AS IS" AND ANY EXPRESS OR IMPLIED WARRANTIES, INCLUDING, BUT NOT
 * LIMITED TO, THE IMPLIED WARRANTIES OF MERCHANTABILITY AND FITNESS FOR
 * A PARTICULAR PURPOSE ARE DISCLAIMED. IN NO EVENT SHALL THE COPYRIGHT
 * OWNER OR CONTRIBUTORS BE LIABLE FOR ANY DIRECT, INDIRECT, INCIDENTAL,
 * SPECIAL, EXEMPLARY, OR CONSEQUENTIAL DAMAGES (INCLUDING, BUT NOT
 * LIMITED TO, PROCUREMENT OF SUBSTITUTE GOODS OR SERVICES; LOSS OF USE,
 * DATA, OR PROFITS; OR BUSINESS INTERRUPTION) HOWEVER CAUSED AND ON ANY
 * THEORY OF LIABILITY, WHETHER IN CONTRACT, STRICT LIABILITY, OR TORT
 * (INCLUDING NEGLIGENCE OR OTHERWISE) ARISING IN ANY WAY OUT OF THE USE
 * OF THIS SOFTWARE, EVEN IF ADVISED OF THE POSSIBILITY OF SUCH DAMAGE.
 *
 * This software consists of voluntary contributions made by many individuals
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\MongoDB;

/**
 * Container for all Doctrine\MongoDB events.
 *
 * This class cannot be instantiated.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
final class Events
{
    private function __construct() {}

    const preBatchInsert = 'collectionPreBatchInsert';
    const postBatchInsert = 'collectionPostBatchInsert';

    const preSave = 'collectionPreSave';
    const postSave = 'collectionPostSave';

    const preInsert = 'collectionPreInsert';
    const postInsert = 'collectionPostInsert';

    const preUpdate = 'collectionPreUpdate';
    const postUpdate = 'collectionPostUpdate';

    const preRemove = 'collectionPreRemove';
    const postRemove = 'collectionPostRemove';

    const preFind = 'collectionPreFind';
    const postFind = 'collectionPostFind';

    const preFindOne = 'collectionPreFindOne';
    const postFindOne = 'collectionPostFindOne';

    const preFindAndRemove = 'collectionPreFindAndRemove';
    const postFindAndRemove = 'collectionPostFindAndRemove';

    const preFindAndUpdate = 'collectionPreFindAndUpdate';
    const postFindAndUpdate = 'collectionPostFindAndUpdate';

    const preGroup = 'collectionPreGroup';
    const postGroup = 'collectionPostGroup';

    const preGetDBRef = 'collectionPreGetDBRef';
    const postGetDBRef = 'collectionPostGetDBRef';

    const preCreateDBRef = 'collectionPreCreateDBRef';
    const postCreateDBRef = 'collectionPostCreateDBRef';

    const preDistinct = 'collectionPreDistinct';
    const postDistinct = 'collectionPostDistinct';

    const preMapReduce = 'preMapReduce';
    const postMapReduce = 'postMapReduce';

    const preNear = 'collectionPreNear';
    const postNear = 'collectionPostNear';

    const preCreateCollection = 'preCreateCollection';
    const postCreateCollection = 'postCreateCollection';

    const preSelectDatabase = 'preSelectDatabase';
    const postSelectDatabase = 'postSelectDatabase';

    const preDropDatabase = 'preDropDatabase';
    const postDropDatabase = 'postDropDatabase';

    const preSelectCollection = 'preSelectCollection';
    const postSelectCollection = 'postSelectCollection';

    const preDropCollection = 'preDropCollection';
    const postDropCollection = 'postDropCollection';

    const preGetGridFS = 'preGetGridFS';
    const postGetGridFS = 'postGetGridFS';

    const preConnect = 'preConnect';
    const postConnect = 'postConnect';
}