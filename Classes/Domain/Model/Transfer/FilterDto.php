<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Model\Transfer;

use DateTime;
use Exception;
use In2code\Lux\Domain\Model\Category;
use In2code\Lux\Domain\Model\Company;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\CategoryRepository;
use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Utility\DateUtility;
use Throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

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
    const PERIOD_LAST3MONTH = 15;
    const PERIOD_LAST7DAYS = 20;
    const PERIOD_7DAYSBEFORELAST7DAYS = 21;
    const PERIOD_ALL = 100;
    const IDENTIFIED_ALL = -1;
    const IDENTIFIED_UNKNOWN = 0;
    const IDENTIFIED_IDENTIFIED = 1;

    protected string $searchterm = '';
    protected string $pid = '';

    /**
     * Must be a string like "2021-01-01T15:03:01.012345Z" (date format "c")
     *
     * @var string
     */
    protected string $timeFrom = '';

    /**
     * Must be a string like "2021-01-01T15:03:01.012345Z" (date format "c")
     *
     * @var string
     */
    protected string $timeTo = '';

    protected int $scoring = 0;
    protected int $timePeriod = self::PERIOD_DEFAULT;
    protected int $identified = self::IDENTIFIED_ALL;

    /**
     * Filter by categoryscoring greater then 0
     *
     * @var ?Category
     */
    protected ?Category $categoryScoring = null;
    protected ?Category $category = null;

    /**
     * If turned on, there is a short timeframe for pagevisits and downloads (the last 7 days) while all other diagrams
     * should show the latest diagrams
     *
     * @var bool
     */
    protected bool $shortMode = true;

    protected string $domain = '';
    protected string $site = '';
    protected string $utmCampaign = '';
    protected string $utmSource = '';
    protected string $utmMedium = '';
    protected int $branchCode = 0;
    protected string $revenueClass = '';
    protected string $sizeClass = '';
    protected ?Visitor $visitor = null;
    protected ?Company $company = null;

    public function __construct(int $timePeriod = self::PERIOD_DEFAULT)
    {
        $this->setTimePeriod($timePeriod);
    }

    public function getSearchterm(): string
    {
        return $this->searchterm;
    }

    public function isSearchtermSet(): bool
    {
        return $this->getSearchterm() !== '';
    }

    public function getSearchterms(): array
    {
        return GeneralUtility::trimExplode(' ', $this->getSearchterm(), true);
    }

    public function setSearchterm(string $searchterm): self
    {
        $this->searchterm = $searchterm;
        return $this;
    }

    public function getPid(): string
    {
        return $this->pid;
    }

    public function isPidSet(): bool
    {
        return $this->getPid() !== '';
    }

    public function setPid(string $pid): self
    {
        $this->pid = $pid;
        return $this;
    }

    public function getTimeFrom(): string
    {
        return $this->timeFrom;
    }

    public function isTimeFromSet(): bool
    {
        return $this->getTimeFrom() !== '';
    }

    public function getTimeFromDateTime(): DateTime
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        return new DateTime($this->getTimeFrom());
    }

    public function setTimeFrom(string $timeFrom): self
    {
        if (!empty($timeFrom)) {
            $this->removeShortMode();
        }
        $this->timeFrom = $timeFrom;
        return $this;
    }

    public function getTimeTo(): string
    {
        return $this->timeTo;
    }

    public function isTimeToSet(): bool
    {
        return $this->getTimeTo() !== '';
    }

    public function getTimeToDateTime(): DateTime
    {
        $timeTo = $this->getTimeTo();
        if ($timeTo !== '') {
            try {
                return new DateTime($timeTo);
            } catch (Throwable $exception) {
                // Return default datetime
            }
        }
        return new DateTime();
    }

    public function setTimeTo(string $timeTo): self
    {
        if (!empty($timeTo)) {
            $this->removeShortMode();
        }
        $this->timeTo = $timeTo;
        return $this;
    }

    public function getTimePeriod(): int
    {
        if ($this->timePeriod === self::PERIOD_DEFAULT) {
            return self::PERIOD_LAST12MONTH;
        }
        return $this->timePeriod;
    }

    public function isTimePeriodSet(): bool
    {
        return $this->timePeriod !== self::PERIOD_DEFAULT;
    }

    public function setTimePeriod(int $timePeriod): self
    {
        $this->timePeriod = $timePeriod;
        return $this;
    }

    public function getIdentified(): int
    {
        return $this->identified;
    }

    public function isIdentifiedSet(): bool
    {
        return $this->getIdentified() !== self::IDENTIFIED_ALL;
    }

    public function setIdentified(int $identified): self
    {
        $this->identified = $identified;
        return $this;
    }

    /**
     * Set a timeTo and timeFrom if there is a timeframe given in seconds. E.g. 60 means a starttime 60s ago to now.
     *
     * @param int $seconds
     * @return FilterDto
     * @throws Exception
     */
    public function setTimeFrame(int $seconds): self
    {
        /** @noinspection PhpUnhandledExceptionInspection */
        $timeFrom = new DateTime($seconds . ' seconds ago');
        $this->setTimeFrom($timeFrom->format('c'));
        $timeTo = new DateTime();
        $this->setTimeTo($timeTo->format('c'));
        return $this;
    }

    public function getScoring(): int
    {
        return $this->scoring;
    }

    public function isScoringSet(): bool
    {
        return $this->getScoring() > 0;
    }

    public function setScoring(int $scoring): self
    {
        $this->scoring = $scoring;
        return $this;
    }

    public function getCategoryScoring(): ?Category
    {
        return $this->categoryScoring;
    }

    public function isCategoryScoringSet(): bool
    {
        return $this->getCategoryScoring() !== null;
    }

    public function setCategoryScoring(int $categoryUid): self
    {
        if ($categoryUid > 0) {
            $categoryRepository = GeneralUtility::makeInstance(CategoryRepository::class);
            $category = $categoryRepository->findByUid((int)$categoryUid);
            if ($category !== null) {
                $this->categoryScoring = $category;
            }
        }
        return $this;
    }

    public function getCategory(): ?Category
    {
        return $this->category;
    }

    public function isCategorySet(): bool
    {
        return $this->getCategory() !== null;
    }

    public function setCategory(?Category $category): self
    {
        $this->category = $category;
        return $this;
    }

    public function isShortMode(): bool
    {
        return $this->shortMode;
    }

    public function setShortMode(): self
    {
        $this->shortMode = true;
        return $this;
    }

    public function removeShortMode(): self
    {
        $this->shortMode = false;
        return $this;
    }

    public function getDomain(): string
    {
        return $this->domain;
    }

    public function isDomainSet(): bool
    {
        return $this->getDomain() !== '';
    }

    public function setDomain(string $domain): self
    {
        $this->domain = $domain;
        return $this;
    }

    public function getSite(): string
    {
        return $this->site;
    }

    public function isSiteSet(): bool
    {
        return $this->getSite() !=='';
    }

    public function setSite(string $site): self
    {
        // Don't allow to pass not allowed site in filter
        if (array_key_exists($site, $this->getAllowedSites()) === false) {
            $site = '';
        }
        $this->site = $site;
        return $this;
    }

    public function getUtmCampaign(): string
    {
        return $this->utmCampaign;
    }

    public function isUtmCampaignSet(): bool
    {
        return $this->getUtmCampaign() !== '';
    }

    public function setUtmCampaign(string $utmCampaign): FilterDto
    {
        $this->utmCampaign = $utmCampaign;
        return $this;
    }

    public function getUtmSource(): string
    {
        return $this->utmSource;
    }

    public function isUtmSourceSet(): bool
    {
        return $this->getUtmSource() !== '';
    }

    public function setUtmSource(string $utmSource): FilterDto
    {
        $this->utmSource = $utmSource;
        return $this;
    }

    public function getUtmMedium(): string
    {
        return $this->utmMedium;
    }

    public function isUtmMediumSet(): bool
    {
        return $this->getUtmMedium() !== '';
    }

    public function setUtmMedium(string $utmMedium): FilterDto
    {
        $this->utmMedium = $utmMedium;
        return $this;
    }

    public function getBranchCode(): int
    {
        return $this->branchCode;
    }

    public function isBranchCodeSet(): bool
    {
        return $this->getBranchCode() > 0;
    }

    public function setBranchCode(int $branchCode): self
    {
        $this->branchCode = $branchCode;
        return $this;
    }

    public function getRevenueClass(): string
    {
        return $this->revenueClass;
    }

    public function isRevenueClassSet(): bool
    {
        return $this->getRevenueClass() !== '';
    }

    public function setRevenueClass(string $revenueClass): self
    {
        $this->revenueClass = $revenueClass;
        return $this;
    }

    public function getSizeClass(): string
    {
        return $this->sizeClass;
    }

    public function isSizeClassSet(): bool
    {
        return $this->getSizeClass() !== '';
    }

    public function setSizeClass(string $sizeClass): self
    {
        $this->sizeClass = $sizeClass;
        return $this;
    }

    public function getVisitor(): ?Visitor
    {
        return $this->visitor;
    }

    public function setVisitor(?Visitor $visitor): self
    {
        $this->visitor = $visitor;
        return $this;
    }

    public function getCompany(): ?Company
    {
        return $this->company;
    }

    public function setCompany(?Company $company): self
    {
        $this->company = $company;
        return $this;
    }

    /**
     * Calculated values from here
     */
    public function isSet(): bool
    {
        return $this->isSearchtermSet()
            || $this->isPidSet()
            || $this->isScoringSet()
            || $this->isCategoryScoringSet()
            || $this->isCategorySet()
            || $this->isTimeFromSet()
            || $this->isTimeToSet()
            || $this->isTimePeriodSet()
            || $this->isIdentifiedSet()
            || $this->isDomainSet()
            || $this->isSiteSet()
            || $this->isUtmCampaignSet()
            || $this->isUtmMediumSet()
            || $this->isUtmSourceSet()
            || $this->isBranchCodeSet()
            || $this->isRevenueClassSet()
            || $this->isSizeClassSet();
    }

    public function isTimeFromOrTimeToSet(): bool
    {
        return $this->timeFrom !== '' || $this->timeTo !== '';
    }

    /**
     * Is only a searchterm given and nothing else in backend filter?
     *
     * @return bool
     */
    protected function isOnlySearchtermGiven(): bool
    {
        return $this->searchterm !== '' && $this->pid === '' && $this->scoring === 0 && $this->categoryScoring === null
            && $this->timeFrom === '' && $this->timeTo === '' && $this->timePeriod === self::PERIOD_DEFAULT
            && $this->identified === self::IDENTIFIED_ALL && $this->domain === '';
    }

    /**
     * Get a start datetime for period filter
     *
     * @param bool $shortmode
     * @return DateTime
     * @throws Exception
     */
    public function getStartTimeForFilter(bool $shortmode = false): DateTime
    {
        if ($this->getTimeFrom()) {
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
     * @return DateTime
     * @throws Exception
     */
    public function getEndTimeForFilter(): DateTime
    {
        if ($this->getTimeFrom()) {
            $time = $this->getTimeToDateTime();
        } else {
            $time = $this->getEndTimeFromTimePeriod();
        }
        return $time;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    protected function getStartTimeFromTimePeriod(): DateTime
    {
        if ($this->getTimePeriod() === self::PERIOD_ALL || $this->isOnlySearchtermGiven()) {
            $time = new DateTime();
            $time->setTimestamp(0);
        } elseif ($this->getTimePeriod() === self::PERIOD_THISYEAR) {
            $time = new DateTime();
            $time->setDate((int)$time->format('Y'), 1, 1)->setTime(0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_LASTYEAR) {
            $time = new DateTime();
            $time->setDate(((int)$time->format('Y') - 1), 1, 1)->setTime(0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_THISMONTH) {
            $time = new DateTime('first day of this month');
            $time->setTime(0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_LASTMONTH) {
            $time = new DateTime('first day of last month');
            $time->setTime(0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_LAST3MONTH) {
            $time = new DateTime();
            $time->modify('-3 months')->setTime(0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_LAST12MONTH) {
            $time = new DateTime();
            $time->modify('-12 months')->setTime(0, 0);
        } elseif ($this->getTimePeriod() === self::PERIOD_LAST7DAYS) {
            $time = new DateTime('7 days ago midnight');
        } elseif ($this->getTimePeriod() === self::PERIOD_7DAYSBEFORELAST7DAYS) {
            $time = new DateTime('14 days ago midnight');
        } else {
            $time = new DateTime();
        }
        return $time;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    protected function getEndTimeFromTimePeriod(): DateTime
    {
        if ($this->getTimePeriod() === self::PERIOD_LASTYEAR) {
            $time = new DateTime('first day of january this year');
        } elseif ($this->getTimePeriod() === self::PERIOD_LASTMONTH) {
            $time = new DateTime('last day of last month');
            $time->setTime(23, 59, 59);
        } elseif ($this->getTimePeriod() === self::PERIOD_7DAYSBEFORELAST7DAYS) {
            $time = new DateTime('7 days ago midnight');
        } else {
            $time = new DateTime();
        }
        return $time;
    }

    /**
     * @return DateTime
     * @throws Exception
     */
    protected function getStartTimeFromTimePeriodShort(): DateTime
    {
        if ($this->isShortMode()) {
            return new DateTime('7 days ago midnight');
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
     * @return DateTime[]
     * @throws Exception
     */
    public function getIntervals(): array
    {
        $intervals = ['frequency' => $this->getStartIntervals()['frequency'], 'intervals' => []];
        $startIntervals = $this->getStartIntervals()['intervals'];
        foreach ($startIntervals as $dateTime) {
            if ($next = next($startIntervals)) {
                $end = clone $next;
                $end->modify('-1 second');
                $intervals['intervals'][] = [
                    'start' => $dateTime,
                    'end' => $end,
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
     * @return DateTime[]
     * @throws Exception
     */
    protected function getStartIntervals(): array
    {
        $start = $this->getStartTimeForFilter(true);
        $end = $this->getEndTimeForFilter();
        $deltaSeconds = $end->getTimestamp() - $start->getTimestamp();
        if ($deltaSeconds <= 86400) { // until 1 day
            return ['intervals' => $this->getHourIntervals(), 'frequency' => 'hour'];
        }
        if ($deltaSeconds <= 1209600) { // until 2 weeks
            return ['intervals' => $this->getDayIntervals(), 'frequency' => 'day'];
        }
        if ($deltaSeconds <= 5184000) { // until 2 month
            return ['intervals' => $this->getWeekIntervals(), 'frequency' => 'week'];
        }
        if ($deltaSeconds <= 63072000) { // until 2 years
            return ['intervals' => $this->getMonthIntervals(), 'frequency' => 'month'];
        }
        // over 2 years
        return ['intervals' => $this->getYearIntervals(), 'frequency' => 'year'];
    }

    /**
     * @return DateTime[]
     * @throws Exception
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
     * @return DateTime[]
     * @throws Exception
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
     * @return DateTime[]
     * @throws Exception
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
     * @return DateTime[]
     * @throws Exception
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
     * @return DateTime[]
     * @throws Exception
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
     * Get all sites on which the current editor has reading access
     *
     * @return array
     */
    public function getAllowedSites(): array
    {
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        return $siteService->getAllowedSites();
    }

    /**
     * Always return given site or all available sites, so this can be always build in sql queries
     *
     * @return array
     */
    public function getSitesForFilter(): array
    {
        if ($this->isSiteSet()) {
            return [$this->getSite()];
        }
        return array_merge(array_keys($this->getAllowedSites()), ['']);
    }

    protected function getSitesForFilterList(): string
    {
        return implode(',', $this->getSitesForFilter());
    }

    public function getHash(): string
    {
        return $this->__toString();
    }

    /**
     * Calculate unique hash that can be transferred to a cacheLayer for different caches depending on filter settings
     *
     * @return string
     */
    public function __toString(): string
    {
        $string = $this->searchterm . $this->pid . $this->timeFrom . $this->timeTo . (string)$this->scoring .
            (string)$this->categoryScoring . (string)$this->timePeriod . (string)$this->identified .
            (string)$this->shortMode . (string)$this->domain . $this->getSitesForFilterList();
        return md5($string);
    }
}
