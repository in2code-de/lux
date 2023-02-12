<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception;
use In2code\Lux\Domain\Model\Page;
use In2code\Lux\Utility\DatabaseUtility;
use PDO;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\HiddenRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class PageRepository extends AbstractRepository
{
    /**
     * @param int $identifier
     * @return string
     * @throws Exception
     * @throws ExceptionDbalDriver
     * @throws DBALException
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
     * @throws ExceptionDbalDriver
     * @throws Exception
     * @throws DBALException
     */
    public function findRawByIdentifier(int $identifier): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Page::TABLE_NAME, true);
        $result = $queryBuilder
            ->select('*')
            ->from(Page::TABLE_NAME)
            ->where('uid=' . (int)$identifier)
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
        if ($result !== false) {
            return $result;
        }
        return [];
    }

    /**
     * @return array
     * @throws Exception
     * @throws ExceptionDbalDriver
     * @throws DBALException
     */
    public function getPageIdentifiersFromNormalDokTypes(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Page::TABLE_NAME, true);
        return $queryBuilder
            ->select('uid')
            ->from(Page::TABLE_NAME)
            ->where('doktype=1')
            ->setMaxResults(100000)
            ->executeQuery()
            ->fetchAllAssociative();
    }

    /**
     * Successor of TYPO3\CMS\Core\Database\QueryGenerator->getTreeList as it was removed in TYPO3 12
     * Currently used in LUXenterprise
     *
     * @param int $pageIdentifier Start page identifier
     * @param bool $addStart Add start page identifier to list
     * @param bool $addHidden Should records with pages.hidden=1 be added?
     * @return array
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    public function getAllSubpageIdentifiers(int $pageIdentifier, bool $addStart = true, bool $addHidden = true): array
    {
        $identifiers = [];
        if ($addStart === true) {
            $identifiers[] = $pageIdentifier;
        }
        foreach ($this->getChildrenPageIdentifiers($pageIdentifier, $addHidden) as $identifier) {
            $identifiers = array_merge($identifiers, $this->getAllSubpageIdentifiers($identifier, true, $addHidden));
        }
        return $identifiers;
    }

    /**
     * @param int $pageIdentifier
     * @param bool $addHidden
     * @return array
     * @throws DBALException
     * @throws ExceptionDbalDriver
     */
    protected function getChildrenPageIdentifiers(int $pageIdentifier, bool $addHidden): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(Page::TABLE_NAME, true);
        $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder
            ->select('uid', 'uid')
            ->from(Page::TABLE_NAME)
            ->where(
                $queryBuilder->expr()->eq(
                    'pid',
                    $queryBuilder->createNamedParameter($pageIdentifier, PDO::PARAM_INT)
                ),
                $queryBuilder->expr()->eq('sys_language_uid', 0)
            );
        if ($addHidden === false) {
            $queryBuilder->getRestrictions()->add(GeneralUtility::makeInstance(HiddenRestriction::class));
        }
        $result = $queryBuilder->execute()->fetchAllKeyValue();
        return array_values($result);
    }
}
