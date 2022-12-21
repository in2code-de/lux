<?php

declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\Driver\Exception;
use Doctrine\DBAL\Exception as ExceptionDbal;
use In2code\Lux\Utility\DatabaseUtility;

class FrontenduserRepository
{
    const TABLE_NAME = 'fe_users';

    /**
     * Return identifiers of fe_users records where email is not empty
     *
     * @return array
     * @throws Exception
     * @throws ExceptionDbal
     */
    public function findFrontendUsersWithEmails(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        return $queryBuilder
            ->select('uid', 'email')
            ->from(self::TABLE_NAME)
            ->where('email != ""')
            ->executeQuery()
            ->fetchAllAssociative();
    }
}
