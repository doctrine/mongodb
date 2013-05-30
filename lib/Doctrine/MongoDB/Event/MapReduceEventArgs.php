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
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class MapReduceEventArgs extends BaseEventArgs
{
    private $invoker;
    private $map;
    private $reduce;
    private $query;
    private $out;
    private $options;

    public function __construct($invoker, $map, $reduce, array $out, array $query, array $options = array())
    {
        $this->invoker = $invoker;
        $this->map = $map;
        $this->reduce = $reduce;
        $this->out = $out;
        $this->query = $query;
        $this->options = $options;
    }

    public function getInvoker()
    {
        return $this->invoker;
    }

    public function getMap()
    {
        return $this->map;
    }

    public function getReduce()
    {
        return $this->reduce;
    }

    public function getOut()
    {
        return $this->out;
    }

    public function getQuery()
    {
        return $this->query;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
