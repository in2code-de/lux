<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use DateTime;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Domain\Repository\DownloadRepository;
use In2code\Lux\Domain\Repository\PagevisitRepository;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Events\AfterTrackingEvent;
use In2code\Lux\Exception\ClassDoesNotExistException;
use In2code\Lux\Utility\ConfigurationUtility;
use Symfony\Component\ExpressionLanguage\ExpressionLanguage;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Utility\GeneralUtility;
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
    protected string $calculation = '';

    protected ?DateTime $time = null;

    /**
     * @param DateTime|null $time Set a time if you want to calculate a scoring from the past
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ClassDoesNotExistException
     */
    public function __construct(?DateTime $time = null)
    {
        if (class_exists(ExpressionLanguage::class) === false) {
            throw new ClassDoesNotExistException(
                'ExpressionLanguage class not found. Composer package symfony/expression-language probably not loaded.',
                1559499211
            );
        }
        if ($time !== null) {
            $this->time = $time;
        }
        $this->setCalculation();
    }

    /**
     * @param AfterTrackingEvent $event
     * @return void
     * @throws IllegalObjectTypeException
     * @throws InvalidQueryException
     * @throws UnknownObjectException
     */
    public function calculateAndSetScoringFromEvent(AfterTrackingEvent $event): void
    {
        $this->calculateAndSetScoring($event->getVisitor());
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
            $visitorRepository = GeneralUtility::makeInstance(VisitorRepository::class);
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
                'downloads' => $this->getNumberOfDownloads($visitor),
            ];
            $expressionLanguage = new ExpressionLanguage();
            $scoring = (int)$expressionLanguage->evaluate($this->getCalculation(), $variables);
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
            $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
            $pagevisits = $pagevisitRepository->findByVisitorAndTime($visitor, $this->time);
            if (count($pagevisits) > 0) {
                $sitevisits = 1;
                $lastVisit = null;
                /** @var Pagevisit $pagevisit */
                foreach ($pagevisits as $pagevisit) {
                    /** @var DateTime $lastVisit */
                    if ($lastVisit !== null) {
                        $interval = $lastVisit->diff($pagevisit->getCrdate());
                        // if difference is greater than one hour
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
            $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
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
            $pagevisitRepository = GeneralUtility::makeInstance(PagevisitRepository::class);
            /** @var Pagevisit $lastPagevisit */
            $lastPagevisit = $pagevisitRepository->findLastByVisitorAndTime($visitor, $this->time);
        }
        if ($lastPagevisit !== null) {
            $time = $this->time;
            if ($this->time === null) {
                $time = new DateTime();
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
            $downloadRepository = GeneralUtility::makeInstance(DownloadRepository::class);
            $downloads = $downloadRepository->findByVisitorAndTime($visitor, $this->time)->count();
        }
        return $downloads;
    }

    public function getCalculation(): string
    {
        return $this->calculation;
    }

    /**
     * @return void
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     * @throws ExtensionConfigurationPathDoesNotExistException
     */
    public function setCalculation(): void
    {
        $this->calculation = ConfigurationUtility::getScoringCalculation();
    }
}
