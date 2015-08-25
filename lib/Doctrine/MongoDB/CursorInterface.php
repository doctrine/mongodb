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
 * Wrapper for the PHP MongoCursor class.
 *
 * @since  1.2
 * @author alcaeus <alcaeus@alcaeus.org>
 */
interface CursorInterface extends Iterator
{
    /**
     * Wrapper method for MongoCursor::addOption().
     *
     * @see http://php.net/manual/en/mongocursor.addoption.php
     * @param string $key
     * @param mixed $value
     * @return self
     */
    public function addOption($key, $value);

    /**
     * Wrapper method for MongoCursor::batchSize().
     *
     * @see http://php.net/manual/en/mongocursor.batchsize.php
     * @param integer $num
     * @return self
     */
    public function batchSize($num);

    /**
     * Wrapper method for MongoCursor::count().
     *
     * @see http://php.net/manual/en/countable.count.php
     * @see http://php.net/manual/en/mongocursor.count.php
     * @return integer
     */
    public function count();

    /**
     * Wrapper method for MongoCursor::dead().
     *
     * @see http://php.net/manual/en/mongocursor.dead.php
     * @return boolean
     */
    public function dead();

    /**
     * Wrapper method for MongoCursor::explain().
     *
     * @see http://php.net/manual/en/mongocursor.explain.php
     * @return array
     */
    public function explain();

    /**
     * Wrapper method for MongoCursor::fields().
     *
     * @param array $f Fields to return (or not return).
     *
     * @see http://php.net/manual/en/mongocursor.fields.php
     * @return self
     */
    public function fields(array $f);

    /**
     * Return the collection for this cursor.
     *
     * @return Collection
     */
    public function getCollection();

    /**
     * Return the selected fields (projection).
     *
     * @return array
     */
    public function getFields();

    /**
     * Wrapper method for MongoCursor::getNext().
     *
     * @see http://php.net/manual/en/mongocursor.getnext.php
     * @return array|null
     */
    public function getNext();

    /**
     * Return the query criteria.
     *
     * @return array
     */
    public function getQuery();

    /**
     * Wrapper method for MongoCursor::getReadPreference().
     *
     * @see http://php.net/manual/en/mongocursor.getreadpreference.php
     * @return array
     */
    public function getReadPreference();

    /**
     * Set the read preference.
     *
     * @see http://php.net/manual/en/mongocursor.setreadpreference.php
     * @param string $readPreference
     * @param array  $tags
     * @return self
     */
    public function setReadPreference($readPreference, array $tags = null);

    /**
     * Return whether the document's "_id" value is used as its iteration key.
     *
     * @since 1.2
     * @return boolean
     */
    public function getUseIdentifierKeys();

    /**
     * Set whether to use the document's "_id" value as its iteration key.
     *
     * @since 1.2
     * @param boolean $useIdentifierKeys
     * @return self
     */
    public function setUseIdentifierKeys($useIdentifierKeys);

    /**
     * Wrapper method for MongoCursor::hasNext().
     *
     * @see http://php.net/manual/en/mongocursor.hasnext.php
     * @return boolean
     */
    public function hasNext();

    /**
     * Wrapper method for MongoCursor::hint().
     *
     * @see http://php.net/manual/en/mongocursor.hint.php
     * @param array|string $keyPattern
     * @return self
     */
    public function hint($keyPattern);

    /**
     * Wrapper method for MongoCursor::immortal().
     *
     * @see http://php.net/manual/en/mongocursor.immortal.php
     * @param boolean $liveForever
     * @return self
     */
    public function immortal($liveForever = true);

    /**
     * Wrapper method for MongoCursor::info().
     *
     * @see http://php.net/manual/en/mongocursor.info.php
     * @return array
     */
    public function info();

    /**
     * Wrapper method for MongoCursor::limit().
     *
     * @see http://php.net/manual/en/mongocursor.limit.php
     * @param integer $num
     * @return self
     */
    public function limit($num);

    /**
     * Recreates the internal MongoCursor.
     */
    public function recreate();

    /**
     * Wrapper method for MongoCursor::reset().
     *
     * @see http://php.net/manual/en/iterator.reset.php
     * @see http://php.net/manual/en/mongocursor.reset.php
     */
    public function reset();

    /**
     * Wrapper method for MongoCursor::skip().
     *
     * @see http://php.net/manual/en/mongocursor.skip.php
     * @param integer $num
     * @return self
     */
    public function skip($num);

    /**
     * Wrapper method for MongoCursor::slaveOkay().
     *
     * @see http://php.net/manual/en/mongocursor.slaveokay.php
     * @param boolean $ok
     * @return self
     */
    public function slaveOkay($ok = true);

    /**
     * Wrapper method for MongoCursor::snapshot().
     *
     * @see http://php.net/manual/en/mongocursor.snapshot.php
     * @return self
     */
    public function snapshot();

    /**
     * Wrapper method for MongoCursor::sort().
     *
     * @see http://php.net/manual/en/mongocursor.sort.php
     * @param array $fields
     * @return self
     */
    public function sort($fields);

    /**
     * Wrapper method for MongoCursor::tailable().
     *
     * @see http://php.net/manual/en/mongocursor.tailable.php
     * @param boolean $tail
     * @return self
     */
    public function tailable($tail = true);

    /**
     * Wrapper method for MongoCursor::timeout().
     *
     * @see http://php.net/manual/en/mongocursor.timeout.php
     * @param integer $ms
     * @return self
     */
    public function timeout($ms);
}
