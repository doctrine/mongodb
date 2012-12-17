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

namespace Doctrine\MongoDB\Util;

/**
 * Static class containing methods for parsing read preferences.
 *
 * @license     http://www.opensource.org/licenses/mit-license.php MIT
 * @link        www.doctrine-project.org
 * @since       1.0
 * @author      Jeremy Mikola <jmikola@gmail.com>
 */
final class ReadPreference
{
    /**
     * Read preference types.
     *
     * The numeric indexes correspond to the values for each read preference
     * returned by getReadPreference() methods. Meanwhile, setReadPreference()
     * expects the string constants. This array is used to translate between
     * both formats.
     */
    private static $types = array(
        \MongoClient::RP_PRIMARY,
        \MongoClient::RP_PRIMARY_PREFERRED,
        \MongoClient::RP_SECONDARY,
        \MongoClient::RP_SECONDARY_PREFERRED,
        \MongoClient::RP_NEAREST,
    );

    /**
     * Private constructor (prevents instantiation)
     */
    private function __construct() {}

    /**
     * Converts a numeric type returned by getReadPreference() methods to the
     * constant accepted by setReadPreference() methods.
     *
     * @param integer $type
     * @return string
     */
    public static function convertNumericType($type)
    {
        if (!isset(self::$types[$type])) {
            throw new InvalidArgumentException('Unknown read preference type: ' . $type);
        }

        return self::$types[$type];
    }

    /**
     * Converts tag sets returned by getReadPreference() methods to the format
     * accepted by setReadPreference() methods.
     *
     * Example input:
     *
     *     [['dc:east','use:reporting'],['dc:west'],[]]
     *
     * Example output:
     *
     *     [{dc:'east', use:'reporting'},{dc:'west'},{}]
     *
     * @param array $tagSets
     * @return array
     */
    public static function convertTagSets(array $tagSets)
    {
        return array_map(function(array $tagSet) {
            $result = array();

            foreach ($tagSet as $tagAndValue) {
                list($tag, $value) = explode(':', $tagAndValue, 2);
                $result[$tag] = $value;
            }

            return $result;
        }, $tagSets);
    }
}
