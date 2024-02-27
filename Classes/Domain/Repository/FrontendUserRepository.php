<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\Exception as ExceptionDbalDriver;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Utility\DatabaseUtility;

class FrontendUserRepository extends AbstractRepository
{
    public const TABLE_NAME = 'fe_users';

    /**
     * Return identifiers of fe_users records where email is not empty
     *
     * @return array
     * @throws DBALException
     * @throws ExceptionDbalDriver
     * @throws ExceptionDbal
     */
    public function findFrontendUsersWithEmails(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        return $queryBuilder
            ->select('uid', 'email')
            ->from(self::TABLE_NAME)
            ->where('email != \'\'')
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
