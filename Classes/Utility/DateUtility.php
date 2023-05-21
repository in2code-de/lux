<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use DateTime;
use Exception;

class DateUtility
{
    /**
     * Define a timeframe in minutes to check how long is somebody online
     */
    const IS_ONLINE_TIME = 5;

    /**
     * Define a timeframe in minutes to check if the visitor is still in the same page funnel. Means that when a
     * visitor opens another page within this timerange, we use this opening as a follow-up opening of the page before.
     */
    const IS_INSAMEPAGEFUNNEL_TIME = 15;

    public static function convertTimestamp(int $timestamp): DateTime
    {
        return DateTime::createFromFormat('U', (string)$timestamp);
    }

    public static function getNumberOfDaysBetweenTwoDates(DateTime $date1, DateTime $date2): int
    {
        $difference = $date1->diff($date2)->days;
        if ($difference < 0) {
            $difference *= -1;
        }
        return $difference;
    }

    /**
     * Get a couple of start and end datetime objects (beginning and ending of a month) by a given number of months
     *
     *  Example result for 3 (3 months back - February 2023 - when we would currently have April 2023):
     *  [
     *      [
     *          [DateTime 1.4.2023 0:00],
     *          [DateTime 30.4.2023 23:59],
     *      ],
     *      [
     *          [DateTime 1.3.2023 0:00],
     *          [DateTime 31.3.2023 23:59],
     *      ],
     *      [
     *          [DateTime 1.2.2023 0:00],
     *          [DateTime 28.2.2023 23:59],
     *      ],
     *  ]
     *
     * @param int $back
     * @return array
     * @throws Exception
     */
    public static function getLatestMonthDatesMultiple(int $back): array
    {
        $dates = [];
        for ($iteration = 0; $iteration < $back; $iteration++) {
            $dates[] = self::getLatestMonthDates($iteration);
        }
        return $dates;
    }

    /**
     * Get a number of months and then the beginning date and the ending date of this month
     *
     *  Example result for 3 (3 months back - January 2023 - when we would currently have April 2023):
     *  [
     *      [DateTime 1.1.2023 0:00],
     *      [DateTime 31.1.2023 23:59],
     *  ]
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

    public static function getDayStart(DateTime $date): DateTime
    {
        $start = clone $date;
        $start->setTime(0, 0);
        return $start;
    }

    public static function getDayEnd(DateTime $date): DateTime
    {
        $end = clone $date;
        $end->modify('midnight')->modify('+1 day')->modify('-1 second');
        return $end;
    }

    public static function getPreviousMonday(DateTime $date): DateTime
    {
        $new = clone $date;
        $new->modify('monday this week');
        return $new;
    }

    public static function getStartOfMonth(DateTime $date): DateTime
    {
        $start = clone $date;
        $start->modify('first day of this month')->modify('midnight');
        return $start;
    }

    protected static function getEndOfMonth(DateTime $date): DateTime
    {
        $end = clone $date;
        $end->modify('last day of this month')->modify('midnight')->modify('+1 day')->modify('-1 second');
        return $end;
    }

    public static function getStartOfYear(DateTime $date): DateTime
    {
        $start = clone $date;
        $start->modify('first day of january this year')->modify('midnight');
        return $start;
    }
}
