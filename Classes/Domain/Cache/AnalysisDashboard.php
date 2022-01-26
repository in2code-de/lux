<?php

declare(strict_types = 1);

namespace In2code\Lux\Domain\Cache;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\DataProvider\BrowserAmountDataProvider;
use In2code\Lux\Domain\DataProvider\DomainDataProvider;
use In2code\Lux\Domain\DataProvider\DownloadsDataProvider;
use In2code\Lux\Domain\DataProvider\PagevisistsDataProvider;
use In2code\Lux\Domain\DataProvider\SocialMediaDataProvider;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbaseObject;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * AnalysisDashboard
 */
class AnalysisDashboard extends AbstractLayer implements LayerInterface
{
    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionDbal
     * @throws ExceptionExtbaseObject
     * @throws InvalidQueryException
     */
    public function getCachableArguments(): array
    {
        $filter = ObjectUtility::getFilterDto();
        return [
            'filter' => $filter,
            'numberOfVisitorsData' => GeneralUtility::makeInstance(PagevisistsDataProvider::class, $filter),
            'numberOfDownloadsData' => GeneralUtility::makeInstance(DownloadsDataProvider::class, $filter),
            'pages' => $this->pagevisitsRepository->findCombinedByPageIdentifier($filter),
            'downloads' => $this->downloadRepository->findCombinedByHref($filter),
            'news' => $this->newsvisitRepository->findCombinedByNewsIdentifier($filter),
            'searchterms' => $this->searchRepository->findCombinedBySearchIdentifier($filter),
            'browserData' => GeneralUtility::makeInstance(BrowserAmountDataProvider::class, $filter),
            'domainData' => GeneralUtility::makeInstance(DomainDataProvider::class, $filter),
            'socialMediaData' => GeneralUtility::makeInstance(SocialMediaDataProvider::class, $filter),
        ];
    }

    /**
     * @return array
     * @throws InvalidConfigurationTypeException
     * @throws InvalidQueryException
     */
    public function getUncachableArguments(): array
    {
        $filter = ObjectUtility::getFilterDto();
        return [
            'interestingLogs' => $this->logRepository->findInterestingLogs($filter),
            'latestPagevisits' => $this->pagevisitsRepository->findLatestPagevisits($filter),
        ];
    }
}
