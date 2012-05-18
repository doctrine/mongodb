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

namespace Doctrine\MongoDB;

/**
 * EagerCursor class.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class EagerCursor implements Iterator
{
    /** @var object Doctrine\MongoDB\Cursor */
    protected $cursor;

    /** @var array Array of data from Cursor to iterate over */
    protected $data = array();

    /** @var bool Whether or not the EagerCursor has been initialized */
    protected $initialized = false;

    public function __construct(Cursor $cursor)
    {
        $this->cursor = $cursor;
    }

    public function getCursor()
    {
        return $this->cursor;
    }

    public function isInitialized()
    {
        return $this->initialized;
    }

    public function initialize()
    {
        if ($this->initialized === false) {
            $this->data = $this->cursor->toArray();
            unset($this->cursor);
        }
        $this->initialized = true;
    }

    public function rewind()
    {
        $this->initialize();
        reset($this->data);
    }
  
    public function current()
    {
        $this->initialize();
        return current($this->data);
    }
  
    public function key() 
    {
        $this->initialize();
        return key($this->data);
    }
  
    public function next() 
    {
        $this->initialize();
        return next($this->data);
    }
  
    public function valid()
    {
        $this->initialize();
        $key = key($this->data);
        return ($key !== NULL && $key !== FALSE);
    }

    public function count()
    {
        $this->initialize();
        return count($this->data);
    }

    public function toArray()
    {
        $this->initialize();
        return $this->data;
    }

    public function getSingleResult()
    {
        $this->initialize();
        return $this->current();
    }
}
