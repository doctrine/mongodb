<?php
/*
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
 * and is licensed under the MIT license. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\MongoDB;

use Doctrine\Common\EventManager;

/**
 * Wrapper for the MongoGridFS class.
 *
 * This class does not proxy all of the MongoGridFS methods; however, the
 * MongoGridFS object is accessible if those methods are required.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class GridFS extends Collection
{
    /**
     * The MongoGridFS instance being wrapped.
     *
     * @var \MongoGridFS
     */
    protected $mongoCollection;

    /**
     * Constructor.
     *
     * @param Database     $database    Database to which this collection belongs
     * @param \MongoGridFS $mongoGridFS MongoGridFS instance being wrapped
     * @param EventManager $evm         EventManager instance
     * @param integer      $numRetries  Number of times to retry queries
     */
    public function __construct(Database $database, \MongoGridFS $mongoGridFS, EventManager $evm, $numRetries = 0)
    {
        parent::__construct($database, $mongoGridFS, $evm, $numRetries);
    }

    /**
     * Return the MongoGridFS instance being wrapped.
     *
     * @see Collection::getMongoCollection()
     * @return \MongoGridFS
     */
    public function getMongoCollection()
    {
        return $this->mongoCollection;
    }

    /**
     * Wrapper method for MongoGridFS::storeFile().
     *
     * This method returns the GridFSFile object, unlike the base MongoGridFS
     * method, which returns the "_id" field of the saved document. The "_id"
     * will be set on the $document parameter, which is passed by reference.
     *
     * @see http://php.net/manual/en/mongogridfs.storefile.php
     * @param string|GridFSFile $file     String filename or a GridFSFile object
     * @param array             $document
     * @param array             $options
     * @return GridFSFile
     */
    public function storeFile($file, array &$document, array $options = array())
    {
        if ( ! $file instanceof GridFSFile) {
            $file = new GridFSFile($file);
        }

        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;

        if ($file->hasUnpersistedFile()) {
            $id = $this->mongoCollection->storeFile($file->getFilename(), $document, $options);
        } else {
            $id = $this->mongoCollection->storeBytes($file->getBytes(), $document, $options);
        }

        $document = array_merge(array('_id' => $id), $document);
        $gridFsFile = $this->mongoCollection->get($id);

        // TODO: Consider throwing exception if file cannot be fetched
        $file->setMongoGridFSFile($this->mongoCollection->get($id));

        return $file;
    }

    /**
     * Execute the batchInsert query.
     *
     * @see Collection::doBatchInsert()
     * @param array $a
     * @param array $options
     */
    protected function doBatchInsert(array &$a, array $options = array())
    {
        foreach ($a as $key => &$array) {
            $this->doInsert($array, $options);
        }
    }

    /**
     * Execute the findAndModify command with the remove option and delete any
     * chunks for the document.
     *
     * @see Collection::doFindAndRemove()
     * @param array $query
     * @param array $options
     * @return array|null
     */
    protected function doFindAndRemove(array $query, array $options = array())
    {
        $document = parent::doFindAndRemove($query, $options);

        if (isset($document)) {
            // Remove the file data from the chunks collection
            $this->mongoCollection->chunks->remove(array('files_id' => $document['_id']), $options);
        }

        return $document;
    }

    /**
     * Execute the findOne query.
     *
     * This method returns the file document, unlike the base MongoGridFS
     * method, which returns a MongoGridFSFile instance. Instead, the document's
     * "file" field will contain an equivalent GridFSFile instance.
     *
     * @see Collection::doFindOne()
     * @param array $query
     * @param array $fields
     * @return array|null
     */
    protected function doFindOne(array $query = array(), array $fields = array())
    {
        $mongoCollection = $this->mongoCollection;
        $file = $this->retry(function() use ($mongoCollection, $query, $fields) {
            return $mongoCollection->findOne($query, $fields);
        });
        if ($file) {
            $document = $file->file;
            $document['file'] = new GridFSFile($file);
            $file = $document;
        }
        return $file;
    }

    /**
     * Execute the insert query and persist the GridFSFile if necessary.
     *
     * @see Collection::doInsert()
     * @param array $a
     * @param array $options
     * @return mixed
     */
    protected function doInsert(array &$a, array $options = array())
    {
        // If there is no file, perform a basic insertion
        if ( ! isset($a['file'])) {
            parent::doInsert($a, $options);
            return;
        }

        /* If the file is dirty (i.e. it must be persisted), delegate to the
         * storeFile() method. Otherwise, perform a basic insertion.
         */
        $file = $a['file']; // instanceof GridFSFile
        unset($a['file']);

        if ($file->isDirty()) {
            $this->storeFile($file, $a, $options);
        } else {
            parent::doInsert($a, $options);
        }

        $a['file'] = $file;
        return $a;
    }

    /**
     * Execute the save query and persist the GridFSFile if necessary.
     *
     * @see Collection::doSave()
     * @param array $a
     * @param array $options
     * @return mixed
     */
    protected function doSave(array &$a, array $options = array())
    {
        if (isset($a['_id'])) {
            return $this->doUpdate(array('_id' => $a['_id']), $a, $options);
        } else {
            return $this->doInsert($a, $options);
        }
    }

    /**
     * Execute the update query and persist its GridFSFile if necessary.
     *
     * @see Collection::doFindOne()
     * @param array $query
     * @param array $newObj
     * @param array $options
     * @return array|null
     */
    protected function doUpdate(array $query, array $newObj, array $options = array())
    {
        $file = isset($newObj['$set']['file']) ? $newObj['$set']['file'] : null;
        unset($newObj['$set']['file']);

        if ($file === null) {
            $file = isset($newObj['file']) ? $newObj['file'] : null;
            unset($newObj['file']);
        }

        /* Before we inspect $newObj, remove an empty $set operator we may have
         * left behind due to extracting the file field above.
         */
        if (empty($newObj['$set'])) {
            unset($newObj['$set']);
        }

        /* Determine if $newObj includes atomic modifiers, which will tell us if
         * we can get by with a storeFile() in some situations or if a two-step
         * storeFile() and update() is necessary.
         */
        $newObjHasModifiers = false;

        foreach (array_keys($newObj) as $key) {
            if ('$' === $key[0]) {
                $newObjHasModifiers = true;
            }
        }

        // Is there a file in need of persisting?
        if (isset($file) && $file->isDirty()) {
            /* It is impossible to overwrite a file's chunks in GridFS so we
             * must remove it and re-persist a new file with the same data.
             *
             * First, use findAndRemove() to remove the file metadata and chunks
             * prior to storing the file again below. Exclude metadata fields
             * from the result, since those will be reset later.
             */
            $document = $this->findAndRemove($query, array('fields' => array(
                'filename' => 0,
                'length' => 0,
                'chunkSize' => 0,
                'uploadDate' => 0,
                'md5' => 0,
                'file' => 0,
            )));

            /* If findAndRemove() returned nothing (no match or removal), create
             * a new document with the query's "_id" if available.
             */
            if (!isset($document)) {
                /* If $newObj had modifiers, we'll need to do an update later,
                 * so default to an empty array for now. Otherwise, we can do
                 * without that update and store $newObj now.
                 */
                $document = $newObjHasModifiers ? array() : $newObj;

                /* If the document has no "_id" but there was one in the query
                 * or $newObj, we can use that instead of having storeFile()
                 * generate one.
                 */
                if (!isset($document['_id']) && isset($query['_id'])) {
                    $document['_id'] = $query['_id'];
                }

                if (!isset($document['_id']) && isset($newObj['_id'])) {
                    $document['_id'] = $newObj['_id'];
                }
            }

            // Document will definitely have an "_id" after storing the file.
            $this->storeFile($file, $document);

            if (!$newObjHasModifiers) {
                /* TODO: MongoCollection::update() would return a boolean if
                 * $newObj was not empty, or an array describing the update
                 * operation. Improvise, since we only stored the file and that
                 * returns the "_id" field.
                 */
                return true;
            }
        }

        // Now send the original update bringing the file up to date
        $options = isset($options['safe']) ? $this->convertWriteConcern($options) : $options;
        return $this->mongoCollection->update($query, $newObj, $options);
    }
}
