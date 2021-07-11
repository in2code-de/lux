<?php
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
declare(strict_types = 1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Service\ConfigurationService;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\Exception;
use TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * Class LogRepository
 */
class LogRepository extends AbstractRepository
{
    /**
     * @return int
     * @throws DBALException
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        return (int)$connection->executeQuery('select count(*) from ' . Log::TABLE_NAME)->fetchColumn();
    }

    /**
     * @param FilterDto $filter
     * @param int $limit
     * @return QueryResultInterface
     * @throws Exception
     * @throws InvalidQueryException
     */
    public function findInterestingLogs(FilterDto $filter, int $limit = 8): QueryResultInterface
    {
        $query = $this->createQuery();
        $logicalAnd = $this->interestingLogsLogicalAnd($query);
        $logicalAnd = $this->extendLogicalAndWithFilterConstraintsForCrdate($filter, $query, $logicalAnd);
        $query->matching($query->logicalAnd($logicalAnd));
        $query->setLimit($limit);
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
    public function findIdentifiedLogsFromMonths(int $months = 6): array
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
        $result = array_reverse($result);
        return $result;
    }

    /**
     * @param int $status
     * @param FilterDto $filter
     * @return int
     * @throws DBALException
     * @throws \Exception
     */
    public function findByStatusAmount(int $status, FilterDto $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $query = 'select count(uid) from ' . Log::TABLE_NAME . ' where status=' . (int)$status
            . $this->extendWhereClauseWithFilterTime($filter);
        return (int)$connection->executeQuery($query)->fetchColumn();
    }

    /**
     * Get minimum status from TypoScript settings.backendview.analysis.activity.statusGreaterThen
     *
     * @param QueryInterface $query
     * @return array
     * @throws InvalidQueryException
     * @throws Exception
     */
    protected function interestingLogsLogicalAnd(QueryInterface $query): array
    {
        /** @var ConfigurationService $configurationService */
        $configurationService = ObjectUtility::getConfigurationService();
        $configString = $configurationService->getTypoScriptSettingsByPath(
            'backendview.analysis.activity.defineLogStatusForInterestingLogs'
        );
        $status = GeneralUtility::trimExplode(',', $configString, true);
        return [
            $query->in('status', $status)
        ];
    }
}
