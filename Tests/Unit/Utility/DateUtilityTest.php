<?php
namespace In2code\Lux\Tests\Unit\Utility;

use DateTime;
use Exception;
use In2code\Lux\Utility\DateUtility;
use Nimut\TestingFramework\TestCase\UnitTestCase;

/**
 * Class DateUtilityTest
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
        $this->assertTrue(is_a($result, DateTime::class));
        $this->assertSame((string)$timestamp, $result->format('U'));
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
        $this->assertSame(29, DateUtility::getNumberOfDaysBetweenTwoDates($date1, $date2));
        $this->assertSame(1282, DateUtility::getNumberOfDaysBetweenTwoDates($date1, $date3));
        $this->assertSame(994, DateUtility::getNumberOfDaysBetweenTwoDates($date1, $date4));
        $this->assertSame(1253, DateUtility::getNumberOfDaysBetweenTwoDates($date2, $date3));
        $this->assertSame(1023, DateUtility::getNumberOfDaysBetweenTwoDates($date2, $date4));
        $this->assertSame(2277, DateUtility::getNumberOfDaysBetweenTwoDates($date3, $date4));
        $this->assertSame(0, DateUtility::getNumberOfDaysBetweenTwoDates($date4, $date5));
    }

    /**
     * @return void
     * @covers ::getLatestMonthDates
     * @throws Exception
     */
    public function testGetLatestMonthDates(): void
    {
        $result = DateUtility::getLatestMonthDates(13);
        $this->assertSame(2, count($result));
        $this->assertTrue(is_a($result[0], DateTime::class));
        $this->assertTrue(is_a($result[1], DateTime::class));
        $this->assertTrue($result[0]->format('Y') < date('Y'));
        $this->assertTrue($result[1]->format('Y') < date('Y'));
    }

    /**
     * @return void
     * @covers ::getCurrentOnlineDateTime
     * @throws Exception
     */
    public function testGetCurrentOnlineDateTime(): void
    {
        $result = DateUtility::getCurrentOnlineDateTime();
        $this->assertTrue(is_a($result, DateTime::class));
        $this->assertTrue($result->format('U') < time());
        $this->assertTrue($result->format('U') > time() - 6000);
        $this->assertTrue((int)$result->format('U') === time() - (DateUtility::IS_ONLINE_TIME * 60));
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
        $this->assertTrue(is_a($result, DateTime::class));
        $this->assertTrue($result->format('U') < $dateToCompare->format('U'));
        $this->assertTrue($result->format('U') > ($dateToCompare->format('U') - 600));
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
        $this->assertTrue(is_a($result, DateTime::class));
        $this->assertTrue($result->format('U') > $dateToCompare->format('U'));
        $this->assertTrue($result->format('U') < ((int)$dateToCompare->format('U') + 600));
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
        $this->assertTrue(is_a($result, DateTime::class));
        $this->assertTrue($result->format('U') < $dateToCompare->format('U'));
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
        $this->assertTrue(is_a($result, DateTime::class));
        $this->assertTrue($result->format('U') < $dateToCompare->format('U'));
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
        $this->assertTrue(is_a($result, DateTime::class));
        $this->assertTrue($result->format('U') < $dateToCompare->format('U'));
    }
}
