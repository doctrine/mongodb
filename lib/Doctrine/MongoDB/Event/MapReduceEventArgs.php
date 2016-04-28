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

namespace Doctrine\MongoDB\Event;

use Doctrine\Common\EventArgs as BaseEventArgs;

/**
 * Event args for the mapReduce command.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class MapReduceEventArgs extends BaseEventArgs
{
    private $invoker;
    private $map;
    private $reduce;
    private $query;
    private $out;
    private $options;

    /**
     * Constructor.
     *
     * @param object            $invoker
     * @param string|\MongoCode $map
     * @param string|\MongoCode $reduce
     * @param array             $out
     * @param array             $query
     * @param array             $options
     */
    public function __construct($invoker, $map, $reduce, array $out, array $query, array $options = [])
    {
        $this->invoker = $invoker;
        $this->map = $map;
        $this->reduce = $reduce;
        $this->out = $out;
        $this->query = $query;
        $this->options = $options;
    }

    /**
     * @return object
     */
    public function getInvoker()
    {
        return $this->invoker;
    }

    /**
     * @return \MongoCode|string
     */
    public function getMap()
    {
        return $this->map;
    }

    /**
     * @return \MongoCode|string
     */
    public function getReduce()
    {
        return $this->reduce;
    }

    /**
     * @return array
     */
    public function getOut()
    {
        return $this->out;
    }

    /**
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $query
     * @since 1.3
     */
    public function setQuery(array $query)
    {
        $this->query = $query;
    }

    /**
     * @param $map
     * @since 1.3
     */
    public function setMap($map)
    {
        $this->map = $map;
    }

    /**
     * @param $reduce
     * @since 1.3
     */
    public function setReduce($reduce)
    {
        $this->reduce = $reduce;
    }

    /**
     * @param array $out
     * @since 1.3
     */
    public function setOut(array $out)
    {
        $this->out = $out;
    }

    /**
     * @param array $options
     * @since 1.3
     */
    public function setOptions(array $options)
    {
        $this->options = $options;
    }
}
