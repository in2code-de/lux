<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model\Transfer;

use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class FilterDto is a filter class that helps filtering visitors by given parameters. Per default, get visitors
 * from the current year.
 */
class FilterDto
{
    const PERIOD_DEFAULT = 0;
    const PERIOD_THISYEAR = 1;
    const PERIOD_THISMONTH = 2;
    const PERIOD_LASTMONTH = 3;
    const PERIOD_LASTYEAR = 4;
    const PERIOD_LAST12MONTH = 10;
    const PERIOD_ALL = 100;
    const IDENTIFIED_ALL = -1;
    const IDENTIFIED_UNKNOWN = 0;
    const IDENTIFIED_IDENTIFIED = 1;

    /**
     * @var string
     */
    protected $searchterm = '';

    /**
     * @var string
     */
    protected $pid = '';

    /**
     * Must be a string like "2021-01-01T15:03:01.012345Z" (date format "c")
     *
     * @var string
     */
    protected $timeFrom = '';

    /**
     * Must be a string like "2021-01-01T15:03:01.012345Z" (date format "c")
     *
     * @var string
     */
    protected $timeTo = '';

    /**
     * @var int
     */
    protected $scoring = 0;

    /**
     * Filter by categoryscoring greater then 0
     *
     * @var \In2code\Lux\Domain\Model\Category
     */
    protected $categoryScoring = null;

    /**
     * @var int
     */
    protected $timePeriod = 0;

    /**
     * @var int
     */
    protected $identified = self::IDENTIFIED_ALL;

    /**
     * If turned on, there is a short timeframe for pagevisits and downloads (the last 7 days) while all other diagrams
     * should show the latest diagrams
     *
     * @var bool
     */
    protected $shortMode = true;

    /**
     * FilterDto constructor.
     *
     * @param int $timePeriod
     */
    public function __construct(int $timePeriod = self::PERIOD_DEFAULT)
    {
        $this->setTimePeriod($timePeriod);
    }

    /**
     * @return string
     */
    public function getSearchterm(): string
    {
        return $this->searchterm;
    }

    /**
     * @return array
     */
    public function getSearchterms(): array
    {
        return GeneralUtility::trimExplode(' ', $this->getSearchterm(), true);
    }

    /**
     * @param string $searchterm
     * @return FilterDto
     */
    public function setSearchterm(string $searchterm)
    {
        $this->searchterm = $searchterm;
        return $this;
    }

    /**
     * @return string
     */
    public function getPid(): string
    {
        return $this->pid;
    }

