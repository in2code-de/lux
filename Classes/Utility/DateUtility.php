<?php
declare(strict_types = 1);
namespace In2code\Lux\Utility;

use DateTime;
use Exception;

/**
 * Class DateUtility
 */
class DateUtility
{
    /**
     * Define a timeframe in minutes to check how long is somebody online
     */
    const IS_ONLINE_TIME = 5;

    /**
     * Define a timeframe in minutes to check if the visitor is still in the same page funnel. Means that when a
     * visitor opens another page within this timerange, we use this opening as a follow up opening of the page before.
     */
    const IS_INSAMEPAGEFUNNEL_TIME = 15;

    /**
     * Convert timestamp into DateTime object
     *
     * @param int $timestamp
     * @return DateTime
     */
    public static function convertTimestamp(int $timestamp): DateTime
    {
        return DateTime::createFromFormat('U', (string)$timestamp);
    }

    /**
     * @param DateTime $date1
     * @param DateTime $date2
     * @return int
     */
    public static function getNumberOfDaysBetweenTwoDates(DateTime $date1, DateTime $date2): int
    {
        $difference = $date1->diff($date2)->days;
        if ($difference < 0) {
            $difference *= -1;
        }
        return $difference;
    }

    /**
     * Get a number of months and then the beginning date and the ending date of this month
     *
     * @param int $back 0 means this month, 1 last month and so on... (must be a positive value)
     * @return DateTime[] 0=>start, 1=>end
     * @throws Exception
     */
    public static function getLatestMonthDates(int $back): array
    {
        $month = new DateTime();
        if ($back > 0) {
            $month->modify('-' . $back . ' months');
        }
        return [self::getStartOfMonth($month), self::getEndOfMonth($month)];
    }

    /**
     * Get a DateTime 5 minutes ago to find out who is online at the moment
     *
     * @return DateTime
     * @throws Exception
     */
    public static function getCurrentOnlineDateTime(): DateTime
    {
        $date = new DateTime();
        $date->modify('-' . self::IS_ONLINE_TIME . ' minutes');
        return $date;
    }

    /**
     * @param DateTime $date
     * @return DateTime
     * @throws Exception
     */
    public static function getDayStart(DateTime $date): DateTime
    {
        $start = clone $date;
        $start->setTime(0, 0);
        return $start;
    }

    /**
     * @param DateTime $date
     * @return DateTime
     * @throws Exception
     */
    public static function getDayEnd(DateTime $date): DateTime
    {
        $end = clone $date;
        $end->modify('midnight')->modify('+1 day')->modify('-1 second');
        return $end;
    }

    /**
     * @param DateTime $date
     * @return DateTime
     */
    public static function getPreviousMonday(DateTime $date): DateTime
    {
        $new = clone $date;
        $new->modify('monday this week');
        return $new;
    }

    /**
     * @param DateTime $date
     * @return DateTime
     */
    public static function getStartOfMonth(DateTime $date): DateTime
    {
        $start = clone $date;
        $start->modify('first day of this month')->modify('midnight');
        return $start;
    }

    /**
     * @param DateTime $date
     * @return DateTime
     */
    protected static function getEndOfMonth(DateTime $date): DateTime
    {
        $end = clone $date;
        $end->modify('last day of this month')->modify('midnight')->modify('+1 day')->modify('-1 second');
        return $end;
    }

    /**
     * @param DateTime $date
     * @return DateTime
     */
    public static function getStartOfYear(DateTime $date): DateTime
    {
        $start = clone $date;
        $start->modify('first day of january this year')->modify('midnight');
        return $start;
    }
}
