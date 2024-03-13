<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Service;

use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Exception\ConfigurationException;
use In2code\Lux\Utility\BackendUtility;
use In2code\Lux\Utility\DatabaseUtility;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Type\Bitmask\Permission;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;

trait PermissionTrait
{
    /**
     * Remove unauthorized records from array
     *
     * @param array $rows
     * @param string $table
     * @return array
     * @throws ExceptionDbal
     * @throws ConfigurationException
     */
    private function filterRecords(array $rows, string $table): array
    {
        if (BackendUtility::isAdministrator()) {
            return $rows;
        }

        foreach ($rows as $key => $row) {
            $identifier = $this->getIdentifierFromArrayOrObject($row, $key);
            if ($this->isAuthenticatedForRecord($identifier, $table) === false) {
                unset($rows[$key]);
            }
        }
        return $rows;
    }

    /**
     * @param $object
     * @param $key
     * @return int
     * @throws ConfigurationException
     */
    protected function getIdentifierFromArrayOrObject($object, $key): int
    {
        if (is_array($object)) { // AllAssociative
            if (array_key_exists('uid', $object)) {
                return $object['uid'];
            }
        } elseif (is_string($object) || is_int($object)) { // KeyValue
            return (int)$key;
        } elseif (is_a($object, AbstractEntity::class)) { // DomainObject
            return $object->getUid();
        }
        throw new ConfigurationException('Object not supported in ' . __CLASS__, 1710317765);
    }

    /**
     * @param int $identifier
     * @param string $table
     * @return bool
     * @throws ExceptionDbal
     */
    private function isAuthenticatedForRecord(int $identifier, string $table): bool
    {
        if (BackendUtility::isAdministrator()) {
            return true;
        }

        $pageIdentifier = $this->getPageIdentifierFromRecord($identifier, $table);
        return $this->isAuthenticatedForPageRow($this->getPageRowFromPageIdentifier($pageIdentifier));
    }

    private function isAuthenticatedForPageRow(array $pageRecord): bool
    {
        if (BackendUtility::isAdministrator()) {
            return true;
        }

        $beuserAuthentication = BackendUtility::getBackendUserAuthentication();
        return $beuserAuthentication !== null &&
            $beuserAuthentication->doesUserHaveAccess($pageRecord, Permission::PAGE_SHOW);
    }

    /**
     * @param int $identifier
     * @param string $table
     * @return int
     * @throws ExceptionDbal
     */
    protected function getPageIdentifierFromRecord(int $identifier, string $table): int
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable($table);
        return (int)$queryBuilder
            ->select('pid')
            ->from($table)
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($identifier, Connection::PARAM_INT))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchOne();
    }

    /**
     * @param int $identifier
     * @return array|int
     * @throws ExceptionDbal
     */
    protected function getPageRowFromPageIdentifier(int $identifier): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable('pages');
        return (array)$queryBuilder
            ->select('*')
            ->from('pages')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($identifier, Connection::PARAM_INT))
            )
            ->setMaxResults(1)
            ->executeQuery()
            ->fetchAssociative();
    }
}
