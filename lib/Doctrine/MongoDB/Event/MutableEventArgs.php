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

/**
 * Mutable event args for query and command results.
 *
 * @since  1.1
 * @author Jeremy Mikola <jmikola@gmail.com>
 */
class MutableEventArgs extends EventArgs
{
    private $changedData;
    private $changedOptions;
    private $isDataChanged = false;
    private $isOptionsChanged = false;

    /**
     * @return mixed|null
     */
    public function getData()
    {
        return $this->isDataChanged ? $this->changedData : parent::getData();
    }

    /**
     * @param mixed $data
     */
    public function setData($data)
    {
        $this->isDataChanged = parent::getData() !== $data;
        $this->changedData = $this->isDataChanged ? $data : null;
    }

    /**
     * @return bool
     */
    public function isDataChanged()
    {
        return $this->isDataChanged;
    }

    /**
     * @since 1.3
     * @return array
     */
    public function getOptions()
    {
        return $this->isOptionsChanged ? $this->changedOptions : parent::getOptions();
    }

    /**
     * @since 1.3
     * @param mixed $options
     */
    public function setOptions(array $options)
    {
        $this->isOptionsChanged = parent::getOptions() !== $options;
        $this->changedOptions = $this->isOptionsChanged ? $options : null;
    }

    /**
     * @since 1.3
     * @return bool
     */
    public function isOptionsChanged()
    {
        return $this->isOptionsChanged;
    }
}
