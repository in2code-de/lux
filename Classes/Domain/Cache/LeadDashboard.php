<?php

declare(strict_types = 1);

namespace In2code\Lux\Domain\Cache;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\DataProvider\IdentificationMethodsDataProvider;
use In2code\Lux\Domain\DataProvider\ReferrerAmountDataProvider;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception as ExceptionExtbaseObject;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;

/**
 * LeadDashboard
 */
class LeadDashboard extends AbstractLayer implements LayerInterface
{
    /**
     * @return array
     * @throws DBALException
     * @throws ExceptionExtbaseObject
     * @throws InvalidQueryException
     */
    public function getCachableArguments(): array
    {
        $filter = ObjectUtility::getFilterDto();
        return [
            'filter' => $filter,
            'numberOfUniqueSiteVisitors' => $this->visitorRepository->findByUniqueSiteVisits($filter)->count(),
            'numberOfRecurringSiteVisitors' => $this->visitorRepository->findByRecurringSiteVisits($filter)->count(),
            'identifiedPerMonth' => $this->logRepository->findIdentifiedLogsFromMonths(6),
            'numberOfIdentifiedVisitors' => $this->visitorRepository->findIdentified($filter)->count(),
            'numberOfUnknownVisitors' => $this->visitorRepository->findUnknown($filter)->count(),
            'identificationMethods' => GeneralUtility::makeInstance(IdentificationMethodsDataProvider::class, $filter),
            'referrerAmountData' => GeneralUtility::makeInstance(ReferrerAmountDataProvider::class, $filter),
            'countries' => $this->ipinformationRepository->findAllCountryCodesGrouped($filter),
        ];
    }

    /**
     * @return array
     * @throws InvalidQueryException
     * @throws ExceptionExtbaseObject
     */
    public function getUncachableArguments(): array
    {
        $filter = ObjectUtility::getFilterDto();
        return [
            'interestingLogs' => $this->logRepository->findInterestingLogs($filter, 10),
            'whoisonline' => $this->visitorRepository->findOnline(8),
            'hottestVisitors' => $this->visitorRepository->findByHottestScorings($filter, 10),
        ];
    }
}
