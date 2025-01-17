<?php

declare(strict_types=1);

namespace In2code\Lux\Update\AddSites;

use In2code\Lux\Domain\Service\SiteService;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

abstract class AbstractSiteUpgrade
{
    /**
     * [
     *      123 => 'site 1',
     *      124 => 'site 2',
     * ]
     *
     * @var array
     */
    protected array $mapping = [];
    protected SiteService $siteService;

    public function __construct()
    {
        $this->siteService = GeneralUtility::makeInstance(SiteService::class);
    }

    public function run(): void
    {
        $connection = $this->getConnectionPool()->getConnectionForTable(static::TABLE_NAME);

        $queryBuilder = $this->getPreparedQueryBuilder();
        $result = $queryBuilder
            ->select('page')
            ->groupBy('page')
            ->where(
                $queryBuilder->expr()->eq('site', $queryBuilder->createNamedParameter(''))
            )
            ->executeQuery();
        while ($record = $result->fetchAssociative()) {
            $siteIdentifier = $this->getSiteIdentifierFromPage($record['page']);
            if ($siteIdentifier !== '') {
                $connection->update(
                    static::TABLE_NAME,
                    ['site' => $siteIdentifier],
                    ['page' => (int)$record['page']]
                );
            } else {
                $connection->update(
                    static::TABLE_NAME,
                    ['deleted' => 1],
                    ['page' => (int)$record['page']]
                );
            }
        }
    }

    protected function getSiteIdentifierFromPage(int $pageIdentifier): string
    {
        if (array_key_exists($pageIdentifier, $this->mapping)) {
            return $this->mapping[$pageIdentifier];
        }
        $siteIdentifier = '';
        $site = $this->siteService->getSiteFromPageIdentifier($pageIdentifier);
        if ($site !== null) {
            $siteIdentifier = $site->getIdentifier();
        }
        $this->mapping[$pageIdentifier] = $siteIdentifier;
        return $siteIdentifier;
    }

    protected function getPreparedQueryBuilder(): QueryBuilder
    {
        $queryBuilder = $this->getConnectionPool()->getQueryBuilderForTable(static::TABLE_NAME);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $queryBuilder->from(static::TABLE_NAME);
        return $queryBuilder;
    }

    protected function getConnectionPool(): ConnectionPool
    {
        return GeneralUtility::makeInstance(ConnectionPool::class);
    }
}
