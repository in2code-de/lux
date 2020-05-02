<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Model\Transfer;

use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class FilterDto is a filter class that helps filtering visitors by given parameters. Per default, get visitors
 * from the current year.
 */
class FilterDto
{
    const PERIOD_ALL = 0;
    const PERIOD_THISYEAR = 1;
    const PERIOD_THISMONTH = 2;
    const PERIOD_LASTMONTH = 3;
    const PERIOD_LASTYEAR = 4;
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
    protected $timePeriod = self::PERIOD_ALL;

    /**
     * @var int
     */
    protected $identified = self::IDENTIFIED_ALL;

    /**
     * FilterDto constructor.
     *
     * @param int $timePeriod
     */
    public function __construct(int $timePeriod = self::PERIOD_ALL)
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
        $this->timeTo = $timeTo;
        return $this;
    }

    /**
     * @return int
     */
    public function getTimePeriod(): int
    {
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
        /** @noinspection PhpUnhandledExceptionInspection */
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
     * Calculated values
     */

    /**
     * @return bool
     */
    public function isSet(): bool
    {
        return $this->searchterm !== '' || $this->pid !== '' || $this->scoring > 0 || $this->categoryScoring !== null
            || $this->timeFrom !== '' || $this->timeTo !== '' || $this->timePeriod !== self::PERIOD_THISYEAR;
    }

    /**
     * Get a start datetime for period filter
     *
     * @return \DateTime
     * @throws \Exception
     */
    public function getStartTimeForFilter(): \DateTime
    {
        if ($this->isTimeFromAndTimeToGiven()) {
            $time = $this->getTimeFromDateTime();
        } else {
            $time = $this->getStartTimeFromTimePeriod();
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
        $time = new \DateTime();
        if ($this->getTimePeriod() === self::PERIOD_ALL) {
            $time = new \DateTime();
            $time->setTimestamp(0);
        }
        if ($this->getTimePeriod() === self::PERIOD_THISYEAR) {
            $time = new \DateTime();
            $time->setDate((int)$time->format('Y'), 1, 1)->setTime(0, 0, 0);
        }
        if ($this->getTimePeriod() === self::PERIOD_LASTYEAR) {
            $time = new \DateTime();
            $time->setDate(((int)$time->format('Y') - 1), 1, 1)->setTime(0, 0, 0);
        }
        if ($this->getTimePeriod() === self::PERIOD_THISMONTH) {
            $time = new \DateTime('first day of this month');
            $time->setTime(0, 0, 0);
        }
        if ($this->getTimePeriod() === self::PERIOD_LASTMONTH) {
            $time = new \DateTime('first day of last month');
            $time->setTime(0, 0, 0);
        }
        return $time;
    }

    /**
     * @return \DateTime
     * @throws \Exception
     */
    protected function getEndTimeFromTimePeriod(): \DateTime
    {
        $time = new \DateTime();
        if ($this->getTimePeriod() === self::PERIOD_LASTYEAR) {
            $time = new \DateTime('first day of january this year');
        }
        if ($this->getTimePeriod() === self::PERIOD_LASTMONTH) {
            $time = new \DateTime('last day of last month');
            $time->setTime(23, 59, 59);
        }
        return $time;
    }

    /**
     * @return bool
     */
    protected function isTimeFromAndTimeToGiven(): bool
    {
        return $this->getTimeFrom() && $this->getTimeTo();
    }
}
