<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as ExceptionDbal;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseUtility
{
    /**
     * @param string $tableName
     * @param bool $removeRestrictions
     * @return QueryBuilder
     */
    public static function getQueryBuilderForTable(string $tableName, bool $removeRestrictions = false): QueryBuilder
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        if ($removeRestrictions === true) {
            $queryBuilder->getRestrictions()->removeAll();
        }
        return $queryBuilder;
    }

    /**
     * @param string $tableName
     * @return Connection
     */
    public static function getConnectionForTable(string $tableName): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
    }

    /**
     * @param string $tableName
     * @return bool
     * @throws DBALException
     */
    public static function isTableExisting(string $tableName): bool
    {
        $existing = false;
        $connection = self::getConnectionForTable($tableName);
        $queryResult = $connection->query('show tables;')->fetchAll();
        foreach ($queryResult as $tableProperties) {
            if (in_array($tableName, array_values($tableProperties))) {
                $existing = true;
                break;
            }
        }
        return $existing;
    }

    /**
     * @param string $fieldName
     * @param string $tableName
     * @return bool
     * @throws DBALException
     */
    public static function isFieldExistingInTable(string $fieldName, string $tableName): bool
    {
        $found = false;
        $connection = self::getConnectionForTable($tableName);
        $queryResult = $connection->query('describe ' . $tableName . ';')->fetchAll();
        foreach ($queryResult as $fieldProperties) {
            if ($fieldProperties['Field'] === $fieldName) {
                $found = true;
                break;
            }
        }
        return $found;
    }

    /**
     * @param string $tableName
     * @return bool
     * @throws ExceptionDbal
     * @throws Exception
     */
    public static function isTableFilled(string $tableName): bool
    {
        $queryBuilder = self::getQueryBuilderForTable($tableName);
        return $queryBuilder
                ->select('*')
                ->from($tableName)
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchAssociative() > 0;
    }

    /**
     * @param string $fieldName
     * @param string $tableName
     * @return bool
     * @throws Exception
     * @throws ExceptionDbal
     */
    public static function isFieldInTableFilled(string $fieldName, string $tableName): bool
    {
        $queryBuilder = self::getQueryBuilderForTable($tableName);
        return (string)$queryBuilder
                ->select($fieldName)
                ->from($tableName)
                ->setMaxResults(1)
                ->executeQuery()
                ->fetchOne() !== '';
    }
}
