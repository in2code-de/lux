<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Utility\DatabaseUtility;

/**
 * Class PageRepository
 */
class PageRepository extends AbstractRepository
{
    /**
     * @param int $identifier
     * @return string
     */
    public function findTitleByIdentifier(int $identifier): string
    {
        $properties = self::findRawByIdentifier($identifier);
        if (!empty($properties['title'])) {
            return $properties['title'];
        }
        return '';
    }

    /**
     * @param int $identifier
     * @return array
     */
    public function findRawByIdentifier(int $identifier): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Page::TABLE_NAME, true);
        $result = $queryBuilder
            ->select('*')
            ->from(Page::TABLE_NAME)
            ->where('uid=' . (int)$identifier)
            ->setMaxResults(1)
            ->execute()
            ->fetch();
        if ($result !== false) {
            return $result;
        }
        return [];
    }

    /**
     * @return array
     */
    public function getPageIdentifiersFromNormalDokTypes(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Page::TABLE_NAME, true);
        return (array)$queryBuilder
            ->select('uid')
            ->from(Page::TABLE_NAME)
            ->where('doktype=1')
            ->setMaxResults(100000)
            ->execute()
            ->fetchAll();
    }
}
