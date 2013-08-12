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

namespace Doctrine\MongoDB\Exception;

use RuntimeException;

/**
* ResultException is thrown when a database command fails.
*
* This is similar to the driver's MongoResultException class, which cannot be
* be used due to inaccessibility of its result document property.
*
* @see http://php.net/manual/en/class.mongoresultexception.php
*/
class ResultException extends RuntimeException
{
    /**
     * The command result document.
     *
     * @var array
     */
    private $document;

    /**
     * Constructor.
     *
     * @param array $document Command result document
     */
    public function __construct(array $document)
    {
        $message = isset($document['errmsg']) ? $document['errmsg'] : 'Unknown error executing command';
        $code = isset($document['code']) ? $document['code'] : 0;

        parent::__construct($message, $code);
    }

    /**
     * Get the command result document.
     *
     * @return array
     */
    public function getDocument()
    {
        return $this->document;
    }
}
