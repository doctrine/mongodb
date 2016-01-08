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
 * Event args for the aggregate command.
 *
 * @since  1.1
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class AggregateEventArgs extends BaseEventArgs
{
    private $invoker;
    private $pipeline;
    private $options;

    /**
     * Constructor.
     *
     * @param object $invoker
     * @param array  $pipeline
     */
    public function __construct($invoker, array $pipeline, array $options = [])
    {
        $this->invoker = $invoker;
        $this->pipeline = $pipeline;
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
    public function getPipeline()
    {
        return $this->pipeline;
    }

    /**
     * @since 1.2
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @param array $pipeline
     * @since 1.3
     */
    public function setPipeline(array $pipeline)
    {
        $this->pipeline = $pipeline;
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
