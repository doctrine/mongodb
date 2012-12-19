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

/**
 * Wrapper for the PHP MongoGridFS class
 *
 * This class does not proxy all of the MongoGridFS methods; however, the
 * MongoGridFS object is accessible if those methods are required.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class GridFS extends Collection
{
    /** @override */
    public function getMongoCollection()
    {
        return $this->database->getMongoDB()->getGridFS($this->name);
    }

    /** @override */
    protected function doFindOne(array $query = array(), array $fields = array())
    {
        $collection = $this;
        $file = $this->retry(function() use ($collection, $query, $fields) {
            return $collection->getMongoCollection()->findOne($query, $fields);
        });
        if ($file) {
            $document = $file->file;
            $document['file'] = new GridFSFile($file);
            $file = $document;
        }
        return $file;
    }

    /** @override */
    protected function doUpdate($query, array $newObj, array $options = array())
    {
        if (is_scalar($query)) {
            $query = array('_id' => $query);
        }

        $file = isset($newObj[$this->cmd.'set']['file']) ? $newObj[$this->cmd.'set']['file'] : null;
        unset($newObj[$this->cmd.'set']['file']);

        if ($file === null) {
            $file = isset($newObj['file']) ? $newObj['file'] : null;
            unset($newObj['file']);
        }

        /* Before we inspect $newObj, remove an empty $set operator we may have
         * left behind due to extracting the file field above.
         */
        if (empty($newObj[$this->cmd.'set'])) {
            unset($newObj[$this->cmd.'set']);
        }

        /* Determine if $newObj includes atomic modifiers, which will tell us if
         * we can get by with a storeFile() in some situations or if a two-step
         * storeFile() and update() is necessary.
         */
        $newObjHasModifiers = false;

        foreach (array_keys($newObj) as $key) {
            if ($this->cmd === $key[0]) {
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
        return $this->getMongoCollection()->update($query, $newObj, $options);
    }

    /** @override */
    protected function doBatchInsert(array &$a, array $options = array())
    {
        foreach ($a as $key => &$array) {
            $this->doInsert($array, $options);
        }
    }

    /** @override */
    protected function doInsert(array &$a, array $options = array())
    {
        // If there is no file, perform a basic insertion
        if (!isset($a['file'])) {
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

    /** @override */
    protected function doSave(array &$a, array $options = array())
    {
        if (isset($a['_id'])) {
            return $this->doUpdate(array('_id' => $a['_id']), $a, $options);
        } else {
            return $this->doInsert($a, $options);
        }
    }

    /**
     * Store a file on the mongodb grid file system.
     *
     * @param string|GridFSFile $file String path to a file or a GridFSFile object.
     * @param object $document
     * @param array $options
     * @return GridFSFile $file
     */
    public function storeFile($file, array &$document, array $options = array())
    {
        if (!$file instanceof GridFSFile) {
            $file = new GridFSFile($file);
        }

        if ($file->hasUnpersistedFile()) {
            $id = $this->getMongoCollection()->storeFile($file->getFilename(), $document, $options);
        } else {
            $id = $this->getMongoCollection()->storeBytes($file->getBytes(), $document, $options);
        }

        $document = array_merge(array('_id' => $id), $document);
        $gridFsFile = $this->getMongoCollection()->get($id);

        // TODO: Consider throwing exception if file cannot be fetched
        $file->setMongoGridFSFile($this->getMongoCollection()->get($id));

        return $file;
    }

    protected function doFindAndRemove(array $query, array $options = array())
    {
        $document = parent::doFindAndRemove($query, $options);

        if (isset($document)) {
            // Remove the file data from the chunks collection
            $this->getMongoCollection()->chunks->remove(array('files_id' => $document['_id']), $options);
        }

        return $document;
    }
}