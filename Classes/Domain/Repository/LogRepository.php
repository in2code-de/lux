<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class LogRepository
 */
class LogRepository extends AbstractRepository
{

    /**
     * @param FilterDto $filter
     * @return array|QueryResultInterface
     */
    public function findInterestingLogs(FilterDto $filter)
    {
        $query = $this->createQuery();
        $logicalAnd = $this->interestingLogsLogicalAnd($query);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setLimit(10);
        return $query->execute();
    }

    /**
     * @param FilterDto $filter
     * @param QueryInterface $query
     * @param array $logicalAnd
     * @return array
     * @throws InvalidQueryException
     */
    protected function extendLogicalAndWithFilterConstraints(
        FilterDto $filter,
        QueryInterface $query,
        array $logicalAnd
    ): array {
        $logicalAnd[] = $query->greaterThan('crdate', $filter->getStartTimeForFilter());
        $logicalAnd[] = $query->lessThan('crdate', $filter->getEndTimeForFilter());
        return $logicalAnd;
    }

    /**
     * Get minimum status from TypoScript settings.backendview.analysis.activity.statusGreaterThen
     *
     * @param QueryInterface $query
     * @return array
     * @throws InvalidQueryException
     */
    protected function interestingLogsLogicalAnd(QueryInterface $query): array
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = ObjectUtility::getConfigurationService();
        $status = (int)$configurationService->getTypoScriptSettingsByPath(
            'backendview.analysis.activity.statusGreaterThen'
        );
        return [$query->greaterThan('status', $status)];
    }
}
