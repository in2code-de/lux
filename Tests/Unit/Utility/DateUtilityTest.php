<?php

namespace In2code\Lux\Tests\Unit\Utility;

use DateTime;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\DateUtility;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\Attributes\CoversMethod;
use TYPO3\TestingFramework\Core\Unit\UnitTestCase;

#[CoversClass(DateUtility::class)]
#[CoversMethod(DateUtility::class, 'convertTimestamp')]
#[CoversMethod(DateUtility::class, 'getCurrentOnlineDateTime')]
#[CoversMethod(DateUtility::class, 'getDayEnd')]
#[CoversMethod(DateUtility::class, 'getDayStart')]
#[CoversMethod(DateUtility::class, 'getLatestMonthDates')]
#[CoversMethod(DateUtility::class, 'getNumberOfDaysBetweenTwoDates')]
#[CoversMethod(DateUtility::class, 'getPreviousMonday')]
#[CoversMethod(DateUtility::class, 'getQuarterFromDate')]
#[CoversMethod(DateUtility::class, 'getQuarterStartDateFromDate')]
#[CoversMethod(DateUtility::class, 'getStartOfMonth')]
#[CoversMethod(DateUtility::class, 'getStartOfYear')]
#[CoversMethod(DateUtility::class, 'isNewVisit')]
class DateUtilityTest extends UnitTestCase
{
    public function testConvertTimestamp(): void
    {
        $timestamp = 12345;
        $result = DateUtility::convertTimestamp($timestamp);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertSame((string)$timestamp, $result->format('U'));
    }

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

    public function testGetLatestMonthDates(): void
    {
        $result = DateUtility::getLatestMonthDates(13);
        self::assertSame(2, count($result));
        self::assertTrue(is_a($result[0], DateTime::class));
        self::assertTrue(is_a($result[1], DateTime::class));
        self::assertTrue($result[0]->format('Y') < date('Y'));
        self::assertTrue($result[1]->format('Y') < date('Y'));
    }

    public function testGetCurrentOnlineDateTime(): void
    {
        $result = DateUtility::getCurrentOnlineDateTime();
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < time());
        self::assertTrue($result->format('U') > time() - 6000);
        self::assertTrue((int)$result->format('U') === time() - (DateUtility::IS_ONLINE_TIME * 60));
    }

    public function testGetDayStart(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-12-1 00:01');
        $result = DateUtility::getDayStart($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < $dateToCompare->format('U'));
        self::assertTrue($result->format('U') > ($dateToCompare->format('U') - 600));
    }

    public function testGetDayEnd(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-12-1 23:55');
        $result = DateUtility::getDayEnd($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') > $dateToCompare->format('U'));
        self::assertTrue($result->format('U') < ((int)$dateToCompare->format('U') + 600));
    }

    public function testGetPreviousMonday(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-12-1 23:55');
        $result = DateUtility::getPreviousMonday($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < $dateToCompare->format('U'));
    }

    public function testGetStartOfMonth(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-12-3 23:55');
        $result = DateUtility::getStartOfMonth($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < $dateToCompare->format('U'));
    }

    public function testGetStartOfYear(): void
    {
        $dateToCompare = DateTime::createFromFormat('Y-m-d H:i', '2021-01-02 23:55');
        $result = DateUtility::getStartOfYear($dateToCompare);
        self::assertTrue(is_a($result, DateTime::class));
        self::assertTrue($result->format('U') < $dateToCompare->format('U'));
    }

    public function testGetQuarterFromDate(): void
    {
        self::assertSame(1, DateUtility::getQuarterFromDate(new DateTime('2024-1-1')));
        self::assertSame(1, DateUtility::getQuarterFromDate(new DateTime('2024-3-31')));
        self::assertSame(2, DateUtility::getQuarterFromDate(new DateTime('2024-4-1')));
        self::assertSame(4, DateUtility::getQuarterFromDate(new DateTime('2024-12-24')));
    }

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
