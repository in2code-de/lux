<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\ConfigurationUtility;
use In2code\Lux\Utility\ObjectUtility;
use jlawrence\eos\Parser;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException;

/**
 * Class ScoringService to calculate a scoring to a visitor
 */
class ScoringService
{

    /**
     * Calculation string like "(10 * numberOfSiteVisits)"
     *
     * @var string
     */
    protected $calculation = '';

    /**
     * @var \DateTime|null
     */
    protected $time = null;

    /**
     * ScoringService constructor.
     * @param \DateTime|null $time Set a time if you want to calculate a scoring from the past
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function __construct(\DateTime $time = null)
    {
        if (!class_exists(Parser::class)) {
            throw new \BadFunctionCallException('Parser class not found. Did you do a "composer update"?', 1518975126);
        }
        if ($time !== null) {
            $this->time = $time;
        }
        $this->setCalculation();
    }

    /**
     * @param Visitor $visitor
     * @return void
     * @throws InvalidQueryException
     * @throws IllegalObjectTypeException
     * @throws UnknownObjectException
     */
    public function calculateAndSetScoring(Visitor $visitor)
    {
        if ($visitor->isNotBlacklisted()) {
            $scoring = $this->calculateScoring($visitor);
            $visitor->setScoring($scoring);
            $visitorRepository = ObjectUtility::getObjectManager()->get(VisitorRepository::class);
            $visitorRepository->update($visitor);
            $visitorRepository->persistAll();
        }
    }

    /**
     * @param Visitor $visitor
     * @return int Integer value 0 or higher
     * @throws InvalidQueryException
     */
    public function calculateScoring(Visitor $visitor): int
    {
        $scoring = 0;
        if ($visitor->isNotBlacklisted()) {
            $variables = [
                'numberOfSiteVisits' => $this->getNumberOfSiteVisits($visitor),
                'numberOfPageVisits' => $this->getNumberOfVisits($visitor),
                'lastVisitDaysAgo' => $this->getNumberOfDaysSinceLastVisit($visitor),
                'downloads' => $this->getNumberOfDownloads($visitor)
            ];
            $scoring = (int)Parser::solve($this->getCalculation(), $variables);
            if ($scoring < 0) {
                $scoring = 0;
            }
        }
        return $scoring;
    }

    /**
     * @param Visitor $visitor
     * @return int
     * @throws InvalidQueryException
     */
    protected function getNumberOfSiteVisits(Visitor $visitor): int
    {
        $sitevisits = 0;
        if ($this->time === null) {
            $sitevisits = $visitor->getVisits();
        } else {
            /** @var PagevisitRepository $pagevisitRepository */
            $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
            $pagevisits = $pagevisitRepository->findByVisitorAndTime($visitor, $this->time);
            if ($pagevisits > 0) {
                $lastVisit = null;
                foreach ($pagevisits as $pagevisit) {
                    if ($lastVisit !== null) {
                        /** @var Pagevisit $pagevisit */
                        $interval = $lastVisit->diff($pagevisit->getCrdate());
                        // if difference is greater then one hour
                        if ($interval->h > 0) {
                            $sitevisits++;
                        }
                    }
                    $lastVisit = $pagevisit->getCrdate();
                }
            }
        }
        return $sitevisits;
    }

    /**
     * @param Visitor $visitor
     * @return int
     * @throws InvalidQueryException
     */
    protected function getNumberOfVisits(Visitor $visitor): int
    {
        if ($this->time === null) {
            $visits = count($visitor->getPagevisits());
        } else {
            /** @var PagevisitRepository $pagevisitRepository */
            $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
            $visits = $pagevisitRepository->findByVisitorAndTime($visitor, $this->time)->count();
        }
        return $visits;
    }

    /**
     * @param Visitor $visitor
     * @return int
     * @throws InvalidQueryException
     */
    protected function getNumberOfDaysSinceLastVisit(Visitor $visitor): int
    {
        $days = 50;
        if ($this->time === null) {
            $lastPagevisit = $visitor->getLastPagevisit();
        } else {
            $pagevisitRepository = ObjectUtility::getObjectManager()->get(PagevisitRepository::class);
            /** @var Pagevisit $lastPagevisit */
            $lastPagevisit = $pagevisitRepository->findLastByVisitorAndTime($visitor, $this->time);
        }
        if ($lastPagevisit !== null) {
            $time = $this->time;
            if ($this->time === null) {
                /** @noinspection PhpUnhandledExceptionInspection */
                $time = new \DateTime();
            }
            $delta = $time->diff($lastPagevisit->getCrdate());
            $days = $delta->d;
        }
        return $days;
    }

    /**
     * @param Visitor $visitor
     * @return int
     * @throws InvalidQueryException
     */
    protected function getNumberOfDownloads(Visitor $visitor): int
    {
        if ($this->time === null) {
            $downloads = count($visitor->getDownloads());
        } else {
            /** @var DownloadRepository $downloadRepository */
            $downloadRepository = ObjectUtility::getObjectManager()->get(DownloadRepository::class);
            $downloads = $downloadRepository->findByVisitorAndTime($visitor, $this->time)->count();
        }
        return $downloads;
    }

    /**
     * @return string
     */
    public function getCalculation(): string
    {
        return $this->calculation;
    }

    /**
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function setCalculation()
    {
        $this->calculation = ConfigurationUtility::getScoringCalculation();
    }
}
