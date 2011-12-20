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

use Doctrine\MongoDB\Logging\MethodLogger;

/**
 * Wrapper for the PHP MongoCollection class.
 *
 * @license     http://www.opensource.org/licenses/lgpl-license.php LGPL
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jonathan H. Wage <jonwage@gmail.com>
 * @author      Bulat Shakirzyanov <mallluhuct@gmail.com>
 */
class LoggableCollection extends Collection
{
    /**
     * The logger.
     *
     * @var MethodLogger
     */
    protected $logger;

    /**
     * Sets the logger.
     *
     * @param MethodLogger $logger The logger
     */
    public function setLogger(MethodLogger $logger)
    {
        $this->logger = $logger;
    }

    /** @override */
    protected function callDelegate($method, array $arguments = array())
    {
        if (!$this->logger) {
            return parent::callDelegate($method, $arguments);
        }

        $this->logger->startMethod(MethodLogger::CONTEXT_COLLECTION, $method, $arguments, $this->database->getName(), $this->getName());
        $result = parent::callDelegate($method, $arguments);
        $this->logger->stopMethod();

        return $result;
    }

    /** @override */
    protected function wrapCursor(\MongoCursor $delegate, $query, $fields)
    {
        if (!$this->logger) {
            return parent::wrapCursor($delegate, $query, $fields);
        }

        $cursor = new LoggableCursor($delegate);
        $cursor->setLogger($this->logger);
        $cursor->setDatabaseName($this->database->getName());
        $cursor->setCollectionName($this->getName());
        $cursor->setQuery($query);
        $cursor->setFields($fields);

        return $cursor;
    }
}
