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
 * Event args for creating a collection.
 *
 * @since  1.0
 * @author Jonathan H. Wage <jonwage@gmail.com>
 */
class CreateCollectionEventArgs extends BaseEventArgs
{
    private $invoker;
    private $name;
    private $options;

    /**
     * Constructor.
     *
     * @todo Remove support for separate capped, size and max parameters in 2.0
     * @param object        $invoker
     * @param string        $name
     * @param boolean|array $cappedOrOptions
     * @param integer       $size
     * @param integer       $max
     */
    public function __construct($invoker, $name, $cappedOrOptions, $size = 0, $max = 0)
    {
        $this->invoker = $invoker;
        $this->name = $name;

        $options = is_array($cappedOrOptions)
            ? $cappedOrOptions
            : array('capped' => $cappedOrOptions, 'size' => $size, 'max' => $max);

        $this->options = $options;
    }

    public function getInvoker()
    {
        return $this->invoker;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getOptions()
    {
        return $this->options;
    }

    /**
     * @deprecated 1.1 Replaced by options; will be removed for 2.0
     */
    public function getCapped()
    {
        return $this->options['capped'];
    }

    /**
     * @deprecated 1.1 Replaced by options; will be removed for 2.0
     */
    public function getSize()
    {
        return $this->options['size'];
    }

    /**
     * @deprecated 1.1 Replaced by options; will be removed for 2.0
     */
    public function getMax()
    {
        return $this->options['max'];
    }
}
