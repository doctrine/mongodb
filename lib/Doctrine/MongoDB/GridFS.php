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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\MongoDB;

/**
 * GridFS
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class GridFS extends Collection
{
    /** @override */
    protected function doFindOne(array $query = array(), array $fields = array())
    {
        $file = $this->mongoCollection->findOne($query, $fields);
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

        // Has file to be persisted
        if (isset($file) && $file->isDirty()) {
            // It is impossible to update a file on the grid so we have to remove it and
            // persist a new file with the same data

            // First do a find and remove query to remove the file metadata and chunks so
            // we can restore the file below
            $document = $this->findAndRemove($query, $options);
            unset(
                $document['filename'],
                $document['length'],
                $document['chunkSize'],
                $document['uploadDate'],
                $document['md5'],
                $document['file']
            );

            // Store the file
            $this->storeFile($file, $document, $options);
        }

        // Now send the original update bringing the file up to date
        if ($newObj) {
            if ( ! isset($newObj[$this->cmd.'set'])) {
                unset($newObj['_id']);
                $newObj = array($this->cmd.'set' => $newObj);
            }
            $this->mongoCollection->update($query, $newObj, $options);
        }
        return $newObj;
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
        // If file exists and is dirty then lets persist the file and store the file path or the bytes
        if (isset($a['file'])) {
            $file = $a['file']; // instanceof GridFSFile
            unset($a['file']);
            if ($file->isDirty()) {
                $this->storeFile($file, $a, $options);
            } else {
                parent::doInsert($a, $options);
            }
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
        if (is_string($file)) {
            $file = new GridFSFile($file);
        }
        if ($file->hasUnpersistedFile()) {
            $id = $this->mongoCollection->storeFile($file->getFilename(), $document, $options);
        } else {
            $id = $this->mongoCollection->storeBytes($file->getBytes(), $document, $options);
        }
        $document = array_merge(array('_id' => $id), $document);
        $file->setMongoGridFSFile(new \MongoGridFSFile($this->mongoCollection, $document));
        return $file;
    }
}