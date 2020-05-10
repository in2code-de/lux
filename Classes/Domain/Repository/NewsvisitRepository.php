<?php
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Newsvisit;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\ObjectUtility;
use TYPO3\CMS\Extbase\Object\Exception;

/**
 * Class NewsvisitRepository
 */
class NewsvisitRepository extends AbstractRepository
{
    /**
     * newsIdentifier => count
     *  [
     *      ['count' => 12, 'news' => {News Object 1}]
     *      ['count' => 55, 'news' => {News Object 2}]
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     * @throws Exception
     */
    public function findCombinedByNewsIdentifier(FilterDto $filter): array
    {
        $newsRepository = ObjectUtility::getObjectManager()->get(NewsRepository::class);
        $connection = DatabaseUtility::getConnectionForTable(Newsvisit::TABLE_NAME);
        $sql = 'select count(*) count, news from ' . Newsvisit::TABLE_NAME
            . ' where ' . $this->extendWhereClauseWithFilterTime($filter, false) . ' group by news order by count desc';
        $rows = (array)$connection->executeQuery($sql)->fetchAll();

        $objects = [];
        foreach ($rows as $row) {
            $news = $newsRepository->findByIdentifier($row['news']);
            if ($news !== null) {
                $objects[] = [
                    'count' => $row['count'],
                    'news' => $news
                ];
            }
        }
        return $objects;
    }
}
