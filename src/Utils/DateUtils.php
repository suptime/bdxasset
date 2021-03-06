<?php

namespace suptime\bdxasset\Utils;

/**
 * Utilities for parsing and formatting dates.
 *
 * <p>
 * Note that this class doesn't use static methods because of the
 * synchronization issues with SimpleDateFormat. This lets synchronization be
 * done on a per-object level, instead of on a per-class level.
 */
class DateUtils
{
    /**
     * Alternate ISO 8601 format without fractional seconds
     */
    const ALTERNATE_ISO8601_DATE_FORMAT = "Y-m-d\TH:i:s\Z";

    /**
     * create utc timezone
     * @return \DateTimeZone
     */
    public static function UTCTimezone()
    {
        return new \DateTimeZone("UTC");
    }

    /**
     * Parses the specified date string as an ISO 8601 date and returns the Date
     * object.
     *
     * @param $dateString string The date string to parse.
     * @return \DateTime The parsed Date object.
     * @throws \Exception If the date string could not be parsed.
     */
    public static function parseAlternateIso8601Date($dateString)
    {
        return \DateTime::createFromFormat(self::ALTERNATE_ISO8601_DATE_FORMAT, $dateString, self::UTCTimezone());
    }

    /**
     * Formats the specified date as an ISO 8601 string.
     *
     * @param $datetime \DateTime The date to format.
     * @return string The ISO 8601 string representing the specified date.
     */
    public static function formatAlternateIso8601Date($datetime)
    {
        return $datetime->format(self::ALTERNATE_ISO8601_DATE_FORMAT);
    }

    /**
     * Parses the specified date string as an RFC 822 date and returns the Date object.
     *
     * @param $dateString string The date string to parse.
     * @return \DateTime The parsed Date object.
     * @throws \Exception If the date string could not be parsed.
     */
    public static function parseRfc822Date($dateString)
    {
        return \DateTime::createFromFormat(\DateTime::RFC822, $dateString, self::UTCTimezone());
    }

    /**
     * Formats the specified date as an RFC 822 string.
     *
     * @param $datetime \DateTime The date to format.
     * @return string The RFC 822 string representing the specified date.
     */
    public static function formatRfc822Date($datetime)
    {
        return $datetime->format(\DateTime::RFC822);
    }
}
