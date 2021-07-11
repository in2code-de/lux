<?php
declare(strict_types = 1);
namespace In2code\Lux\Domain\Repository;

use In2code\Lux\Utility\DatabaseUtility;

/**
 * Class FrontenduserRepository
 */
class FrontenduserRepository
{
    const TABLE_NAME = 'fe_users';

    /**
     * Return identifiers of fe_users records where email is not empty
     *
     * @return array
     */
    public function findFrontendUsersWithEmails(): array
    {
        $queryBuilder = DatabaseUtility::getQueryBuilderForTable(self::TABLE_NAME);
        $result = $queryBuilder
            ->select('uid', 'email')
            ->from(self::TABLE_NAME)
            ->where('email != ""')
            ->execute()
            ->fetchAll();
        if ($result !== false) {
            return $result;
        }
        return [];
    }
}
