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
 * and is licensed under the LGPL. For more information, see
 * <http://www.doctrine-project.org>.
 */

namespace Doctrine\MongoDB;

/**
 * Configuration
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.com
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 */
class Configuration
{
    /**
     * Array of attributes for this configuration instance.
     *
     * @var array $attributes
     */
    protected $attributes = array('mongoCmd' => '$');

    /**
     * Set the logger callable.
     *
     * @param mixed $loggerCallable The logger callable.
     */
    public function setLoggerCallable($loggerCallable)
    {
        $this->attributes['loggerCallable'] = $loggerCallable;
    }

    /**
     * Gets the logger callable.
     *
     * @return mixed $loggerCallable The logger callable.
     */
    public function getLoggerCallable()
    {
        return isset($this->attributes['loggerCallable']) ?
                $this->attributes['loggerCallable'] : null;
    }

    /**
     * Get mongodb command prefix - '$' by default
     * @return string
     */
    public function getMongoCmd()
    {
        return $this->attributes['mongoCmd'];
    }

    /**
     * Set mongodb command prefix
     * @param string $cmd
     */
    public function setMongoCmd($cmd)
    {
        $this->attributes['mongoCmd'] = $cmd;
    }
}