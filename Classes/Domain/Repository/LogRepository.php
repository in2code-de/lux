<?php
/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
declare(strict_types = 1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use Exception;
use In2code\Lux\Domain\Model\Log;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\DateUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException;
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
     * @throws InvalidQueryException
     * @throws InvalidConfigurationTypeException
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
     * @throws Exception
     */
    public function findByStatusAmount(int $status, FilterDto $filter): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
        $query = 'select count(*) from ' . Log::TABLE_NAME . ' where status=' . (int)$status
            . $this->extendWhereClauseWithFilterTime($filter);
        return (int)$connection->executeQuery($query)->fetchColumn();
    }

    /**
     * @param int $pageIdentifier
     * @param FilterDto $filter
     * @return int
     */
    public function findAmountOfIdentifiedLogsByPageIdentifierAndTimeFrame(int $pageIdentifier, FilterDto $filter): int
    {
        $identifiedStatus = [
            Log::STATUS_IDENTIFIED,
            Log::STATUS_IDENTIFIED_EMAIL4LINK,
            Log::STATUS_IDENTIFIED_FORMLISTENING,
            Log::STATUS_IDENTIFIED_FRONTENDAUTHENTICATION,
            Log::STATUS_IDENTIFIED_LUXLETTERLINK
        ];
        try {
            $connection = DatabaseUtility::getConnectionForTable(Log::TABLE_NAME);
            $query = 'select count(*) from ' . Log::TABLE_NAME
                . ' where status in (' . implode(',', $identifiedStatus) . ')'
                . ' and JSON_EXTRACT(properties, "$.pageUid") = ' . (int)$pageIdentifier
                . $this->extendWhereClauseWithFilterTime($filter);
            return (int)$connection->executeQuery($query)->fetchColumn();
        } catch (Exception $exception) {
            // Catch if JSON_EXTRACT() is not possible as database operation
            return 0;
        }
    }

    /**
     * Get minimum status from TypoScript settings.backendview.analysis.activity.statusGreaterThen
     *
     * @param QueryInterface $query
     * @return array
     * @throws InvalidQueryException
     * @throws InvalidConfigurationTypeException
     */
    protected function interestingLogsLogicalAnd(QueryInterface $query): array
    {
        $configurationService = ObjectUtility::getConfigurationService();
        $configString = $configurationService->getTypoScriptSettingsByPath(
            'backendview.analysis.activity.defineLogStatusForInterestingLogs'
        );
        if ($configString === '') {
            // In some rare cases TypoScript is not available in backend module even if TS is included in root template
            $configString = '2,3,25,28,26,21,22,23,50,55,60,70,80,100';
        }
        $status = GeneralUtility::trimExplode(',', $configString, true);
        return [
            $query->in('status', $status)
        ];
    }
}
