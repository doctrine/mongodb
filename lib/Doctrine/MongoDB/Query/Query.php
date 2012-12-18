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

namespace Doctrine\MongoDB\Query;

use Doctrine\MongoDB\Collection;
use Doctrine\MongoDB\Cursor;
use Doctrine\MongoDB\Database;
use Doctrine\MongoDB\EagerCursor;
use Doctrine\MongoDB\Iterator;
use Doctrine\MongoDB\IteratorAggregate;

/**
 * Query is responsible for executing and returning the results from queries built by the
 * query builder.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class Query implements IteratorAggregate
{
    const TYPE_FIND            = 1;
    const TYPE_FIND_AND_UPDATE = 2;
    const TYPE_FIND_AND_REMOVE = 3;
    const TYPE_INSERT          = 4;
    const TYPE_UPDATE          = 5;
    const TYPE_REMOVE          = 6;
    const TYPE_GROUP           = 7;
    const TYPE_MAP_REDUCE      = 8;
    const TYPE_DISTINCT_FIELD  = 9;
    const TYPE_GEO_LOCATION    = 10;
    const TYPE_COUNT           = 11;

    /**
     * The Database instance.
     *
     * @var Database
     */
    protected $database;

    /**
     * The Collection instance.
     *
     * @var Collection
     */
    protected $collection;

    /**
     * Array containing the query data.
     *
     * @var array
     */
    protected $query = array();

    /**
     * Mongo command prefix
     *
     * @var string
     */
    protected $cmd;

    /**
     * @var Iterator
     */
    protected $iterator;

    /**
     * Query options
     *
     * @var array
     */
    protected $options;

    public function __construct(Database $database, Collection $collection, array $query, array $options, $cmd)
    {
        $this->database   = $database;
        $this->collection = $collection;
        $this->query      = $query;
        $this->cmd        = $cmd;
        $this->options    = $options;
    }

    /**
     * Gets an array of information about this query for debugging.
     *
     * @param string $name
     * @return array $debug
     */
    public function debug($name = null)
    {
        $debug = $this->query['query'];
        if ($name !== null) {
            return $debug[$name];
        }
        foreach ($debug as $key => $value) {
            if ( ! $value) {
                unset($debug[$key]);
            }
        }
        return $debug;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getType()
    {
        return $this->query['type'];
    }

    public function getIterator()
    {
        if ($this->iterator === null) {
            $iterator = $this->execute();
            if ($iterator !== null && !$iterator instanceof Iterator) {
                throw new \BadMethodCallException('Query execution did not return an iterator. This query may not support returning iterators. ');
            }
            $this->iterator = $iterator;
        }
        return $this->iterator;
    }

    public function execute()
    {
        switch ($this->query['type']) {
            case self::TYPE_FIND:
                $cursor = $this->collection->find($this->query['query'], $this->query['select']);
                return $this->prepareCursor($cursor);

            case self::TYPE_FIND_AND_UPDATE:
                if ($this->query['sort']) {
                    $this->options['sort'] = $this->query['sort'];
                }
                if ($this->query['select']) {
                    $this->options['fields'] = $this->query['select'];
                }
                if ($this->query['upsert']) {
                    $this->options['upsert'] = true;
                }
                if ($this->query['new']) {
                    $this->options['new'] = true;
                }
                return $this->collection->findAndUpdate($this->query['query'], $this->query['newObj'], $this->options);

            case self::TYPE_FIND_AND_REMOVE:
                if ($this->query['sort']) {
                    $this->options['sort'] = $this->query['sort'];
                }
                if ($this->query['select']) {
                    $this->options['fields'] = $this->query['select'];
                }
                return $this->collection->findAndRemove($this->query['query'], $this->options);

            case self::TYPE_INSERT:
                return $this->collection->insert($this->query['newObj']);

            case self::TYPE_UPDATE:
                if ($this->query['upsert']) {
                    $this->options['upsert'] = $this->query['upsert'];
                }
                if ($this->query['multiple']) {
                    $this->options['multiple'] = $this->query['multiple'];
                }
                return $this->collection->update($this->query['query'], $this->query['newObj'], $this->options);

            case self::TYPE_REMOVE:
                return $this->collection->remove($this->query['query'], $this->options);

            case self::TYPE_GROUP:
                if (!empty($this->query['query'])) {
                    $this->query['group']['options']['condition'] = $this->query['query'];
                }

                $options = array_merge($this->options, $this->query['group']['options']);

                return $this->collection->group($this->query['group']['keys'], $this->query['group']['initial'], $this->query['group']['reduce'], $options);

            case self::TYPE_MAP_REDUCE:
                if (!isset($this->query['mapReduce']['out'])) {
                    $this->query['mapReduce']['out'] = array('inline' => true);
                }

                $options = array_merge($this->options, $this->query['mapReduce']['options']);
                $cursor = $this->collection->mapReduce($this->query['mapReduce']['map'], $this->query['mapReduce']['reduce'], $this->query['mapReduce']['out'], $this->query['query'], $options);

                if ($cursor instanceof Cursor) {
                    $cursor = $this->prepareCursor($cursor);
                }

                return $cursor;

            case self::TYPE_DISTINCT_FIELD:
                return $this->collection->distinct($this->query['distinctField'], $this->query['query'], $this->options);

            case self::TYPE_GEO_LOCATION:
                if (isset($this->query['limit']) && $this->query['limit']) {
                    $this->options['num'] = $this->query['limit'];
                }

                foreach (array('distanceMultiplier', 'maxDistance', 'spherical') as $key) {
                    if (isset($this->query['geoNear'][$key]) && $this->query['geoNear'][$key]) {
                        $this->options[$key] = $this->query['geoNear'][$key];
                    }
                }

                return $this->collection->near($this->query['geoNear']['near'], $this->query['query'], $this->options);

            case self::TYPE_COUNT:
                return $this->collection->count($this->query['query']);
        }
    }

    protected function prepareCursor(Cursor $cursor)
    {
        $cursor->limit($this->query['limit']);
        $cursor->skip($this->query['skip']);
        $cursor->sort($this->query['sort']);
        $cursor->immortal($this->query['immortal']);

        if (null !== $this->query['slaveOkay']) {
            $cursor->slaveOkay($this->query['slaveOkay']);
        }

        if ($this->query['snapshot']) {
            $cursor->snapshot();
        }

        foreach ($this->query['hints'] as $keyPattern) {
            $cursor->hint($keyPattern);
        }

        if ($this->query['eagerCursor'] === true) {
            $cursor = new EagerCursor($cursor);
        }

        return $cursor;
    }

    /**
     * Count the number of results for this query.
     *
     * @param bool $all
     * @return integer $count
     */
    public function count($all = false)
    {
        return $this->getIterator()->count($all);
    }

    /**
     * Execute the query and get a single result
     *
     * @return object $document  The single document.
     */
    public function getSingleResult()
    {
        return $this->getIterator()->getSingleResult();
    }

    /**
     * Iterator over the query using the Cursor.
     *
     * @return Cursor $cursor
     */
    public function iterate()
    {
        return $this->getIterator();
    }

    /** @inheritDoc */
    public function toArray()
    {
        return $this->getIterator()->toArray();
    }
}
