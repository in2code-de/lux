<?php
declare(strict_types=1);
namespace In2code\Lux\Utility;

/**
 * Class DateUtility
 */
class DateUtility
{
    /**
     * Get a number of months and then the beginning date and the ending date of this month
     *
     * @param int $back 0 means this month, 1 last month and so on... (must be a positive value)
     * @return \DateTime[] 0=>start, 1=>end
     * @throws \Exception
     */
    public static function getLatestMonthDates(int $back): array
    {
        $month = new \DateTime();
        if ($back > 0) {
            $month->modify('-' . $back . ' months');
        }
        return [self::getStartOfMonth($month), self::getEndOfMonth($month)];
    }

    /**
     * @param \DateTime $date
     * @return \DateTime
     */
    protected static function getEndOfMonth(\DateTime $date): \DateTime
    {
        $end = clone $date;
        $end->modify('last day of this month')->modify('midnight')->modify('+1 day')->modify('-1 second');
        return $end;
    }

    /**
     * @param \DateTime $date
     * @return \DateTime
     */
    protected static function getStartOfMonth(\DateTime $date): \DateTime
    {
        $start = clone $date;
        $start->modify('first day of this month')->modify('midnight');
        return $start;
    }
}
