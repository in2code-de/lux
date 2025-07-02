<?php

namespace In2code\Lux\Tests\Unit\Utility;

use DateTime;
use Exception;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\DateUtility;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

/**
 * @coversDefaultClass DateUtility
 */
class DateUtilityTest extends UnitTestCase
{
    /**
     * @return void
     * @covers ::convertTimestamp
     */
    public function testConvertTimestamp(): void
    {
        $timestamp = 12345;
        $result = DateUtility::convertTimestamp($timestamp);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertSame((string)$timestamp, $result->format('U'));
    }

    /**
     * @return void
     * @covers ::getNumberOfDaysBetweenTwoDates
     */
    public function testGetNumberOfDaysBetweenTwoDates(): void
    {
        $date1 = DateTime::createFromFormat('Y-m-d H:i', '2021-12-1 12:00');
        $date2 = DateTime::createFromFormat('Y-m-d H:i', '2021-12-30 12:00');
        $date3 = DateTime::createFromFormat('Y-m-d H:i', '2025-06-06 00:01');
        $date4 = DateTime::createFromFormat('Y-m-d H:i', '2019-03-13 00:00');
        $date5 = DateTime::createFromFormat('Y-m-d H:i', '2019-03-13 23:39');
        self::assertSame(29, DateUtility::getNumberOfDaysBetweenTwoDates($date1, $date2));
        self::assertSame(1282, DateUtility::getNumberOfDaysBetweenTwoDates($date1, $date3));
        self::assertSame(994, DateUtility::getNumberOfDaysBetweenTwoDates($date1, $date4));
        self::assertSame(1253, DateUtility::getNumberOfDaysBetweenTwoDates($date2, $date3));
        self::assertSame(1023, DateUtility::getNumberOfDaysBetweenTwoDates($date2, $date4));
        self::assertSame(2277, DateUtility::getNumberOfDaysBetweenTwoDates($date3, $date4));
        self::assertSame(0, DateUtility::getNumberOfDaysBetweenTwoDates($date4, $date5));
    }

    /**
     * @return void
     * @covers ::getLatestMonthDates
     * @throws Exception
     */
    public function testGetLatestMonthDates(): void
    {
        $result = DateUtility::getLatestMonthDates(13);
        self::assertSame(2, count($result));
        self::assertTrue(is_a($result[0], DateTime::class));
        self::assertTrue(is_a($result[1], DateTime::class));
        self::assertTrue($result[0]->format('Y') < date('Y'));
        self::assertTrue($result[1]->format('Y') < date('Y'));
    }

    /**
     * @return void
     * @covers ::getCurrentOnlineDateTime
     * @throws Exception
     */
    public function testGetCurrentOnlineDateTime(): void
    {
        $result = DateUtility::getCurrentOnlineDateTime();
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < time());
        self::assertTrue($result->format('U') > time() - 6000);
        self::assertTrue((int)$result->format('U') === time() - (DateUtility::IS_ONLINE_TIME * 60));
    }

    /**
     * @return void
     * @covers ::getDayStart
     * @throws Exception
     */
    public function testGetDayStart(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-12-1 00:01');
        $result = DateUtility::getDayStart($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < $dateToCompare->format('U'));
        self::assertTrue($result->format('U') > ($dateToCompare->format('U') - 600));
    }

    /**
     * @return void
     * @covers ::getDayEnd
     * @throws Exception
     */
    public function testGetDayEnd(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-12-1 23:55');
        $result = DateUtility::getDayEnd($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') > $dateToCompare->format('U'));
        self::assertTrue($result->format('U') < ((int)$dateToCompare->format('U') + 600));
    }

    /**
     * @return void
     * @covers ::getPreviousMonday
     * @throws Exception
     */
    public function testGetPreviousMonday(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-12-1 23:55');
        $result = DateUtility::getPreviousMonday($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < $dateToCompare->format('U'));
    }

    /**
     * @return void
     * @covers ::getStartOfMonth
     * @throws Exception
     */
    public function testGetStartOfMonth(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-12-3 23:55');
        $result = DateUtility::getStartOfMonth($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < $dateToCompare->format('U'));
    }

    /**
     * @return void
     * @covers ::getStartOfYear
     * @throws Exception
     */
    public function testGetStartOfYear(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-01-02 23:55');
        $result = DateUtility::getStartOfYear($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < $dateToCompare->format('U'));
    }

    /**
     * @return void
     * @covers ::getQuarterFromDate
     */
    public function testGetQuarterFromDate(): void
    {
        self::assertSame(1, DateUtility::getQuarterFromDate(new DateTime('2024-1-1')));
        self::assertSame(1, DateUtility::getQuarterFromDate(new DateTime('2024-3-31')));
        self::assertSame(2, DateUtility::getQuarterFromDate(new DateTime('2024-4-1')));
        self::assertSame(4, DateUtility::getQuarterFromDate(new DateTime('2024-12-24')));
    }

    /**
     * @return void
     * @covers ::getQuarterStartDateFromDate
     */
    public function testGetQuarterStartDateFromDate(): void
    {
        self::assertSame(
            (new DateTime('2024-1-1'))->getTimestamp(),
            DateUtility::getQuarterStartDateFromDate(new DateTime('2024-1-1'))->getTimestamp()
        );
        self::assertSame(
            (new DateTime('2024-1-1'))->getTimestamp(),
            DateUtility::getQuarterStartDateFromDate(new DateTime('2024-3-13'))->getTimestamp()
        );
        self::assertSame(
            (new DateTime('2024-10-1'))->getTimestamp(),
            DateUtility::getQuarterStartDateFromDate(new DateTime('2024-11-11'))->getTimestamp()
        );
    }

    /**
     * @return void
     * @covers ::isNewVisit
     * @throws ConfigurationException
     */
    public function testIsNewVisit(): void
    {
        self::assertTrue(DateUtility::isNewVisit(new DateTime('2025-12-24'), new DateTime('2025-12-31')));
        self::assertFalse(DateUtility::isNewVisit(new DateTime('2025-12-24 12:00:00'), new DateTime('2025-12-24 12:01:00')));
        self::assertTrue(DateUtility::isNewVisit(new DateTime('2025-12-24 12:00:00'), new DateTime('2025-12-24 12:05:00')));

        $this->expectExceptionCode(1744734078);
        DateUtility::isNewVisit(new DateTime('2025-12-31'), new DateTime('2025-12-24'));
    }
}
