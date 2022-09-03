<?php

/** @noinspection SqlNoDataSourceInspection */
/** @noinspection SqlDialectInspection */
declare(strict_types=1);
namespace In2code\Lux\Domain\Repository;

use Doctrine\DBAL\DBALException;
use In2code\Lux\Domain\Model\Ipinformation;
use In2code\Lux\Domain\Model\Transfer\FilterDto;
use In2code\Lux\Utility\DatabaseUtility;

/**
 * Class IpinformationRepository
 */
class IpinformationRepository extends AbstractRepository
{
    /**
     * @return int
     * @throws DBALException
     */
    public function findAllAmount(): int
    {
        $connection = DatabaseUtility::getConnectionForTable(Ipinformation::TABLE_NAME);
        return (int)$connection->executeQuery('select count(*) from ' . Ipinformation::TABLE_NAME)->fetchColumn();
    }

    /**
     * Get an array with number of visitors depending to the country where they came from
     *  Example return value:
     *  [
     *      'de' => 234,
     *      'at' => 45,
     *      'ch' => 11
     *  ]
     *
     * @param FilterDto $filter
     * @return array
     * @throws DBALException
     * @throws \Exception
     */
    public function findAllCountryCodesGrouped(FilterDto $filter): array
    {
        $connection = DatabaseUtility::getConnectionForTable(Ipinformation::TABLE_NAME);
        $rows = $connection->query(
            'select value from ' . Ipinformation::TABLE_NAME . ' where name = "countryCode"'
            . $this->extendWhereClauseWithFilterTime($filter)
        )->fetchAll();

        $countryCodes = [];
        /** @var Ipinformation $ipinformation */
        foreach ($rows as $row) {
            $countryCode = $row['value'];
            if (!array_key_exists($countryCode, $countryCodes)) {
                $countryCodes[$countryCode] = 1;
            } else {
                $countryCodes[$countryCode]++;
            }
        }
        arsort($countryCodes);
        return $countryCodes;
    }
}
