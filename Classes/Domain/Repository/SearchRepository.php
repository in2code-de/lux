<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\Exception;
use In2code\Lux\Domain\Model\Search;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\DatabaseUtility;

/**
 * Class SearchRepository
 */
class SearchRepository extends AbstractRepository
{
    /**
     * newsIdentifier => count
     *  [
     *      ['count' => 55, 'searchterm' => 'lux'],
     *      ['count' => 12, 'searchterm' => 'luxletter']
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws Exception
     */
    public function findCombinedBySearchIdentifier(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Search::TABLE_NAME);
        $sql = 'select count(*) count, searchterm from ' . Search::TABLE_NAME
            . ' where ' . $this->extendWhereClauseWithFilterTime($filter, false)
            . ' group by searchterm order by count desc';
        return (array)$connection->executeQuery($sql)->fetchAll();
    }
}
