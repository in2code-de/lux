<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Fingerprint;
use In2code\Lux\Domain\Model\Pagevisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Domain\Model\Visitor;
use In2code\Lux\Exception\ClassDoesNotExistException;
use In2code\Lux\Utility\DatabaseUtility;
use WhichBrowser\Parser;

class FingerprintRepository extends AbstractRepository
{
    /**
     * @return int
     * @throws ExceptionDbal
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        return (int)$connection->executeQuery('select count(*) from ' . Fingerprint::TABLE_NAME)->fetchOne();
    }

    /**
     * [
     *      'Windows Chrome' => 234,
     *      'Android Chrome' => 123
     * ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws ClassDoesNotExistException
     * @throws ExceptionDbal
     */
    public function getAmountOfUserAgents(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Fingerprint::TABLE_NAME);
        $sql = 'select fp.user_agent, count(fp.user_agent) count'
            . ' from ' . Fingerprint::TABLE_NAME . ' fp'
            . ' left join ' . Visitor::TABLE_NAME . ' v on fp.visitor=v.uid'
            . ' left join ' . Pagevisit::TABLE_NAME . ' pv on pv.visitor=v.uid'
            . ' where fp.user_agent != ""'
            . $this->extendWhereClauseWithFilterTime($filter, true, 'fp')
            . $this->extendWhereClauseWithFilterSite($filter, 'pv')
            . ' group by user_agent having (count > 1) order by count desc limit 1000';
        $records = $connection->executeQuery($sql)->fetchAllAssociative();
        $result = [];
        foreach ($records as $record) {
            if (class_exists(Parser::class)) {
                $parser = new Parser($record['user_agent']);
                $osBrowser = ($parser->os->getName() ?? '') . ' ' . ($parser->browser->getName() ?? '');
                if (array_key_exists($osBrowser, $result)) {
                    $result[$osBrowser] += $record['count'];
                } else {
                    $result[$osBrowser] = $record['count'];
                }
            } else {
                throw new ClassDoesNotExistException(
                    '\WhichBrowser\Parser class is missing. ' .
                    'Maybe your TYPO3 is running in classic mode instead of composer mode. ' .
                    'Please install this extension via composer only.',
                    1588337756
                );
            }
        }
        arsort($result);
        return $result;
    }

    /**
     * @param string $fingerprint
     * @return int
     * @throws ExceptionDbal
     */
    public function getFingerprintCountByValue(string $fingerprint): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Fingerprint::TABLE_NAME);
        return (int)$queryBuilder
            ->count('*')
            ->from(Fingerprint::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq('value', $queryBuilder->createNamedParameter($fingerprint)),
                $queryBuilder->expr()->eq('type', Fingerprint::TYPE_FINGERPRINT)
            )
            ->executeQuery()
            ->fetchOne();
    }
}
