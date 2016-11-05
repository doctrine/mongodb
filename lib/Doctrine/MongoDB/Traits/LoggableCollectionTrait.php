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

namespace Doctrine\MongoDB\Traits;

/**
 * {@internal It's used by loggable collection classes }}
 */
trait LoggableCollectionTrait
{
    /**
     * The logger callable.
     *
     * @var callable
     */
    protected $loggerCallable;

    /**
     * Log something using the configured logger callable.
     *
     * @see Loggable::log()
     * @param array $log
     */
    public function log(array $log)
    {
        $log['db'] = $this->database->getName();
        $log['collection'] = $this->getName();
        call_user_func($this->loggerCallable, $log);
    }

    /**
     * @see Collection::aggregate()
     */
    public function aggregate(array $pipeline, array $options = [] /* , array $op, ... */)
    {
        if ( ! array_key_exists(0, $pipeline)) {
            $pipeline = func_get_args();
            $options = [];
        }

        $this->log([
            'aggregate' => true,
            'pipeline' => $pipeline,
            'options' => $options,
        ]);

        return parent::aggregate($pipeline, $options);
    }

    /**
     * @see Collection::batchInsert()
     */
    public function batchInsert(array &$a, array $options = [])
    {
        $this->log([
            'batchInsert' => true,
            'num' => count($a),
            'data' => $a,
            'options' => $options,
        ]);

        return parent::batchInsert($a, $options);
    }

    /**
     * @see Collection::count()
     */
    public function count(array $query = [], $limitOrOptions = 0, $skip = 0)
    {
        $options = is_array($limitOrOptions)
            ? array_merge(['limit' => 0, 'skip' => 0], $limitOrOptions)
            : ['limit' => $limitOrOptions, 'skip' => $skip];

        $this->log([
            'count' => true,
            'query' => $query,
            'options' => $options,
            /* @deprecated 1.2 Replaced by options; will be removed for 2.0 */
            'limit' => $options['limit'],
            'skip' => $options['skip'],
        ]);

        return parent::count($query, $options);
    }

    /* Collection::createDBRef() is intentionally omitted because it does not
     * interact with the server.
     */

    /**
     * @see Collection::deleteIndex()
     */
    public function deleteIndex($keys)
    {
        $this->log([
            'deleteIndex' => true,
            'keys' => $keys,
        ]);

        return parent::deleteIndex($keys);
    }

    /**
     * @see Collection::deleteIndexes()
     */
    public function deleteIndexes()
    {
        $this->log(['deleteIndexes' => true]);

        return parent::deleteIndexes();
    }

    /**
     * @see Collection::distinct()
     */
    public function distinct($field, array $query = [], array $options = [])
    {
        $this->log([
            'distinct' => true,
            'field' => $field,
            'query' => $query,
            'options' => $options,
        ]);

        return parent::distinct($field, $query, $options);
    }

    /**
     * @see Collection::drop()
     */
    public function drop()
    {
        $this->log(['drop' => true]);

        return parent::drop();
    }

    /**
     * @see Collection::ensureIndex()
     */
    public function ensureIndex(array $keys, array $options = [])
    {
        $this->log([
            'ensureIndex' => true,
            'keys' => $keys,
            'options' => $options,
        ]);

        return parent::ensureIndex($keys, $options);
    }

    /**
     * @see Collection::find()
     */
    public function find(array $query = [], array $fields = [])
    {
        $this->log([
            'find' => true,
            'query' => $query,
            'fields' => $fields,
        ]);

        return parent::find($query, $fields);
    }

    /**
     * @see Collection::findAndRemove()
     */
    public function findAndRemove(array $query, array $options = [])
    {
        $this->log([
            'findAndRemove' => true,
            'query' => $query,
            'options' => $options,
        ]);

        return parent::findAndRemove($query, $options);
    }

    /**
     * @see Collection::findAndUpdate()
     */
    public function findAndUpdate(array $query, array $newObj, array $options = [])
    {
        $this->log([
            'findAndUpdate' => true,
            'query' => $query,
            'newObj' => $newObj,
            'options' => $options,
        ]);

        return parent::findAndUpdate($query, $newObj, $options);
    }

    /**
     * @see Collection::findOne()
     */
    public function findOne(array $query = [], array $fields = [])
    {
        $this->log([
            'findOne' => true,
            'query' => $query,
            'fields' => $fields,
        ]);

        return parent::findOne($query, $fields);
    }

    /**
     * @see Collection::getDBRef()
     */
    public function getDBRef(array $reference)
    {
        $this->log([
            'getDBRef' => true,
            'reference' => $reference,
        ]);

        return parent::getDBRef($reference);
    }

    /**
     * @see Collection::getIndexInfo()
     */
    public function getIndexInfo()
    {
        $this->log(['getIndexInfo' => true]);

        return parent::getIndexInfo();
    }

    /**
     * @see Collection::group()
     */
    public function group($keys, array $initial, $reduce, array $options = [])
    {
        $this->log([
            'group' => true,
            'keys' => $keys,
            'initial' => $initial,
            'reduce' => $reduce,
            'options' => $options,
        ]);

        return parent::group($keys, $initial, $reduce, $options);
    }

    /**
     * @see Collection::insert()
     */
    public function insert(array &$a, array $options = [])
    {
        $this->log([
            'insert' => true,
            'document' => $a,
            'options' => $options,
        ]);

        return parent::insert($a, $options);
    }

    /**
     * @see Collection::mapReduce()
     */
    public function mapReduce($map, $reduce, $out = ['inline' => true], array $query = [], array $options = [])
    {
        $this->log([
            'mapReduce' => true,
            'map' => $map,
            'reduce' => $reduce,
            'out' => $out,
            'query' => $query,
            'options' => $options,
        ]);

        return parent::mapReduce($map, $reduce, $out, $query, $options);
    }

    /**
     * @see Collection::near()
     */
    public function near($near, array $query = [], array $options = [])
    {
        $this->log([
            'geoNear' => true,
            'near' => $near,
            'query' => $query,
            'options' => $options,
        ]);

        return parent::near($near, $query, $options);
    }

    /**
     * @see Collection::remove()
     */
    public function remove(array $query, array $options = [])
    {
        $this->log([
            'remove' => true,
            'query' => $query,
            'options' => $options,
        ]);

        return parent::remove($query, $options);
    }

    /**
     * @see Collection::save()
     */
    public function save(array &$a, array $options = [])
    {
        $this->log([
            'save' => true,
            'document' => $a,
            'options' => $options,
        ]);

        return parent::save($a, $options);
    }

    /**
     * @see Collection::update()
     */
    public function update($query, array $newObj, array $options = [])
    {
        $this->log([
            'update' => true,
            'query' => $query,
            'newObj' => $newObj,
            'options' => $options,
        ]);

        return parent::update($query, $newObj, $options);
    }

    /**
     * @see Collection::validate()
     */
    public function validate($scanData = false)
    {
        $this->log([
            'validate' => true,
            'scanData' => $scanData,
        ]);

        return parent::validate($scanData);
    }
}
