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
 * Event args for the group command.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class GroupEventArgs extends BaseEventArgs
{
    private $invoker;
    private $keys;
    private $initial;
    private $reduce;
    private $options;

    /**
     * Constructor.
     *
     * @param object                  $invoker
     * @param array|string|\MongoCode $keys
     * @param array                   $initial
     * @param string|\MongoCode       $reduce
     * @param array                   $options
     */
    public function __construct($invoker, $keys, array $initial, $reduce, array $options = array())
    {
        $this->invoker = $invoker;
        $this->keys = $keys;
        $this->initial = $initial;
        $this->reduce = $reduce;
        $this->options = $options;
    }

    public function getInvoker()
    {
        return $this->invoker;
    }

    public function getKeys()
    {
        return $this->keys;
    }

    public function getInitial()
    {
        return $this->initial;
    }

    public function getReduce()
    {
        return $this->reduce;
    }

    public function getOptions()
    {
        return $this->options;
    }
}
