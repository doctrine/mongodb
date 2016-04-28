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
 * Event args for update queries.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class UpdateEventArgs extends BaseEventArgs
{
    private $invoker;
    private $query;
    private $newObj;
    private $options;

    /**
     * Constructor.
     *
     * @param object $invoker
     * @param array  $query
     * @param array  $newObj
     * @param array  $options
     */
    public function __construct($invoker, array $query, array $newObj, array $options = [])
    {
        $this->invoker = $invoker;
        $this->query = $query;
        $this->newObj = $newObj;
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
     * @return array
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @return array
     */
    public function getNewObj()
    {
        return $this->newObj;
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
     * @param array $newObj
     * @since 1.3
     */
    public function setNewObj(array $newObj)
    {
        $this->newObj = $newObj;
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
