<?php

namespace Doctrine\MongoDB\Util;

/**
 * Utility class for converting read preferences.
 *
 * This is necessary for versions of the driver <=1.3.2, where values returned
 * by getReadPreference() are not consistent with those expected by
 * setReadPreference(). See: https://jira.mongodb.org/browse/PHP-638.
 *
 * @since  1.0
 * @author Jeremy Mikola <jmikola@gmail.com>
 * @deprecated 1.3 No longer required; will be removed for 2.0
 */
final class ReadPreference
{
    /**
     * Read preference types.
     *
     * The indexes correspond to the numeric values for each read preference
     * returned by getReadPreference() methods, while the values correspond to
     * the string constants expected by setReadPreference() methods.
     */
    private static $types = [
        \MongoClient::RP_PRIMARY,
        \MongoClient::RP_PRIMARY_PREFERRED,
        \MongoClient::RP_SECONDARY,
        \MongoClient::RP_SECONDARY_PREFERRED,
        \MongoClient::RP_NEAREST,
    ];

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
        if (! isset(self::$types[$type])) {
            throw new \InvalidArgumentException('Unknown numeric read preference type: ' . $type);
        }

        return self::$types[$type];
    }

    /**
     * Converts return values from getReadPreference() methods to the format
     * accepted by setReadPreference() methods.
     *
     * This is necessary for MongoClient, MongoDB, and MongoCollection classes
     * in driver versions between 1.3.0 and 1.3.3.
     *
     * @since 1.1
     * @param array $readPref
     * @return array
     */
    public static function convertReadPreference(array $readPref)
    {
        if (is_numeric($readPref['type'])) {
            $readPref['type'] = self::convertNumericType($readPref['type']);
        }

        if (isset($readPref['type_string'])) {
            unset($readPref['type_string']);
        }

        if ( ! empty($readPref['tagsets'])) {
            $readPref['tagsets'] = self::convertTagSets($readPref['tagsets']);
        }

        return $readPref;
    }

    /**
     * Converts tag sets returned by getReadPreference() methods to the format
     * accepted by setReadPreference() methods.
     *
     * Example input:
     *
     *     [['dc:east', 'use:reporting'], ['dc:west'], []]
     *
     * Example output:
     *
     *     [['dc' => 'east', 'use' => 'reporting'], ['dc' => 'west'], []]
     *
     * @param array $tagSets
     * @return array
     */
    public static function convertTagSets(array $tagSets)
    {
        return array_map(function(array $tagSet) {
            /* If the tag set does not contain a zeroth element, or that element
             * does not contain a colon character, we can assume this tag set is
             * already in the format expected by setReadPreference().
             */
            if (! isset($tagSet[0]) || false === strpos($tagSet[0], ':')) {
                return $tagSet;
            }

            $result = [];

            foreach ($tagSet as $tagAndValue) {
                list($tag, $value) = explode(':', $tagAndValue, 2);
                $result[$tag] = $value;
            }

            return $result;
        }, $tagSets);
    }
}
