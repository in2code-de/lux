<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Domain\Model\Linklistener;
use In2code\Lux\Domain\Repository\VisitorRepository;
use In2code\Lux\Utility\DatabaseUtility;
use In2code\Lux\Utility\StringUtility;
use In2code\Luxenterprise\Domain\Model\Shortener;
use In2code\Luxenterprise\Domain\Model\Shortenervisit;
use In2code\Luxenterprise\Domain\Model\Workflow;
use throwable;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DemoDataService extends SiteService
{
    protected VisitorRepository $visitorRepository;
    protected string $configurationPath = 'EXT:lux/Configuration/DemoData/';

    public function __construct(VisitorRepository $visitorRepository)
    {
        $this->visitorRepository = $visitorRepository;
    }

    public function write(): void
    {
        $this->truncateAll();
        $this->writeData();
    }

    protected function writeData(): void
    {
        foreach ($this->getTableConfiguration() as $table => $configuration) {
            $this->writeToTable($table, $configuration);
        }
    }

    protected function writeToTable(string $table, array $properties): void
    {
        $connection = DatabaseUtility::getConnectionForTable($table);
        foreach ($properties as $row) {
            $connection->insert($table, $row);
        }
    }

    /**
     *  [
     *      'tx_tablename' => [
     *          'uid' => 1,
     *          'email' => 'mail@mail.org',
     *      ]
     *  ]
     *
     * @return array
     */
    protected function getTableConfiguration(): array
    {
        $properties = [];
        $files = GeneralUtility::getFilesInDir(
            GeneralUtility::getFileAbsFileName($this->configurationPath),
            'php'
        );
        foreach ($files as $file) {
            $table = StringUtility::removeStringPostfix($file, '.php');
            if (DatabaseUtility::isTableExisting($table)) {
                $row = include GeneralUtility::getFileAbsFileName($this->configurationPath . $file);
                $properties[$table] = $row;
            }
        }
        return $properties;
    }

    protected function truncateAll(): void
    {
        $this->visitorRepository->truncateAll();
        DatabaseUtility::getConnectionForTable(Linklistener::TABLE_NAME)->truncate(Linklistener::TABLE_NAME);
        if (class_exists(Shortener::class)) {
            DatabaseUtility::getConnectionForTable(Shortener::TABLE_NAME)->truncate(Shortener::TABLE_NAME);
        }
        if (class_exists(Shortenervisit::class)) {
            DatabaseUtility::getConnectionForTable(Shortenervisit::TABLE_NAME)->truncate(Shortenervisit::TABLE_NAME);
        }
    }

    /**
     * Static functions can be used in table configuration
     */

    /**
     * Get a random array of 5 page identifiers (with hardcoded pid 1) and return one of them per random
     *
     * @return int
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    public static function getRandomPageIdentifier(): int
    {
        static $identifiers = [];
        if ($identifiers === []) {
            $identifiers = [self::getFirstRootPageIdentifier()];
            $connection = DatabaseUtility::getConnectionForTable('pages');
            $results = $connection->executeQuery(
                'select uid from pages where deleted=0 and hidden=0 and doktype=1 and sys_language_uid=0'
                . ' order by rand() limit 4;'
            )->fetchAllAssociative();
            foreach ($results as $result) {
                $identifiers[] = $result['uid'];
            }
        }
        return $identifiers[rand(0, (count($identifiers) - 1))];
    }

    public static function getFirstRootPageIdentifier(): int
    {
        static $pageIdentifier = 0;
        if ($pageIdentifier === 0) {
            $connection = DatabaseUtility::getConnectionForTable('pages');
            $pageIdentifier = (int)$connection->executeQuery(
                'select uid from pages where deleted=0 and hidden=0 and is_siteroot=1 and sys_language_uid=0'
                . ' order by uid asc limit 1;'
            )->fetchOne();
            if ($pageIdentifier === 0) {
                $pageIdentifier = 1;
            }
        }
        return $pageIdentifier;
    }

    /**
     * @return int
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public static function getRandomFileIdentifier(): int
    {
        $connection = DatabaseUtility::getConnectionForTable('sys_file');
        return (int)$connection->executeQuery(
            'select uid from sys_file where missing=0 and extension="pdf" order by rand() limit 1;'
        )->fetchOne();
    }

    /**
     * @return int
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public static function getRandomLuxCatgoryIdentifier(): int
    {
        $connection = DatabaseUtility::getConnectionForTable('sys_category');
        return (int)$connection->executeQuery(
            'select uid from sys_category where deleted=0 and hidden=0 and lux_category=1 order by rand() limit 1;'
        )->fetchOne();
    }

    /**
     * @return int
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public static function getRandomWorkflowIdentifier(): int
    {
        if (class_exists(Workflow::class)) {
            $connection = DatabaseUtility::getConnectionForTable(Workflow::TABLE_NAME);
            return (int)$connection->executeQuery(
                'select uid from ' . Workflow::TABLE_NAME . ' where deleted=0 and hidden=0 order by rand() limit 1;'
            )->fetchOne();
        }
        return rand(0, 999999);
    }

    /**
     * @return string
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public static function getRandomWorkflowTitle(): string
    {
        if (class_exists(Workflow::class)) {
            $connection = DatabaseUtility::getConnectionForTable(Workflow::TABLE_NAME);
            return (string)$connection->executeQuery(
                'select title from ' . Workflow::TABLE_NAME . ' where deleted=0 and hidden=0 order by rand() limit 1;'
            )->fetchOne();
        }
        return StringUtility::getRandomString();
    }

    public static function getRandomNewsIdentifier(): int
    {
        try {
            $connection = DatabaseUtility::getconnectionfortable('tx_news_domain_model_news');
            return (int)$connection->executequery(
                'select uid from tx_news_domain_model_news where deleted=0 and hidden=0 and sys_language_uid=0'
                . ' order by rand() limit 1;'
            )->fetchone();
        } catch (throwable $exception) {
            return 0;
        }
    }

    /**
     * @return int
     * @throws ExceptionDbal
     * @throws ExceptionDbalDriver
     */
    public static function getRandomAbPage(): int
    {
        if (DatabaseUtility::isTableExisting('tx_luxenterprise_abpage')) {
            $connection = DatabaseUtility::getconnectionfortable('pages');
            return (int)$connection->executequery(
                'select abp.uid'
                . ' from pages p'
                . ' left join tx_luxenterprise_abpage abp on abp.parent_page=p.uid'
                . ' where p.deleted=0 and p.hidden=0 and p.sys_language_uid=0 and abp.deleted=0'
                . ' and abp.hidden=0 and p.ab_enabled=1'
                . ' order by rand() limit 1;'
            )->fetchone();
        }
        return rand(0, 999999);
    }

    public static function getRandomSiteIdentifier(): string
    {
        $siteService = GeneralUtility::makeInstance(SiteService::class);
        $identifiers = array_keys($siteService->getAllowedSites());
        shuffle($identifiers);
        return $identifiers[0];
    }
}
