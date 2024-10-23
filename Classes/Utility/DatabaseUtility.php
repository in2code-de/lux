<?php

declare(strict_types=1);
namespace In2code\Lux\Utility;

use Doctrine\DBAL\Exception as ExceptionDbal;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryBuilder;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class DatabaseUtility
{
    public static function getQueryBuilderForTable(string $tableName, bool $removeRestrictions = false): QueryBuilder
    {
        /** @var QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)->getQueryBuilderForTable($tableName);
        if ($removeRestrictions === true) {
            $queryBuilder->getRestrictions()->removeAll();
        }
        return $queryBuilder;
    }

    public static function getConnectionForTable(string $tableName): Connection
    {
        return GeneralUtility::makeInstance(ConnectionPool::class)->getConnectionForTable($tableName);
    }

    /**
     * @param string $tableName
     * @return bool
     * @throws ExceptionDbal
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
     * @throws ExceptionDbal
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

    /**
     * @param string $fieldName
     * @param string $tableName
     * @return bool
     * @throws ExceptionDbal
     */
    public static function isAnyFieldFilledInTable(string $fieldName, string $tableName): bool
    {
        $queryBuilder = self::getQueryBuilderForTable($tableName);
        $queryBuilder->getRestrictions()->removeAll()->add(GeneralUtility::makeInstance(DeletedRestriction::class));
        $result = $queryBuilder
            ->select($fieldName)
            ->from($tableName)
            ->where($fieldName . ' = \'\'')
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
        return $result === false;
    }
}