    /**
     * @param string $pid
     * @return $this
     */
    public function setPid(string $pid)
    {
        $this->pid = $pid;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeFrom(): string
    {
        return $this->timeFrom;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getTimeFromDateTime(): \DateTime
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new \DateTime($this->getTimeFrom());
    }

    /**
     * @param string $timeFrom
     * @return FilterDto
     */
    public function setTimeFrom(string $timeFrom)
    {
        if (!empty($timeFrom)) {
            $this->removeShortMode();
        }
        $this->timeFrom = $timeFrom;
        return $this;
    }

    /**
     * @return string
     */
    public function getTimeTo(): string
    {
        return $this->timeTo;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    public function getTimeToDateTime(): \DateTime
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new \DateTime($this->getTimeTo());
    }

    /**
     * @param string $timeTo
     * @return FilterDto
     */
    public function setTimeTo(string $timeTo)
    {
        if (!empty($timeTo)) {
            $this->removeShortMode();
        }
        $this->timeTo = $timeTo;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimePeriod(): int
    {
        if ($this->timePeriod === self::PERIOD_DEFAULT) {
            return self::PERIOD_LAST12MONTH;
        }
        return $this->timePeriod;
    }

    /**
     * @param int $timePeriod
     * @return FilterDto
     */
    public function setTimePeriod(int $timePeriod)
    {
        $this->timePeriod = $timePeriod;
        return $this;
    }

    /**
     * @return int
     */
    public function getIdentified(): int
    {
        return $this->identified;
    }

    /**
     * @param int $identified
     * @return FilterDto
     */
    public function setIdentified(int $identified)
    {
        $this->identified = $identified;
        return $this;
    }

    /**
     * Set a timeTo and timeFrom if there is a timeframe given in seconds. E.g. 60 means a starttime 60s ago to now.
     *
     * @param int $seconds
     * @return FilterDto
     * @throws \Exception
     */
    public function setTimeFrame(int $seconds)
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $timeFrom = new \DateTime($seconds . ' seconds ago');
        $this->setTimeFrom($timeFrom->format('c'));
        $timeTo = new \DateTime();
        $this->setTimeTo($timeTo->format('c'));
        return $this;
    }

    /**
     * @return int
     */
    public function getScoring(): int
    {
        return $this->scoring;
    }

    /**
     * @param int $scoring
     * @return FilterDto
     */
    public function setScoring(int $scoring)
    {
        $this->scoring = $scoring;
        return $this;
    }

    /**
     * @return Category
     */
    public function getCategoryScoring()
    {
        return $this->categoryScoring;
    }

    /**
     * @param int $categoryUid
     * @return FilterDto
     * @throws Exception
     */
    public function setCategoryScoring(int $categoryUid)
    {
        if ($categoryUid > 0) {
            $categoryRepository = ObjectUtility::getObjectManager()->get(CategoryRepository::class);
            $category = $categoryRepository->findByUid((int)$categoryUid);
            if ($category !== null) {
                $this->categoryScoring = $category;
            }
        }
        return $this;
    }

    /**
     * @return bool
     */
    public function isShortMode(): bool
    {
        return $this->shortMode;
    }

    /**
     * @return FilterDto
     */
    public function setShortMode(): self
    {
        $this->shortMode = true;
        return $this;
    }

    /**
     * @return FilterDto
     */
    public function removeShortMode(): self
    {
        $this->shortMode = false;
        return $this;
    }

    /**
     * Calculated values
     */

    /**
     * @return bool
     */
    public function isSet(): bool
    {
        return $this->searchterm !== '' || $this->pid !== '' || $this->scoring > 0 || $this->categoryScoring !== null
            || $this->timeFrom !== '' || $this->timeTo !== '' || $this->timePeriod !== self::PERIOD_DEFAULT;
    }

    /**
     * Get a start datetime for period filter
     *
     * @param bool $shortmode
     * @return \DateTime
     * @throws \Exception
     */
    public function getStartTimeForFilter(bool $shortmode = false): \DateTime
    {
        if ($this->isTimeFromAndTimeToGiven()) {
            $time = $this->getTimeFromDateTime();
        } else {
            if ($shortmode === false || $this->isShortMode() === false) {
                $time = $this->getStartTimeFromTimePeriod();
            } else {
                $time = $this->getStartTimeFromTimePeriodShort();
            }
        }
        return $time;
    }

    /**
     * Get a stop datetime for period filter
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function getEndTimeForFilter(): \DateTime
    {
        if ($this->isTimeFromAndTimeToGiven()) {
            $time = $this->getTimeToDateTime();
        } else {
            $time = $this->getEndTimeFromTimePeriod();
        }
        return $time;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    protected function getStartTimeFromTimePeriod(): \DateTime
    {
        if ($this->getTimePeriod() === self::PERIOD_ALL) {
            $time = new \DateTime();
            $time->setTimestamp(0);
        } elseif ($this->getTimePeriod() === self::PERIOD_THISYEAR) {
            $time = new \DateTime();
            $time->setDate((int)$time->format('Y'), 1, 1)->setTime(0, 0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_LASTYEAR) {
            $time = new \DateTime();
            $time->setDate(((int)$time->format('Y') - 1), 1, 1)->setTime(0, 0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_THISMONTH) {
            $time = new \DateTime('first day of this month');
            $time->setTime(0, 0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_LASTMONTH) {
            $time = new \DateTime('first day of last month');
            $time->setTime(0, 0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_LAST12MONTH) {
            $time = new \DateTime();
            $time->modify('-12 months')->setTime(0, 0, 0);
        } else {
            $time = new \DateTime();
        }
        return $time;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    protected function getEndTimeFromTimePeriod(): \DateTime
    {
        if ($this->getTimePeriod() === self::PERIOD_LASTYEAR) {
            $time = new \DateTime('first day of january this year');
        } elseif ($this->getTimePeriod() === self::PERIOD_LASTMONTH) {
            $time = new \DateTime('last day of last month');
            $time->setTime(23, 59, 59);
        } else {
            $time = new \DateTime();
        }
        return $time;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    protected function getStartTimeFromTimePeriodShort(): \DateTime
    {
        if ($this->isShortMode()) {
            return new \DateTime('7 days ago midnight');
        }
        return $this->getStartTimeFromTimePeriod();
    }

    /**
     * Example return values
     *  [
     *      'intervals' => [
     *          [
     *              'start' => {DateTime 2020-10-01 00:00:00},
     *              'end' => {DateTime 2020-10-01 23:59:59}
     *          ],
     *          [
     *              'start' => {DateTime 2020-10-02 00:00:00},
     *              'end' => {DateTime 2020-10-02 23:59:59}
     *          ],
     *          [
     *              'start' => {DateTime 2020-10-03 00:00:00},
     *              'end' => {DateTime 2020-10-03 23:59:59}
     *          ],
     *      ],
     *      'frequency' => 'day' // or "week", "month", "year"
     *  ]
     *
     * @return \DateTime[]
     * @throws \Exception
     */
    public function getIntervals(): array
    {
        $intervals = ['frequency' => $this->getStartIntervals()['frequency']];
        $startIntervals = $this->getStartIntervals()['intervals'];
        foreach ($startIntervals as $dateTime) {
            if ($next = next($startIntervals)) {
                $end = clone $next;
                $end->modify('-1 second');
                $intervals['intervals'][] = [
                    'start' => $dateTime,
                    'end' => $end
                ];
            }
        }
        return $intervals;
    }

    /**
     * Example return values
     *  [
     *      'intervals' => [
     *          {DateTime 2020-10-01 00:00:00},
     *          {DateTime 2020-10-02 00:00:00},
     *          {DateTime 2020-10-03 00:00:00}
     *      ],
     *      'frequency' => 'day' // or "week", "month", "year"
     *  ]
     *
     * @return \DateTime[]
     * @throws \Exception
     */
    protected function getStartIntervals(): array
    {
        $start = $this->getStartTimeForFilter(true);
        $end = $this->getEndTimeForFilter();
        $deltaSeconds = $end->getTimestamp() - $start->getTimestamp();
        if ($deltaSeconds <= 86400) { // until 1 days
            return ['intervals' => $this->getHourIntervals(), 'frequency' => 'hour'];
        } elseif ($deltaSeconds <= 1209600) { // until 2 weeks
            return ['intervals' => $this->getDayIntervals(), 'frequency' => 'day'];
        } elseif ($deltaSeconds <= 5184000) { // until 2 month
            return ['intervals' => $this->getWeekIntervals(), 'frequency' => 'week'];
        } elseif ($deltaSeconds <= 63072000) { // until 2 years
            return ['intervals' => $this->getMonthIntervals(), 'frequency' => 'month'];
        } else { // over 2 years
            return ['intervals' => $this->getYearIntervals(), 'frequency' => 'year'];
        }
    }

    /**
     * @return \DateTime[]
     * @throws \Exception
     */
    protected function getHourIntervals(): array
    {
        $start = $this->getStartTimeForFilter(true);
        $end = $this->getEndTimeForFilter();
        $interval = [];
        for ($hour = clone $start; $hour < $end; $hour->modify('+1 hour')) {
            $interval[] = clone $hour;
        }
        $interval[] = $end;
        return $interval;
    }

    /**
     * @return \DateTime[]
     * @throws \Exception
     */
    protected function getDayIntervals(): array
    {
        $start = DateUtility::getDayStart($this->getStartTimeForFilter(true));
        $end = $this->getEndTimeForFilter();
        $interval = [];
        for ($day = clone $start; $day < $end; $day->modify('+1 day')) {
            $interval[] = clone $day;
        }
        $interval[] = $end;
        return $interval;
    }

    /**
     * @return \DateTime[]
     * @throws \Exception
     */
    protected function getWeekIntervals(): array
    {
        $start = DateUtility::getPreviousMonday($this->getStartTimeForFilter(true));
        $end = $this->getEndTimeForFilter();
        $interval = [];
        for ($week = clone $start; $week < $end; $week->modify('+1 week')) {
            $interval[] = clone $week;
        }
        $interval[] = $end;
        return $interval;
    }

    /**
     * @return \DateTime[]
     * @throws \Exception
     */
    protected function getMonthIntervals(): array
    {
        $start = DateUtility::getStartOfMonth($this->getStartTimeForFilter(true));
        $end = $this->getEndTimeForFilter();
        $interval = [];
        for ($month = clone $start; $month < $end; $month->modify('+1 month')) {
            $interval[] = clone $month;
        }
        $interval[] = $end;
        return $interval;
    }

    /**
     * @return \DateTime[]
     * @throws \Exception
     */
    protected function getYearIntervals(): array
    {
        $start = DateUtility::getStartOfYear($this->getStartTimeForFilter(true));
        $end = $this->getEndTimeForFilter();
        $interval = [];
        for ($year = clone $start; $year < $end; $year->modify('+1 year')) {
            $interval[] = clone $year;
        }
        $interval[] = $end;
        return $interval;
    }

    /**
     * @return bool
     */
    protected function isTimeFromAndTimeToGiven(): bool
    {
        return $this->getTimeFrom() && $this->getTimeTo();
    }
}
