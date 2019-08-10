<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use Nimut\TestingFramework\Database\Database;
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
     * @throws InvalidQueryException
     */
    public function findInterestingLogs(FilterDto $filter)
    {
        $query = $this->createQuery();
        $logicalAnd = $this->interestingLogsLogicalAnd($query);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraints($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setLimit(8);
        return $query->execute();
    }

    /**
     * Return a result of identified logs for the last months
     *  e.g. for the last 2:
     *      [
     *          [Log:1, Log:2],
     *          [Log:4, Log:88],
     *      ]
     *
     * @param int $months
     * @return array
     * @throws DBALException
     */
    public function findIdentifiedLogsFromMonths(int $months): array
    {
        $queryBuilder = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $result = [];
        for ($i = 0; $i < $months; $i++) {
            /** @noinspection PhpUnhandledExceptionInspection */
            $dates = DateUtility::getLatestMonthDates($i);
            $query = 'select * from ' . Log::TABLE_NAME .
                ' where status in(' . implode(',', Log::getIdentifiedStatus()) . ')' .
                ' and crdate >= ' . $dates[0]->format('U') . ' and crdate <= ' . $dates[1]->format('U');
            $result[] = $queryBuilder->executeQuery($query)->fetchAll();
        }
        return $result;
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
